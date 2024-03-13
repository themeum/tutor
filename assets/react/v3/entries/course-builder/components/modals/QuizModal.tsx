import SVGIcon from '@Atoms/SVGIcon';
import FormSelectInput from '@Components/fields/FormSelectInput';
import { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';
import { Option } from '@Utils/types';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';

interface QuizModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

export type QuizType =
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
  quiz_type: QuizType;
  answer_required: boolean;
  randomize: boolean;
  point: number;
  display_point: boolean;
}

const QuizModal = ({ closeModal, icon, title, subtitle, actions }: QuizModalProps) => {
  const form = useFormWithGlobalError<QuizForm>({
    defaultValues: {
      quiz_type: 'true-false',
      answer_required: false,
      randomize: false,
      point: 0,
      display_point: true,
    },
  });

  const quizTypeOptions: Option<QuizType>[] = [
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

  return (
    <ModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      icon={icon}
      title={title}
      subtitle={subtitle}
      actions={actions}
    >
      <div css={styles.wrapper}>
        <div css={styles.left}>
          <div css={styles.quizName}>General Knowledge</div>
          <div css={styles.questionsLabel}>
            <span>Questions</span>
            <button type="button">
              <SVGIcon name="plusSquareBrand" />
            </button>
          </div>
          <div css={styles.questionList}>@TODO: Question list</div>
        </div>
        <div css={styles.content}>
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Nihil autem totam amet sequi reprehenderit numquam
          nostrum cumque ex ad earum! Nesciunt alias voluptate, quibusdam expedita delectus rerum ducimus et at.
        </div>
        <div css={styles.right}>
          <Controller
            control={form.control}
            name="quiz_type"
            render={controllerProps => (
              <FormSelectInput {...controllerProps} label="Quiz Type" options={quizTypeOptions} />
            )}
          />
        </div>
      </div>
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
    border-left: 1px solid ${colorTokens.stroke.divider};
    padding: ${spacing[8]} ${spacing[32]};
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
};
