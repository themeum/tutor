import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { TopicContent as TopicContentType } from '@CourseBuilderServices/curriculum';
import { styleUtils } from '@Utils/style-utils';
import { IconCollection } from '@Utils/types';
import { css } from '@emotion/react';
import React from 'react';

type ContentType = 'lesson' | 'quiz' | 'assignment' | 'zoom' | 'meet';
interface TopicContentProps {
  type: ContentType;
  content: TopicContentType;
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

const TopicContent = ({ type, content }: TopicContentProps) => {
  const icon = icons[type];

  return (
    <div css={styles.wrapper}>
      <div css={styles.iconAndTitle}>
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
          <span>{content.title}</span>
          <Show when={type === 'quiz'}>
            <span data-question-count>(21 Questions)</span>
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
  wrapper: css`
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
  iconAndTitle: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};

    [data-bar-icon] {
      display: none;
    }
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
