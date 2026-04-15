import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import WPEditor from '@TutorShared/atoms/WPEditor';
import Tabs from '@TutorShared/molecules/Tabs';

import FocusTrap from '@TutorShared/components/FocusTrap';
import { type ModalProps } from '@TutorShared/components/modals/Modal';

import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type QuizQuestion, type QuizQuestionType } from '@TutorShared/utils/types';

import DrawImagePreview from './question-previews/DrawImagePreview';
import CoordinatesPreview from './question-previews/CoordinatesPreview';
import FillInTheBlankPreview from './question-previews/FillInTheBlankPreview';
import ImageAnsweringPreview from './question-previews/ImageAnsweringPreview';
import MatchingPreview from './question-previews/MatchingPreview';
import MultipleChoicePreview from './question-previews/MultipleChoicePreview';
import OpenEndedPreview from './question-previews/OpenEndedPreview';
import OrderingPreview from './question-previews/OrderingPreview';
import PinImagePreview from './question-previews/PinImagePreview';
import ScalePreview from './question-previews/ScalePreview';
import TrueFalsePreview from './question-previews/TrueFalsePreview';
import UnsupportedPreview from './question-previews/UnsupportedPreview';

interface QuestionPreviewModalProps extends ModalProps {
  question: QuizQuestion;
  onClose: () => void;
}

const isTutorPro = tutorConfig.tutor_pro_url;
const IFRAME_SRC_DOC =
  '<!doctype html><html><head><meta charset="utf-8" /></head><body><div id="preview-root"></div></body></html>';
const PREVIEW_STYLESHEET_PATHS = [
  '/wp-content/plugins/tutor/assets/css/tutor-learning-area.min.css',
  '/wp-content/plugins/tutor/assets/css/tutor-core.min.css',
  '/wp-content/plugins/tutor/assets/css/tutor-kids.min.css',
];

if (isTutorPro) {
  PREVIEW_STYLESHEET_PATHS.push('/wp-content/plugins/tutor-pro/assets/css/front.css');
}

// Prefetch the stylesheets automatically so they are loaded in the background
// before the user even opens the preview modal.
if (typeof document !== 'undefined') {
  const siteUrl = tutorConfig.site_url.replace(/\/$/, '');
  if (siteUrl) {
    PREVIEW_STYLESHEET_PATHS.forEach((path) => {
      const link = document.createElement('link');
      link.rel = 'prefetch';
      link.as = 'style';
      link.href = `${siteUrl}${path}`;
      document.head.appendChild(link);
    });
  }
}

