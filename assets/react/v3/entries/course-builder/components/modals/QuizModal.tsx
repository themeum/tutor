import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSwitch from '@Components/fields/FormSwitch';
import { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { AnimationType } from '@Hooks/useAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import ConfirmationPopover from '@Molecules/ConfirmationPopover';
import { styleUtils } from '@Utils/style-utils';
import { Option } from '@Utils/types';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';
import { Controller } from 'react-hook-form';

interface QuizModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

export type QuestionType =
  | 'true-false'
  | 'single-choice'
  | 'multiple-choice'
  | 'open-ended'
  | 'fill-in-the-blanks'
  | 'short-answer'
  | 'matching'
  | 'image-matching'
  | 'image-answering'
  | 'ordering';

interface QuizForm {
  question_type: QuestionType;
  answer_required: boolean;
  randomize: boolean;
  point: number;
  display_point: boolean;
}

const questionTypeOptions: Option<QuestionType>[] = [
  {
    label: __('True/ False', 'tutor'),
    value: 'true-false',
    icon: 'quizTrueFalse',
  },
  {
    label: __('Single Choice', 'tutor'),
    value: 'single-choice',
    icon: 'quizSingleChoice',
  },
  {
    label: __('Multiple Choice', 'tutor'),
    value: 'multiple-choice',
    icon: 'quizMultiChoice',
  },
  {
    label: __('Open Ended/ Essay', 'tutor'),
    value: 'open-ended',
    icon: 'quizEssay',
  },
  {
    label: __('Fill in the Blanks', 'tutor'),
    value: 'fill-in-the-blanks',
    icon: 'quizFillInTheBlanks',
  },
  {
    label: __('Short Answer', 'tutor'),
    value: 'short-answer',
    icon: 'quizShortAnswer',
  },
  {
    label: __('Matching', 'tutor'),
    value: 'matching',
    icon: 'quizMatching',
  },
  {
    label: __('Image Matching', 'tutor'),
    value: 'image-matching',
    icon: 'quizImageMatching',
  },
  {
    label: __('Image Answering', 'tutor'),
    value: 'image-answering',
    icon: 'quizImageAnswer',
  },
  {
    label: __('Ordering', 'tutor'),
    value: 'ordering',
    icon: 'quizOrdering',
  },
];

const QuizModal = ({ closeModal, icon, title, subtitle }: QuizModalProps) => {
  const [isConfirmationOpen, setIsConfirmationOpen] = useState(false);

  const cancelRef = useRef<HTMLButtonElement>(null);

  const form = useFormWithGlobalError<QuizForm>({
    defaultValues: {
      question_type: 'true-false',
      answer_required: false,
      randomize: false,
      point: 0,
      display_point: true,
    },
  });

  const { isDirty } = form.formState;

  return (
    <ModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      icon={icon}
      title={title}
      subtitle={subtitle}
      actions={
        <>
          <Button
            variant="text"
            size="small"
            onClick={() => {
              if (isDirty) {
                setIsConfirmationOpen(true);
                return;
              }

              closeModal();
            }}
            ref={cancelRef}
          >
            {__('Cancel', 'tutor')}
          </Button>
          <Button variant="primary" size="small" onClick={() => alert('@TODO: will be implemented later')}>
            {__('Next', 'tutor')}
          </Button>
        </>
      }
    >
      <div css={styles.wrapper}>
        <div css={styles.left}>
          <div css={styles.quizName}>General Knowledge</div>
          <div css={styles.questionsLabel}>
            <span>Questions</span>
            <button type="button" onClick={() => alert('@TODO: will be implemented later')}>
              <SVGIcon name="plusSquareBrand" />
            </button>
          </div>
          <div css={styles.questionList}>@TODO: Question list</div>
        </div>
        <div css={styles.content}>@TODO: Question content</div>
        <div css={styles.right}>
          <div css={styles.questionTypeWrapper}>
            <Controller
              control={form.control}
              name="question_type"
              render={controllerProps => (
                <FormSelectInput {...controllerProps} label="Question Type" options={questionTypeOptions} />
              )}
            />
          </div>
          <div css={styles.conditions}>
            <p>{__('Conditions', 'tutor')}</p>
            <div css={styles.conditionControls}>
              <Controller
                control={form.control}
                name="answer_required"
                render={controllerProps => <FormSwitch {...controllerProps} label={__('Answer Required', 'tutor')} />}
              />
              <Controller
                control={form.control}
                name="randomize"
                render={controllerProps => <FormSwitch {...controllerProps} label={__('Randomize Choice', 'tutor')} />}
              />
              <Controller
                control={form.control}
                name="point"
                render={controllerProps => (
                  <FormInput
                    {...controllerProps}
                    label={__('Point For This Answer', 'tutor')}
                    type="number"
                    isInlineLabel
                    style={css`
                      max-width: 72px;
                    `}
                  />
                )}
              />
              <Controller
                control={form.control}
                name="display_point"
                render={controllerProps => <FormSwitch {...controllerProps} label={__('Display Points', 'tutor')} />}
              />
            </div>
          </div>
        </div>
      </div>
      <ConfirmationPopover
        isOpen={isConfirmationOpen}
        triggerRef={cancelRef}
        closePopover={() => setIsConfirmationOpen(false)}
        maxWidth="258px"
        title={__('Do you want to cancel the progress without saving?', 'tutor')}
        message="There is unsaved changes."
        animationType={AnimationType.slideUp}
        arrow="top"
        positionModifier={{ top: -50, left: 0 }}
        hideArrow
        confirmButton={{
          text: __('Yes', 'tutor'),
          variant: 'primary',
        }}
        cancelButton={{
          text: __('No', 'tutor'),
          variant: 'text',
        }}
        onConfirmation={() => {
          closeModal();
        }}
      />
    </ModalWrapper>
  );
};

export default QuizModal;

const styles = {
  wrapper: css`
    width: 1217px;
    display: grid;
    grid-template-columns: 352px 1fr 352px;
    height: 100%;
  `,
  left: css`
    border-right: 1px solid ${colorTokens.stroke.divider};
  `,
  content: css`
    padding: ${spacing[32]};
  `,
  right: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    border-left: 1px solid ${colorTokens.stroke.divider};
  `,
  quizName: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[16]} ${spacing[32]} ${spacing[16]} ${spacing[28]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  questionsLabel: css`
    display: flex;
    gap: ${spacing[4]};
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    padding: ${spacing[16]} ${spacing[16]} ${spacing[16]} ${spacing[28]};

    ${typography.caption('medium')};
    color: ${colorTokens.text.subdued};

    button {
      ${styleUtils.resetButton};
      width: 32px;
      height: 32px;

      svg {
        color: ${colorTokens.action.primary.default};
        width: 100%;
        height: 100%;
      }
    }
  `,
  questionList: css`
    padding: ${spacing[8]} ${spacing[20]};
  `,
  questionTypeWrapper: css`
    padding: ${spacing[8]} ${spacing[32]} ${spacing[24]} ${spacing[24]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  conditions: css`
    padding: ${spacing[8]} ${spacing[32]} ${spacing[24]} ${spacing[24]};
    p {
      ${typography.body('medium')};
      color: ${colorTokens.text.primary};
    }
  `,
  conditionControls: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    margin-top: ${spacing[16]};
  `,
};
