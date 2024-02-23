import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { CurriculumTopic } from '@CourseBuilderServices/curriculum';

import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import React from 'react';
import TopicContent from './TopicContent';

interface TopicProps {
  topic: CurriculumTopic;
  isCollapsed: boolean;
  onToggle: () => void;
}

const Topic = ({ topic }: TopicProps) => {
  return (
    <div css={styles.wrapper}>
      <div css={styles.header}>
        <div css={styles.headerContent}>
          <div css={styles.grabberInput}>
            <SVGIcon name="dragVertical" width={24} height={24} />
            <div css={styles.title}>{topic.title}</div>
          </div>
          <div css={styles.actions}>
            <button type="button" css={styles.actionButton}>
              <SVGIcon name="edit" width={24} height={24} />
            </button>
            <button type="button" css={styles.actionButton}>
              <SVGIcon name="copyPaste" width={24} height={24} />
            </button>
            <button type="button" css={styles.actionButton}>
              <SVGIcon name="delete" width={24} height={24} />
            </button>
            <button type="button" css={styles.actionButton}>
              <SVGIcon name="chevronUp" />
            </button>
          </div>
        </div>

        <div css={styles.description}>{topic.summary}</div>
      </div>
      <div css={styles.content}>
        <div>
          <TopicContent type="lesson" content={{ title: 'Lesson: topic 1' }} />
          <TopicContent type="quiz" content={{ title: 'Quiz' }} />
          <TopicContent type="assignment" content={{ title: 'Assignments' }} />
        </div>
        <div css={styles.contentButtons}>
          <div css={[styleUtils.display.flex(), { gap: spacing[12] }]}>
            <Button variant="tertiary" icon={<SVGIcon name="plus" />}>
              Lesson
            </Button>
            <Button variant="tertiary" icon={<SVGIcon name="plus" />}>
              Quiz
            </Button>
            <Button variant="tertiary" icon={<SVGIcon name="plus" />}>
              Assignment
            </Button>
          </div>
          <div>
            <Button variant="tertiary" icon={<SVGIcon name="download" width={24} height={24} />}>
              Import Quiz
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Topic;

const styles = {
  wrapper: css`
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
  `,
  header: css`
    padding: ${spacing[12]} ${spacing[16]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  headerContent: css`
    display: grid;
    grid-template-columns: auto 144px;
  `,
  grabberInput: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};

    svg {
      color: ${colorTokens.color.black[40]};
    }
  `,
  actions: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
    justify-content: end;
  `,
  actionButton: css`
    ${styleUtils.resetButton};
    color: ${colorTokens.icon.default};
    display: flex;
  `,
  content: css`
    padding: ${spacing[16]};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
  `,
  contentButtons: css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
  `,
  title: css`
    ${typography.body()};
    color: ${colorTokens.text.hints};
  `,
  description: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
    margin-left: ${spacing[24]};
    padding: ${spacing[8]} ${spacing[10]};
  `,
};