const QuestionPreviewModal = ({ question, onClose }: QuestionPreviewModalProps) => {
  const [activeTab, setActiveTab] = useState<'desktop' | 'mobile'>('desktop');
  const [cssLoaded, setCssLoaded] = useState(false);
  const iframeRef = useRef<HTMLIFrameElement>(null);
  const [iframeDocument, setIframeDocument] = useState<Document | null>(null);
  const previewQuestionStyleType = getPreviewQuestionStyleType(question);

  useEffect(() => {
    const iframe = iframeRef.current;

    if (!iframe) {
      return;
    }

    const handleLoad = () => {
      setIframeDocument(iframe.contentDocument);
    };

    handleLoad();
    iframe.addEventListener('load', handleLoad);

    return () => {
      iframe.removeEventListener('load', handleLoad);
    };
  }, []);

  useEffect(() => {
    if (!iframeDocument) {
      return;
    }

    iframeDocument.querySelectorAll('[data-preview-cloned="true"]').forEach((node) => node.remove());
    iframeDocument.querySelectorAll('[data-preview-external-style="true"]').forEach((node) => node.remove());

    Array.from(document.querySelectorAll('style, link[rel="stylesheet"]')).forEach((node) => {
      const clone = node.cloneNode(true) as HTMLElement;
      clone.setAttribute('data-preview-cloned', 'true');
      iframeDocument.head.appendChild(clone);
    });

    const siteUrl = tutorConfig.site_url.replace(/\/$/, '');

    if (!siteUrl || PREVIEW_STYLESHEET_PATHS.length === 0) {
      setCssLoaded(true);
      return;
    }

    let loadedCount = 0;
    const totalLinks = PREVIEW_STYLESHEET_PATHS.length;

    const handleLoadOrError = () => {
      loadedCount++;
      if (loadedCount === totalLinks) {
        setCssLoaded(true);
      }
    };

    PREVIEW_STYLESHEET_PATHS.forEach((path) => {
      const link = iframeDocument.createElement('link');
      link.rel = 'stylesheet';
      link.href = `${siteUrl}${path}`;
      link.setAttribute('data-preview-external-style', 'true');
      link.onload = handleLoadOrError;
      link.onerror = handleLoadOrError;
      iframeDocument.head.appendChild(link);
    });
  }, [iframeDocument]);

  useEffect(() => {
    if (!iframeDocument?.body) {
      return;
    }

    iframeDocument.body.setAttribute('data-preview-device', activeTab);

    if (tutorConfig.settings?.learning_mode === 'kids') {
      iframeDocument.body.setAttribute('data-tutor-ui', 'kids');
      return;
    }

    iframeDocument.body.removeAttribute('data-tutor-ui');
  }, [activeTab, iframeDocument]);

  useLayoutEffect(() => {
    if (!iframeDocument || !iframeRef.current) {
      return;
    }

    const iframe = iframeRef.current;
    const root = iframeDocument.getElementById('preview-root');

    if (!root) {
      return;
    }

    const syncHeight = () => {
      iframe.style.height = `${root.scrollHeight}px`;
    };

    syncHeight();

    if (typeof ResizeObserver === 'undefined') {
      const frame = requestAnimationFrame(syncHeight);

      return () => {
        cancelAnimationFrame(frame);
      };
    }

    const resizeObserver = new ResizeObserver(() => {
      syncHeight();
    });

    resizeObserver.observe(root);

    return () => {
      resizeObserver.disconnect();
    };
  }, [activeTab, iframeDocument, question]);

  return (
    <FocusTrap blurPrevious={false}>
      <div css={styles.container}>
        <div css={styles.wrapper}>
          <button type="button" css={styleUtils.crossButton} onClick={onClose}>
            <SVGIcon name="cross" />
          </button>

          <div css={styles.questionPreviewWrapper({ activeTab })}>
            <div css={styles.scrollContainer}>
              <iframe
                ref={iframeRef}
                title={__('Question preview', 'tutor')}
                srcDoc={IFRAME_SRC_DOC}
                css={styles.previewIframe({ activeTab, cssLoaded })}
              />
              {iframeDocument?.getElementById('preview-root')
                ? createPortal(
                    <PreviewDocumentContent
                      activeTab={activeTab}
                      question={question}
                      previewQuestionStyleType={previewQuestionStyleType}
                    />,
                    iframeDocument.getElementById('preview-root') as HTMLElement,
                  )
                : null}
            </div>
          </div>

          <div css={styles.tabsWrapper}>
            <Tabs
              wrapperCss={styles.tabsStyle}
              activeTab={activeTab}
              onChange={(tab) => setActiveTab(tab)}
              tabList={[
                {
                  label: '',
                  value: 'desktop',
                  icon: <SVGIcon name="mac" width={20} height={20} />,
                },
                {
                  label: '',
                  value: 'mobile',
                  icon: <SVGIcon name="iosPhone" width={24} height={24} />,
                },
              ]}
            />
          </div>
        </div>
      </div>
    </FocusTrap>
  );
};

