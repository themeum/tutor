import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import WPEditor from '@TutorShared/atoms/WPEditor';
import FocusTrap from '@TutorShared/components/FocusTrap';
import { type ModalProps } from '@TutorShared/components/modals/Modal';
import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import Tabs from '@TutorShared/molecules/Tabs';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type QuizQuestion, type QuizQuestionOption, type QuizQuestionType } from '@TutorShared/utils/types';

interface QuestionPreviewModalProps extends ModalProps {
  question: QuizQuestion;
  onClose: () => void;
}

const IFRAME_SRC_DOC =
  '<!doctype html><html><head><meta charset="utf-8" /></head><body><div id="preview-root"></div></body></html>';
const PREVIEW_STYLESHEET_PATHS = [
  '/wp-content/plugins/tutor/assets/css/tutor-learning-area.min.css',
  '/wp-content/plugins/tutor/assets/css/tutor-core.min.css',
  '/wp-content/plugins/tutor/assets/css/tutor-kids.min.css',
];

const QuestionPreviewModal = ({ question, onClose }: QuestionPreviewModalProps) => {
  const [activeTab, setActiveTab] = useState<'desktop' | 'mobile'>('desktop');
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

    if (siteUrl) {
      PREVIEW_STYLESHEET_PATHS.forEach((path) => {
        const link = iframeDocument.createElement('link');
        link.rel = 'stylesheet';
        link.href = `${siteUrl}${path}`;
        link.setAttribute('data-preview-external-style', 'true');
        iframeDocument.head.appendChild(link);
      });
    }
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
      iframe.style.height = `${Math.max(root.scrollHeight, activeTab === 'mobile' ? 520 : 620)}px`;
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
            <iframe
              ref={iframeRef}
              title={__('Question preview', 'tutor')}
              srcDoc={IFRAME_SRC_DOC}
              css={styles.previewIframe({ activeTab })}
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
                    <div
                      className="tutor-p2 tutor-text-secondary tutor-preview-description"
                      // dangerouslySetInnerHTML={{ __html: question.question_description }}
                    >
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
    default:
      return <UnsupportedPreview />;
  }
};

const TrueFalsePreview = ({ answers }: { answers: QuizQuestionOption[] }) => (
  <div className="tutor-quiz-question-options">
    {answers.map((answer) => (
      <label key={answer.answer_id} className="tutor-quiz-question-option">
        <SVGIcon name={__('True', 'tutor') === answer.answer_title ? 'check2' : 'cross'} width={20} height={20} />
        {answer.answer_title}
      </label>
    ))}
  </div>
);

const MultipleChoicePreview = ({
  answers,
  hasMultipleCorrectAnswer,
}: {
  answers: QuizQuestionOption[];
  hasMultipleCorrectAnswer: boolean;
}) => (
  <div className="tutor-quiz-question-options">
    {answers.map((answer, index) => {
      const hasImage = !!answer.image_url;

      return (
        <label key={answer.answer_id || index} className="tutor-quiz-question-option">
          <Show
            when={!hasImage}
            fallback={
              <>
                <img src={answer.image_url} alt={answer.answer_title} />
                <div data-title>{answer.answer_title}</div>
              </>
            }
          >
            <div className="tutor-input-field">
              <div className="tutor-input-wrapper">
                <input
                  type={hasMultipleCorrectAnswer ? 'checkbox' : 'radio'}
                  className={hasMultipleCorrectAnswer ? 'tutor-checkbox' : 'tutor-radio'}
                  disabled
                />
                <label>{answer.answer_title}</label>
              </div>
            </div>
          </Show>
        </label>
      );
    })}
  </div>
);

const OpenEndedPreview = ({ questionType }: { questionType: QuizQuestionType }) => (
  <div className="tutor-quiz-question-options">
    <textarea
      className="tutor-form-textarea tutor-quiz-question-input"
      placeholder={
        questionType === 'short_answer'
          ? __('Type your short answer here', 'tutor')
          : __('Type your answer here', 'tutor')
      }
      disabled
      rows={5}
    />
  </div>
);

const FillInTheBlankPreview = ({ answers }: { answers: QuizQuestionOption[] }) => (
  <div className="tutor-quiz-question-options">
    {answers.map((answer, index) => (
      <div key={answer.answer_id || index} className="tutor-quiz-question-option">
        {renderFillInBlankText(answer, index)}
      </div>
    ))}
  </div>
);

