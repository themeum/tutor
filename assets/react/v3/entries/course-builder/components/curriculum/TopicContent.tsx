import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { ID, TopicContent as TopicContentType } from '@CourseBuilderServices/curriculum';
import { styleUtils } from '@Utils/style-utils';
import { IconCollection } from '@Utils/types';
import { AnimateLayoutChanges, defaultAnimateLayoutChanges, useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import React from 'react';

type ContentType = 'lesson' | 'quiz' | 'assignment' | 'zoom' | 'meet';
interface TopicContentProps {
  type: ContentType;
  content: { id: ID; title: string; questionCount?: number };
  isDragging?: boolean;
}

const icons = {
  lesson: {
    name: 'lesson',
    color: colorTokens.icon.default,
  },
  quiz: {
    name: 'quiz',
    color: colorTokens.design.warning,
  },
  assignment: {
    name: 'assignment',
    color: colorTokens.icon.processing,
  },
  zoom: {
    name: 'zoomColorize',
    color: '',
  },
  meet: {
    name: 'googleMeetColorize',
    color: '',
  },
} as const;

const animateLayoutChanges: AnimateLayoutChanges = args => defaultAnimateLayoutChanges({ ...args, wasDragging: true });

const TopicContent = ({ type, content, isDragging = false }: TopicContentProps) => {
  const icon = icons[type];
  const { attributes, listeners, setNodeRef, transform, transition } = useSortable({
    id: content.id,
    animateLayoutChanges,
  });
  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  };

  return (
    <div css={styles.wrapper({ isDragging })} ref={setNodeRef} style={style} {...attributes}>
      <div css={styles.iconAndTitle({ isDragging })} {...listeners}>
        <div data-content-icon>
          <SVGIcon
            name={icon.name as IconCollection}
            width={24}
            height={24}
            style={css`
              color: ${icon.color};
            `}
          />
        </div>
        <div data-bar-icon>
          <SVGIcon name="bars" width={24} height={24} />
        </div>
        <p css={styles.title}>
          <span dangerouslySetInnerHTML={{ __html: content.title }}></span>
          <Show when={type === 'quiz' && !!content.questionCount}>
            <span data-question-count>({content.questionCount} Questions)</span>
          </Show>
        </p>
      </div>

      <div css={styles.actions} data-actions>
        <button
          type="button"
          css={styles.actionButton}
          onClick={() => {
            alert('@TODO: will be implemented later');
          }}
        >
          <SVGIcon name="edit" width={24} height={24} />
        </button>
        <button
          type="button"
          css={styles.actionButton}
          onClick={() => {
            alert('@TODO: will be implemented later');
          }}
        >
          <SVGIcon name="copyPaste" width={24} height={24} />
        </button>
        <button
          type="button"
          css={styles.actionButton}
          onClick={() => {
            alert('@TODO: will be implemented later');
          }}
        >
          <SVGIcon name="delete" width={24} height={24} />
        </button>
        <button
          type="button"
          css={styles.actionButton}
          onClick={() => {
            alert('@TODO: will be implemented later');
          }}
        >
          <SVGIcon name="threeDotsVertical" width={24} height={24} />
        </button>
      </div>
    </div>
  );
};

export default TopicContent;

const styles = {
  wrapper: ({ isDragging = false }) => css`
    width: 100%;
    padding: ${spacing[10]} ${spacing[8]};
    cursor: pointer;
    border: 1px solid transparent;
    border-radius: ${borderRadius[6]};
    display: flex;
    justify-content: space-between;
    align-items: center;

    [data-content-icon],
    [data-bar-icon] {
      display: flex;
      height: 24px;
    }

    :hover {
      border-color: ${colorTokens.stroke.border};
      background-color: ${colorTokens.background.white};

      [data-content-icon] {
        display: none;
      }
      [data-bar-icon] {
        display: block;
      }

      [data-actions] {
        display: flex;
      }
    }

    ${isDragging &&
    css`
      box-shadow: ${shadow.drag};
    `}
  `,
  title: css`
    ${typography.caption()};
    color: ${colorTokens.text.title};
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
    [data-question-count] {
      color: ${colorTokens.text.hints};
    }
  `,
  iconAndTitle: ({ isDragging = false }) => css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};

    [data-bar-icon] {
      display: none;
    }
    cursor: ${isDragging ? 'grabbing' : 'grab'};
  `,
  actions: css`
    display: none;
    align-items: center;
    gap: ${spacing[8]};
    justify-content: end;
  `,
  actionButton: css`
    ${styleUtils.resetButton};
    color: ${colorTokens.icon.default};
    display: flex;
  `,
};
