import type { SyntheticListenerMap } from '@dnd-kit/core/dist/hooks/utilities';
import { css } from '@emotion/react';
import { animated } from '@react-spring/web';
import { __, sprintf } from '@wordpress/i18n';
import { memo, useEffect, useRef, useState } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import LoadingSpinner from '@TutorShared/atoms/LoadingSpinner';
import ProBadge from '@TutorShared/atoms/ProBadge';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Tooltip from '@TutorShared/atoms/Tooltip';
import ConfirmationPopover from '@TutorShared/molecules/ConfirmationPopover';

import FormInput from '@TutorShared/components/fields/FormInput';

import type { CourseTopicWithCollapse } from '@CourseBuilderPages/Curriculum';
import {
  useDeleteTopicMutation,
  useDuplicateContentMutation,
  useSaveTopicMutation,
} from '@CourseBuilderServices/curriculum';
import FormTextareaInput from '@TutorShared/components/fields/FormTextareaInput';
import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';

import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { useCollapseExpandAnimation } from '@TutorShared/hooks/useCollapseExpandAnimation';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';

import { getCourseId, getIdWithoutPrefix } from '@CourseBuilderUtils/utils';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type ID } from '@TutorShared/utils/types';
import { noop } from '@TutorShared/utils/util';

interface TopicHeaderProps {
  topic: CourseTopicWithCollapse;
  isEdit: boolean;
  isActive: boolean;
  listeners: SyntheticListenerMap | undefined;
  isDragging: boolean;
  onCollapse: (topicId: ID) => void;
  onEdit: (topicId: ID) => void;
  onCopy?: (topicId: ID) => void;
  onDelete?: () => void;
  setIsEdit: (isEdit: boolean) => void;
}

interface TopicForm {
  title: string;
  summary: string;
}

const courseId = getCourseId();
const isTutorPro = !!tutorConfig.tutor_pro_url;