const PreviewDocumentContent = ({
  question,
  previewQuestionStyleType,
}: {
  activeTab: 'desktop' | 'mobile';
  question: QuizQuestion;
  previewQuestionStyleType: string;
}) => {
  return (
    <>
      <style>{getPreviewFrameStyles()}</style>
      <div className="tutor-preview-stage">
        <div className="tutor-quiz tutor-quiz-submission">
          <div className="tutor-quiz-question-wrapper">
            <div className="tutor-quiz-question" data-question={previewQuestionStyleType}>
              <div className="tutor-quiz-question-header">
                <div className="tutor-quiz-question-number">{question.question_order || 1}</div>
                <div className="tutor-quiz-question-title">
                  {question.question_title}
                  <Show when={question.question_description}>
                    <div className="tutor-p2 tutor-text-secondary tutor-preview-description">
                      <WPEditor value={question.question_description} readonly min_height={24} onChange={() => {}} />
                    </div>
                  </Show>
                </div>
                <Show when={question.question_settings.show_question_mark}>
                  <span className="tutor-badge tutor-badge-rounded tutor-text-secondary">
                    <span className="tutor-text-subdued">{__('Points: ', 'tutor')}</span>
                    {question.question_mark}
                  </span>
                </Show>
              </div>

              {renderQuestionPreview(question)}
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

const getPreviewQuestionType = (question: QuizQuestion): QuizQuestionType => {
  if (question.question_type === 'single_choice') {
    return 'multiple_choice';
  }

  if (question.question_type === 'matching' && question.question_settings.is_image_matching) {
    return 'image_matching';
  }

  return question.question_type;
};

const getPreviewQuestionStyleType = (question: QuizQuestion) => {
  const previewQuestionType = getPreviewQuestionType(question);

  if (previewQuestionType === 'image_matching') {
    return 'matching';
  }

  return previewQuestionType;
};

const renderQuestionPreview = (question: QuizQuestion) => {
  switch (getPreviewQuestionType(question)) {
    case 'true_false':
      return <TrueFalsePreview answers={question.question_answers} />;
    case 'multiple_choice':
      return (
        <MultipleChoicePreview
          answers={question.question_answers}
          hasMultipleCorrectAnswer={question.question_settings.has_multiple_correct_answer}
        />
      );
    case 'open_ended':
    case 'short_answer':
      return <OpenEndedPreview questionType={question.question_type} />;
    case 'fill_in_the_blank':
      return <FillInTheBlankPreview answers={question.question_answers} />;
    case 'matching':
    case 'image_matching':
      return (
        <MatchingPreview
          answers={question.question_answers}
          isImageMatching={!!question.question_settings.is_image_matching}
        />
      );
    case 'image_answering':
      return <ImageAnsweringPreview answers={question.question_answers} />;
    case 'ordering':
      return <OrderingPreview answers={question.question_answers} />;
    case 'pin_image':
      return <PinImagePreview answers={question.question_answers} />;
    case 'draw_image':
      return <DrawImagePreview answers={question.question_answers} />;
    case 'scale':
      return <ScalePreview answers={question.question_answers} />;
    case 'coordinates':
      return <CoordinatesPreview />;
    default:
      return <UnsupportedPreview />;
  }
};

const getPreviewFrameStyles = () => `
  :root {
    color-scheme: light;
  }

  html,
  body {
    margin: 0;
    padding: 0;
    background: transparent;
  }

  body {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100%;
  }

  #preview-root {
    box-sizing: border-box;
    width: 100%;
  }

  .tutor-preview-stage {
    width: 100%;
    margin-inline: auto;
  }
  
  .tutor-quiz-submission {
    padding-top: 0;
  }

  [data-question=fill_in_the_blank] .tutor-quiz-question-input {
    box-shadow: none;
  }

  .tutor-quiz-question-option {
    cursor: default;
  }
`;

export default QuestionPreviewModal;

const styles = {
  container: css`
    position: relative;
    overflow: hidden;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    ${Breakpoint.smallTablet} {
      width: 90%;
    }
  `,
  wrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    align-items: center;
  `,
  questionPreviewWrapper: ({ activeTab }: { activeTab: 'desktop' | 'mobile' }) => css`
    display: flex;
    flex-direction: column;
    overflow: hidden;
    max-width: ${activeTab === 'mobile' ? '444px' : '1220px'};
    height: 686px;
    margin-inline: auto;
    width: 100%;
    background-color: ${colorTokens.surface.courseBuilder};
    border-radius: ${borderRadius[14]};
    transition: max-width 240ms cubic-bezier(0.22, 1, 0.36, 1);

    @media (prefers-reduced-motion: reduce) {
      transition: none;
    }
  `,
  scrollContainer: css`
    ${styleUtils.overflowYAuto};
    display: flex;
    justify-content: center;
    align-items: flex-start;
    flex: 1;
    min-height: 0;
    width: 100%;
    padding: ${spacing[12]};
  `,
  previewIframe: ({ activeTab, cssLoaded }: { activeTab: 'desktop' | 'mobile'; cssLoaded: boolean }) => css`
    width: 100%;
    max-width: ${activeTab === 'mobile' ? '420px' : '960px'};
    border: 0;
    min-height: 100%;
    background: transparent;
    display: block;
    margin-inline: auto;
    flex: 0 0 auto;
    opacity: ${cssLoaded ? 1 : 0};
    transition:
      max-width 240ms cubic-bezier(0.22, 1, 0.36, 1),
      opacity 160ms ease;

    @media (prefers-reduced-motion: reduce) {
      transition: none;
    }
  `,
  tabsWrapper: css`
    margin-top: ${spacing[16]};

    span {
      display: none;
    }
  `,
  tabsStyle: css`
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[8]};

    button {
      min-width: unset;
      max-height: 44px;

      &[aria-selected='true'] {
        background-color: #e4ebfc;
        border-radius: ${borderRadius[8]};
      }
    }
  `,
};