const MatchingPreview = ({ answers, isImageMatching }: { answers: QuizQuestionOption[]; isImageMatching: boolean }) => {
  const draggableAnswers = [...answers].sort((first, second) => first.answer_order - second.answer_order);

  return (
    <div className="tutor-flex tutor-flex-column tutor-gap-7 tutor-sm-gap-5">
      <div className="tutor-quiz-question-options" data-image-matching={isImageMatching ? '1' : '0'}>
        {answers.map((answer, index) => (
          <div key={answer.answer_id || index} className="tutor-quiz-question-option">
            <Show
              when={isImageMatching && answer.image_url}
              fallback={
                <div data-title>
                  <div className="tutor-quiz-question-option-number">{answer.answer_order || index + 1}</div>
                  {answer.answer_title}
                </div>
              }
            >
              <img src={answer.image_url} alt={answer.answer_title} />
            </Show>
            <div className="tutor-quiz-question-option-drop-zone" data-drop-placeholder-text={__('Drop here', 'tutor')}>
              <span data-drop-placeholder className="tutor-text-subdued">
                {__('Drop here', 'tutor')}
              </span>
            </div>
          </div>
        ))}
      </div>

      <div className="tutor-quiz-question-draggable">
        <div className="tutor-quiz-question-draggable-header">
          <SVGIcon name="drag" width={20} height={20} />
          <span className="tutor-text-small tutor-font-medium">{__('Drag from here', 'tutor')}</span>
        </div>
        <div className="tutor-quiz-question-options">
          {draggableAnswers.map((answer, index) => (
            <div
              key={answer.answer_id || index}
              className="tutor-quiz-question-option"
              data-option="draggable"
              data-id={answer.answer_id}
            >
              <div data-title>
                <Show when={isImageMatching} fallback={answer.answer_two_gap_match}>
                  {answer.answer_title}
                </Show>
              </div>
              <button type="button" data-grab-handle disabled>
                <SVGIcon name="grabHandle" width={24} height={24} />
              </button>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

const ImageAnsweringPreview = ({ answers }: { answers: QuizQuestionOption[] }) => (
  <div className="tutor-quiz-question-options">
    {answers.map((answer, index) => (
      <div key={answer.answer_id || index} className="tutor-quiz-question-option">
        <Show when={answer.image_url}>
          <img src={answer.image_url} alt={answer.answer_title} />
        </Show>
        <input
          type="text"
          className="tutor-form-control tutor-quiz-question-input"
          placeholder={__('Write your answer here', 'tutor')}
          disabled
        />
      </div>
    ))}
  </div>
);

const OrderingPreview = ({ answers }: { answers: QuizQuestionOption[] }) => (
  <div className="tutor-quiz-question-options">
    {answers.map((answer, index) => (
      <div
        key={answer.answer_id || index}
        className="tutor-quiz-question-option"
        data-option="draggable"
        data-id={answer.answer_id}
      >
        <div data-option-order>{answer.answer_order || index + 1}</div>
        <div data-title>
          {answer.image_url ? <img src={answer.image_url} alt={answer.answer_title} /> : null}
          {answer.answer_title}
        </div>

        <button type="button" data-grab-handle disabled>
          <SVGIcon name="grabHandle" width={24} height={24} />
        </button>
      </div>
    ))}
  </div>
);

const UnsupportedPreview = () => (
  <div className="tutor-quiz-question-options">
    <div className="tutor-quiz-question-option">
      {__('Preview is not available for this question type yet.', 'tutor')}
    </div>
  </div>
);

const renderFillInBlankText = (answer: QuizQuestionOption, index: number) => {
  const parts = (answer.answer_title || '').split('{dash}');

  return parts.flatMap((part, partIndex) => {
    const nodes = [];

    if (part) {
      nodes.push(<span key={`${answer.answer_id || index}-text-${partIndex}`}>{part}</span>);
    }

    if (partIndex < parts.length - 1) {
      nodes.push(
        <input
          key={`${answer.answer_id || index}-input-${partIndex}`}
          type="text"
          className="tutor-quiz-question-input"
          placeholder={__('Type your answer here', 'tutor')}
          disabled
        />,
      );
    }

    return nodes;
  });
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
    padding: 12px;
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
    ${styleUtils.overflowYAuto};
    display: flex;
    justify-content: center;
    align-items: ${activeTab === 'mobile' ? 'flex-start' : 'center'};
    max-width: ${activeTab === 'mobile' ? '444px' : '1220px'};
    max-height: min(80vh, 900px);
    margin-inline: auto;
    min-height: 578px;
    width: 100%;
    padding: ${spacing[12]};
    background-color: ${colorTokens.surface.courseBuilder};
    border-radius: ${borderRadius[14]};
    transition:
      max-width 240ms cubic-bezier(0.22, 1, 0.36, 1),
      min-height 240ms cubic-bezier(0.22, 1, 0.36, 1);

    @media (prefers-reduced-motion: reduce) {
      transition: none;
    }
  `,
  previewIframe: ({ activeTab }: { activeTab: 'desktop' | 'mobile' }) => css`
    width: 100%;
    max-width: ${activeTab === 'mobile' ? '420px' : '960px'};
    border: 0;
    background: transparent;
    display: block;
    margin-inline: auto;
    flex: 0 0 auto;
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
