import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSwitch from '@Components/fields/FormSwitch';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';
import { Option } from '@Utils/types';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
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
  quiz_title: string;
  quiz_description: string;
  question_type: QuestionType;
  answer_required: boolean;
  randomize: boolean;
  point: number;
  display_point: boolean;
}

const QuizModal = ({ closeModal, icon, title, subtitle, actions }: QuizModalProps) => {
  const form = useFormWithGlobalError<QuizForm>({
    defaultValues: {
      quiz_title: '',
      quiz_description: '',
      question_type: 'true-false',
      answer_required: false,
      randomize: false,
      point: 0,
      display_point: true,
    },
  });

  // @TODO: isEdit will be calculated based on the quiz data form API
  const [isEdit, setIsEdit] = useState<boolean>(true);

  const onQuizFormSubmit = (data: QuizForm) => {
    // @TODO: will be implemented later
    alert(JSON.stringify(data, null, 2));
    setIsEdit(false);
  };

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
          <div css={styles.quizTitleWrapper}>
            <Show
              when={isEdit}
              fallback={
                <div css={styles.quizNameWithButton}>
                  <span css={styles.quizTitle}>General knowledge</span>
                  <Button variant="text" type="button" onClick={() => setIsEdit(true)}>
                    <SVGIcon name="edit" width={24} height={24} />
                  </Button>
                </div>
              }
            >
              <div css={styles.quizForm}>
                <Controller
                  control={form.control}
                  name="quiz_title"
                  rules={{ required: __('Quiz title is required', 'tutor') }}
                  render={controllerProps => (
                    <FormInput {...controllerProps} placeholder={__('Add quiz title', 'tutor')} />
                  )}
                />
                <Controller
                  control={form.control}
                  name="quiz_description"
                  render={controllerProps => (
                    <FormTextareaInput
                      {...controllerProps}
                      placeholder={__('Add a summary', 'tutor')}
                      enableResize
                      rows={2}
                    />
                  )}
                />

                <div css={styles.quizFormButtonWrapper}>
                  <Button variant="text" type="button" onClick={() => setIsEdit(false)} size="small">
                    {__('Cancel', 'tutor')}
                  </Button>
                  <Button variant="secondary" type="submit" size="small" onClick={form.handleSubmit(onQuizFormSubmit)}>
                    {__('Ok', 'tutor')}
                  </Button>
                </div>
              </div>
            </Show>
          </div>
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
  quizTitleWrapper: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[16]} ${spacing[32]} ${spacing[16]} ${spacing[28]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  quizNameWithButton: css`
    display: inline-flex;
    width: 100%;
    transition: all 0.3s ease-in-out;

    button {
      display: none;
    }

    :hover {
      button {
        display: block;
      }
    }
  `,
  quizTitle: css`
    flex: 1;
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[8]};
    background-color: ${colorTokens.background.white};
    border-radius: ${borderRadius[6]};
  `,
  quizForm: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  quizFormButtonWrapper: css`
    display: flex;
    justify-content: end;
    margin-top: ${spacing[4]};
    gap: ${spacing[8]};
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