const TopicHeader = ({
  topic,
  isEdit,
  listeners,
  isDragging,
  onCollapse,
  onEdit,
  onCopy,
  onDelete,
  setIsEdit,
}: TopicHeaderProps) => {
  const topicId = getIdWithoutPrefix('topic-', topic.id);

  const form = useFormWithGlobalError<TopicForm>({
    defaultValues: {
      title: topic.title,
      summary: topic.summary,
    },
    shouldFocusError: true,
  });

  const [isDeletePopoverOpen, setIsDeletePopoverOpen] = useState(false);

  const descriptionRef = useRef<HTMLDivElement>(null);
  const deleteRef = useRef<HTMLButtonElement>(null);

  const saveTopicMutation = useSaveTopicMutation();
  const duplicateContentMutation = useDuplicateContentMutation();
  const deleteTopicMutation = useDeleteTopicMutation(courseId);

  const collapseAnimationDescription = useCollapseExpandAnimation({
    ref: descriptionRef,
    isOpen: !topic.isCollapsed,
    heightCalculator: 'client',
  });

  const handleSubmit = async (values: TopicForm) => {
    const response = await saveTopicMutation.mutateAsync({
      ...(topic.isSaved && { topic_id: topicId }),
      course_id: courseId,
      title: values.title,
      summary: values.summary,
    });

    if (response.data) {
      setIsEdit(false);
    }

    if (response.status_code === 201) {
      onEdit?.(`topic-${response.data}`);
    }
  };

  const handleDuplicateTopic = async () => {
    const response = await duplicateContentMutation.mutateAsync({
      course_id: courseId,
      content_id: topicId,
      content_type: 'topic',
    });

    if (response.data) {
      onCopy?.(`topic-${response.data}`);
    }
  };

  useEffect(() => {
    if (isEdit) {
      form.setFocus('title');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isEdit]);

  return (
    <>
      <div css={styles.header({ isCollapsed: topic.isCollapsed, isEdit, isDeletePopoverOpen, isDragging })}>
        <div css={styles.headerContent({ isSaved: topic.isSaved })}>
          <div
            css={styles.grabberInput}
            onClick={() => onCollapse(topic.id)}
            onKeyDown={(event) => {
              if (event.key === 'Enter') {
                onCollapse(topic.id);
              }
            }}
          >
            <button
              {...(topic.isSaved ? listeners : {})}
              css={styles.grabButton({ isDragging: isDragging })}
              type="button"
              disabled={!topic.isSaved}
            >
              <SVGIcon name="dragVertical" width={24} height={24} />
            </button>

            <Show
              when={isEdit}
              fallback={
                <div css={styles.title({ isEdit })} onDoubleClick={() => setIsEdit(true)}>
                  {form.watch('title')}
                </div>
              }
            >
              <div css={styles.title({ isEdit })}>
                <Controller
                  control={form.control}
                  name="title"
                  rules={{ required: __('Title is required', 'tutor') }}
                  render={(controllerProps) => (
                    <FormInput {...controllerProps} placeholder={__('Add a title', 'tutor')} isSecondary />
                  )}
                />
              </div>
            </Show>
          </div>

          <div css={styles.actions} data-visually-hidden>
            <Show when={!isEdit}>
              <Tooltip content={__('Edit', 'tutor')} delay={200}>
                <button
                  data-cy="edit-topic"
                  type="button"
                  css={styleUtils.actionButton}
                  disabled={!topic.isSaved}
                  onClick={() => {
                    setIsEdit(true);
                    if (topic.isCollapsed) {
                      onCollapse?.(topic.id);
                    }
                  }}
                >
                  <SVGIcon name="edit" width={24} height={24} />
                </button>
              </Tooltip>
            </Show>
            <Show when={topic.isSaved}>
              <Show when={!duplicateContentMutation.isPending} fallback={<LoadingSpinner size={24} />}>
                <Tooltip content={__('Duplicate', 'tutor')} delay={200}>
                  <Show
                    when={!isTutorPro}
                    fallback={
                      <button
                        data-cy="duplicate-topic"
                        type="button"
                        css={styleUtils.actionButton}
                        disabled={!topic.isSaved}
                        onClick={handleDuplicateTopic}
                      >
                        <SVGIcon name="copyPaste" width={24} height={24} />
                      </button>
                    }
                  >
                    <ProBadge size="tiny">
                      <button type="button" css={styleUtils.actionButton} disabled onClick={noop}>
                        <SVGIcon name="copyPaste" width={24} height={24} />
                      </button>
                    </ProBadge>
                  </Show>
                </Tooltip>
              </Show>
            </Show>
            <Show when={topic.isSaved}>
              <Tooltip content={__('Delete', 'tutor')} delay={200}>
                <button
                  data-cy="delete-topic"
                  type="button"
                  css={styleUtils.actionButton}
                  disabled={!topic.isSaved}
                  data-visually-hidden
                  ref={deleteRef}
                  onClick={() => {
                    setIsDeletePopoverOpen(true);
                  }}
                >
                  <SVGIcon name="delete" width={24} height={24} />
                </button>
              </Tooltip>
            </Show>

            <Show when={topic.isSaved}>
              <button
                type="button"
                css={styleUtils.actionButton}
                disabled={!topic.isSaved}
                onClick={() => {
                  onCollapse?.(topic.id);
                }}
                data-toggle-collapse
              >
                <SVGIcon name={'chevronDown'} width={24} height={24} />
              </button>
            </Show>
          </div>
        </div>

        <Show
          when={isEdit}
          fallback={
            <Show when={topic.summary.length > 0}>
              <animated.div style={{ ...collapseAnimationDescription }}>
                <div css={styles.description({ isEdit })} ref={descriptionRef} onDoubleClick={() => setIsEdit(true)}>
                  {form.watch('summary')}
                </div>
              </animated.div>
            </Show>
          }
        >
          <div css={styles.description({ isEdit })}>
            <Controller
              control={form.control}
              name="summary"
              render={(controllerProps) => (
                <FormTextareaInput
                  {...controllerProps}
                  placeholder={__('Add a summary', 'tutor')}
                  isSecondary
                  rows={2}
                  enableResize
                />
              )}
            />
          </div>
        </Show>

        <Show when={isEdit}>
          <div css={styles.footer}>
            <Button
              variant="text"
              size="small"
              onClick={() => {
                if (!form.formState.isValid && !topic.isSaved) {
                  onDelete?.();
                }
                form.reset();
                setIsEdit(false);
              }}
            >
              {__('Cancel', 'tutor')}
            </Button>
            <Button
              data-cy="save-topic"
              loading={saveTopicMutation.isPending}
              variant="secondary"
              size="small"
              onClick={form.handleSubmit(handleSubmit)}
            >
              {__('Ok', 'tutor')}
            </Button>
          </div>
        </Show>
      </div>

      <ConfirmationPopover
        isOpen={isDeletePopoverOpen}
        triggerRef={deleteRef}
        isLoading={deleteTopicMutation.isPending}
        closePopover={noop}
        maxWidth="258px"
        title={
          /* translators: %s is the topic title */
          sprintf(__('Delete topic "%s"', 'tutor'), topic.title)
        }
        message={__('Are you sure you want to delete this content from your course? This cannot be undone.', 'tutor')}
        animationType={AnimationType.slideUp}
        confirmButton={{
          text: __('Delete', 'tutor'),
          variant: 'text',
          isDelete: true,
        }}
        cancelButton={{
          text: __('Cancel', 'tutor'),
          variant: 'text',
        }}
        onConfirmation={async () => {
          await deleteTopicMutation.mutateAsync(topicId);
          setIsDeletePopoverOpen(false);
          onDelete?.();
        }}
        onCancel={() => setIsDeletePopoverOpen(false)}
      />
    </>
  );
};

export default memo(TopicHeader, (prev, next) => {
  return (
    prev.topic.id === next.topic.id &&
    prev.topic.isSaved === next.topic.isSaved &&
    prev.topic.isCollapsed === next.topic.isCollapsed &&
    prev.isEdit === next.isEdit
  );
});

const styles = {
  header: ({
    isCollapsed,
    isEdit,
    isDeletePopoverOpen,
    isDragging,
  }: {
    isCollapsed: boolean;
    isEdit: boolean;
    isDeletePopoverOpen: boolean;
    isDragging: boolean;
  }) => css`
    padding: ${spacing[12]} ${spacing[16]};
    ${styleUtils.display.flex('column')};

    [data-toggle-collapse] {
      transition: transform 0.3s ease-in-out;
      ${!isCollapsed &&
      css`
        transform: rotate(180deg);
      `}
    }

    ${!isCollapsed &&
    css`
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    `}

    ${!isEdit &&
    css`
      padding-bottom: 0;
    `}

    ${!isEdit &&
    !isDeletePopoverOpen &&
    css`
      [data-visually-hidden] {
        opacity: 0;
        transition: ${!isDragging ? 'opacity 0.3s ease-in-out' : 'none'};
      }

      :hover,
      :focus-within {
        [data-visually-hidden] {
          opacity: ${isDragging ? 0 : 1};
        }
      }
    `}

    ${Breakpoint.smallTablet} {
      [data-visually-hidden] {
        opacity: 1;
      }
    }
  `,
  headerContent: ({ isSaved = true }: { isSaved: boolean }) => css`
    display: grid;
    grid-template-columns: ${isSaved ? '1fr auto' : '1fr'};
    gap: ${spacing[12]};
    width: 100%;
    padding-bottom: ${spacing[12]};
  `,
  grabberInput: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};

    svg {
      color: ${colorTokens.color.black[40]};
      flex-shrink: 0;
    }
  `,
  grabButton: ({ isDragging = false }: { isDragging: boolean }) => css`
    ${styleUtils.resetButton};
    ${styleUtils.flexCenter()};
    cursor: ${isDragging ? 'grabbing' : 'grab'};

    :disabled {
      cursor: not-allowed;
    }

    &:focus,
    &:active,
    &:hover {
      background: none;
    }

    &:focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
      border-radius: ${borderRadius[2]};
    }
  `,
  title: ({ isEdit }: { isEdit: boolean }) => css`
    ${typography.body()};
    color: ${colorTokens.text.hints};
    width: 100%;
    ${!isEdit &&
    css`
      ${styleUtils.text.ellipsis(1)};
    `}
  `,
  description: ({ isEdit }: { isEdit: boolean }) => css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
    padding-inline: ${spacing[8]};
    margin-left: ${spacing[24]};
    padding-bottom: ${spacing[12]};

    ${!isEdit &&
    css`
      ${styleUtils.text.ellipsis(2)};
    `}

    ${isEdit &&
    css`
      padding-right: 0;
    `}
  `,
  footer: css`
    width: 100%;
    text-align: right;
    ${styleUtils.display.flex()};
    gap: ${spacing[8]};
    justify-content: end;
  `,
  actions: css`
    ${styleUtils.display.flex()};
    align-items: start;
    gap: ${spacing[8]};
    justify-content: end;
  `,
};
