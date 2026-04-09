import { css } from '@emotion/react';
import { useState } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import FocusTrap from '@TutorShared/components/FocusTrap';
import { type ModalProps } from '@TutorShared/components/modals/Modal';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import Tabs from '@TutorShared/molecules/Tabs';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type QuizQuestion } from '@TutorShared/utils/types';

// @ts-ignore
import '../../../../../../../core/scss/themes/_light.scss?inline';
// @ts-ignore
import '../../../../../../scss/frontend/components/_quiz-question.scss?inline';
// @ts-ignore
import '../../../../../../scss/frontend/learning-area/components/quiz/_quiz.scss?inline';

interface QuestionPreviewModalProps extends ModalProps {
  question: QuizQuestion;
  onClose: () => void;
}

const QuestionPreviewModal = ({ question, onClose }: QuestionPreviewModalProps) => {
  const [activeTab, setActiveTab] = useState<'desktop' | 'mobile'>('desktop');

  return (
    <FocusTrap blurPrevious={false}>
      <div css={styles.container}>
        <div css={styles.wrapper}>
          <button type="button" css={styleUtils.crossButton} onClick={onClose}>
            <SVGIcon name="cross" />
          </button>

          <div css={styles.questionPreviewWrapper}>
            <div className="tutor-quiz tutor-quiz-questions">
              <div className="tutor-quiz-question" data-question="true_false">
                <div className="tutor-quiz-question-header">
                  <div className="tutor-quiz-question-number">1 </div>
                  <div className="tutor-quiz-question-title">
                    {question.question_title}
                    <div className="tutor-p2 tutor-text-secondary">
                      <p>{question.question_description}</p>
                    </div>
                  </div>
                </div>
              </div>
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
  questionPreviewWrapper: css`
    ${styleUtils.flexCenter()};
    max-width: 1220px;
    width: 100%;
    padding: 132px ${spacing[12]};
    background-color: ${colorTokens.surface.courseBuilder};
    border-radius: ${borderRadius[14]};
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
