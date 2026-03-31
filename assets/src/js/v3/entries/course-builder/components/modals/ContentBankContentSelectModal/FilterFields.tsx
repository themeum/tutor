import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import Checkbox from '@TutorShared/atoms/CheckBox';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Popover from '@TutorShared/molecules/Popover';

import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { type IconCollection } from '@TutorShared/icons/types';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type QuizQuestionType } from '@TutorShared/utils/types';

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
      <div data-filter css={styles.wrapper}>
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

        <Show when={isQuestionType}>
          <div>
            <Button
              ref={filterButtonRef}
              variant="tertiary"
              icon={<SVGIcon name="mageFilter" height={16} width={16} />}
              buttonCss={styles.filterButton({
                hasFilters: questionTypes.length > 0,
              })}
              onClick={() => setIsFiltersPopoverOpen((prev) => !prev)}
            >
              <span>{__('Filters', 'tutor')}</span>

              <Show when={isQuestionType && questionTypes.length > 0}>
                <div css={styles.filterCount}>
                  <div css={styles.divider} />
                  <span>{questionTypes.length}</span>
                </div>
              </Show>
            </Button>
          </div>
        </Show>

        <div>
          <Button
            variant="tertiary"
            isIconOnly
            icon={<SVGIcon name={order === 'asc' ? 'sortASC' : 'sortDESC'} width={18} height={18} />}
            onClick={() => {
              const newOrder = order === 'asc' ? 'desc' : 'asc';
              form.setValue('order', newOrder);
              onFilterChange({ ...form.getValues(), order: newOrder });
            }}
            aria-label={__('Order by', 'tutor')}
          />
        </div>
      </div>

      <Popover
        isOpen={isFiltersPopoverOpen}
        closeOnEscape
        animationType={AnimationType.slideDown}
        closePopover={() => {
          form.reset();
          setIsFiltersPopoverOpen(false);
        }}
        triggerRef={filterButtonRef}
        maxWidth={!isQuestionType ? '200px' : '240px'}
      >
        <div css={styles.filterFieldsWrapper}>
          <h6>{__('Filter by', 'tutor')}</h6>
          <div css={styles.filterFields}>
            <Show when={isQuestionType}>
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
  filterButton: ({ hasFilters = false }) => css`
    height: 40px;
    padding-inline: ${spacing[12]} ${spacing[16]};
    background-color: ${colorTokens.background.white};

    ${hasFilters &&
    css`
      padding-right: ${spacing[6]};
    `}
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
      height: 26px;
      width: 26px;
      ${styleUtils.display.flex()};
      align-items: center;
      justify-content: center;
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
