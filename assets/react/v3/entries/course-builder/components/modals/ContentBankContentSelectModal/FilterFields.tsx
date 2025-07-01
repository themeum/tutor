import { css } from '@emotion/react';
import Button from '@TutorShared/atoms/Button';
import Checkbox from '@TutorShared/atoms/CheckBox';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { type IconCollection } from '@TutorShared/icons/types';
import Popover from '@TutorShared/molecules/Popover';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type QuizQuestionType } from '@TutorShared/utils/types';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';

interface FilterFieldsProps {
  onFilterChange: (values: FilterFormValues) => void;
  initialValues?: FilterFormValues;
  type: 'lesson_assignment' | 'question';
}

interface FilterFormValues {
  contentTypes: ('lesson' | 'assignment' | 'question')[];
  order: 'asc' | 'desc';
  questionTypes: QuizQuestionType[];
}

const FilterFields = ({ onFilterChange, initialValues, type }: FilterFieldsProps) => {
  const form = useFormWithGlobalError<FilterFormValues>({
    defaultValues: initialValues || {
      contentTypes: [],
      order: 'asc',
      questionTypes: [],
    },
  });
  const [isFiltersPopoverOpen, setIsFiltersPopoverOpen] = useState(false);
  const filterButtonRef = useRef<HTMLButtonElement>(null);

  const order = form.watch('order') || 'asc';
  const contentTypes = form.watch('contentTypes') || [];
  const questionTypes = form.watch('questionTypes') || [];
  const isQuestionType = type === 'question';

  const questionTypeOptions: {
    label: string;
    value: QuizQuestionType;
    icon: IconCollection;
  }[] = [
    {
      label: __('True/False', 'tutor'),
      value: 'true_false',
      icon: 'quizTrueFalse',
    },
    {
      label: __('Multiple Choice', 'tutor'),
      value: 'multiple_choice',
      icon: 'quizMultiChoice',
    },
    {
      label: __('Open Ended/Essay', 'tutor'),
      value: 'open_ended',
      icon: 'quizEssay',
    },
    {
      label: __('Fill in the Blanks', 'tutor'),
      value: 'fill_in_the_blank',
      icon: 'quizFillInTheBlanks',
    },
    {
      label: __('Short Answer', 'tutor'),
      value: 'short_answer',
      icon: 'quizShortAnswer',
    },
    {
      label: __('Matching', 'tutor'),
      value: 'matching',
      icon: 'quizImageMatching',
    },
    {
      label: __('Image Answering', 'tutor'),
      value: 'image_answering',
      icon: 'quizImageAnswer',
    },
    {
      label: __('Ordering', 'tutor'),
      value: 'ordering',
      icon: 'quizOrdering',
    },
  ];

  return (
    <>
      <div css={styles.wrapper}>
        <Show when={(!isQuestionType && contentTypes.length > 0) || (isQuestionType && questionTypes.length > 0)}>
          <Button
            size="small"
            variant="text"
            buttonCss={styles.clearButton}
            onClick={() => {
              form.reset({
                contentTypes: [],
                questionTypes: [],
                order: 'asc',
              });
              onFilterChange(form.getValues());
            }}
          >
            {__('Clear All', 'tutor')}
          </Button>
        </Show>

        <Button
          ref={filterButtonRef}
          variant="tertiary"
          icon={<SVGIcon name="mageFilter" height={16} width={16} />}
          buttonCss={styles.filterButton}
          onClick={() => setIsFiltersPopoverOpen((prev) => !prev)}
        >
          <span>{__('Filters', 'tutor')}</span>

          <Show when={(!isQuestionType && contentTypes.length > 0) || (isQuestionType && questionTypes.length > 0)}>
            <div css={styles.filterCount}>
              <div css={styles.divider} />
              <span>{isQuestionType ? questionTypes.length : contentTypes.length}</span>
            </div>
          </Show>
        </Button>

        <Button
          variant="tertiary"
          icon={<SVGIcon name={order === 'asc' ? 'sortASC' : 'sortDESC'} width={18} height={18} />}
          buttonCss={styles.sortButton}
          onClick={() => {
            const newOrder = order === 'asc' ? 'desc' : 'asc';
            form.setValue('order', newOrder);
            onFilterChange({ ...form.getValues(), order: newOrder });
          }}
          aria-label={__('Order by', 'tutor')}
        />
      </div>

      <Popover
        isOpen={isFiltersPopoverOpen}
        closeOnEscape
        closePopover={() => {
          form.reset();
          setIsFiltersPopoverOpen(false);
        }}
        triggerRef={filterButtonRef}
        maxWidth={!isQuestionType ? '200px' : '300px'}
      >
        <div css={styles.filterFieldsWrapper}>
          <h6>{__('Filter by', 'tutor')}</h6>
          {/* Add filter fields here */}
          <div css={styles.filterFields}>
            <Show
              when={!isQuestionType}
              fallback={
                <>
                  <For each={questionTypeOptions}>
                    {(option) => (
                      <Checkbox
                        label={
                          <div css={styles.questionType}>
                            <SVGIcon name={option.icon} height={24} width={24} />
                            <span>{option.label}</span>
                          </div>
                        }
                        checked={questionTypes.includes(option.value as QuizQuestionType)}
                        onChange={(isChecked) => {
                          const newTypes = isChecked
                            ? [...questionTypes, option.value]
                            : questionTypes.filter((type) => type !== option.value);
                          form.setValue('questionTypes', newTypes as QuizQuestionType[]);
                        }}
                      />
                    )}
                  </For>
                </>
              }
            >
              <Checkbox
                label={__('Lesson', 'tutor')}
                checked={form.watch('contentTypes').includes('lesson')}
                onChange={(isChecked) => {
                  const newTypes = isChecked
                    ? [...contentTypes, 'lesson']
                    : contentTypes.filter((type) => type !== 'lesson');
                  form.setValue('contentTypes', newTypes as ('lesson' | 'assignment' | 'question')[]);
                }}
              />

              <Checkbox
                label={__('Assignment', 'tutor')}
                checked={form.watch('contentTypes').includes('assignment')}
                onChange={(isChecked) => {
                  const newTypes = isChecked
                    ? [...contentTypes, 'assignment']
                    : contentTypes.filter((type) => type !== 'assignment');
                  form.setValue('contentTypes', newTypes as ('lesson' | 'assignment' | 'question')[]);
                }}
              />
            </Show>
          </div>

          <div css={styles.filterActions}>
            <Button
              size="small"
              variant="primary"
              onClick={() => {
                onFilterChange(form.getValues());
                setIsFiltersPopoverOpen(false);
              }}
            >
              {__('Apply Filters', 'tutor')}
            </Button>
          </div>
        </div>
      </Popover>
    </>
  );
};

export default FilterFields;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex()};
    gap: ${spacing[12]};
  `,
  filterButton: css`
    padding-inline: ${spacing[8]};
  `,
  sortButton: css`
    padding: ${spacing[10]} ${spacing[12]};
    background-color: ${colorTokens.background.white};
    color: ${colorTokens.icon.default};
  `,
  clearButton: css`
    flex-shrink: 0;
  `,
  questionType: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[12]};
    color: ${colorTokens.text.primary};
  `,
  divider: css`
    width: 1px;
    height: 24px;
    background-color: ${colorTokens.stroke.divider};
  `,
  filterCount: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
    margin-left: ${spacing[8]};

    span {
      height: 24px;
      width: 24px;
      ${styleUtils.display.flex()};
      align-items: center;
      justify-content: center;
      padding: ${spacing[4]};
      border-radius: ${borderRadius[4]};
      background-color: ${colorTokens.primary[50]};
      color: ${colorTokens.text.brand};
    }
  `,
  filterFieldsWrapper: css`
    ${styleUtils.display.flex('column')};

    h6 {
      padding: ${spacing[12]} ${spacing[12]} ${spacing[6]} ${spacing[12]};
      ${typography.caption()};
      color: ${colorTokens.text.hints};
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    }
  `,
  filterFields: css`
    padding: ${spacing[6]} ${spacing[12]} ${spacing[6]} ${spacing[16]};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
    background-color: ${colorTokens.surface.tutor};
    border-radius: ${borderRadius[8]};
  `,
  filterActions: css`
    padding: ${spacing[6]} ${spacing[12]} ${spacing[12]} ${spacing[12]};

    button {
      width: 100%;
    }
  `,
};
