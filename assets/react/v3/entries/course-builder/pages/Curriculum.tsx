import React, { useEffect } from 'react';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';
import ReferenceModal from '@Components/modals/ReferenceModal';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import { __ } from '@wordpress/i18n';
import { css } from '@emotion/react';
import { spacing } from '@Config/styles';
import Show from '@Controls/Show';
import Topic from '@CourseBuilderComponents/curriculum/Topic';
import { CurriculumTopic, useCourseCurriculumQuery } from '@CourseBuilderServices/curriculum';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import For from '@Controls/For';
import { styleUtils } from '@Utils/style-utils';
import { useState } from 'react';
import EmptyState from '@Molecules/EmptyState';
import emptyStateImage from '@CourseBuilderPublic/images/empty-state-illustration.webp';
import emptyStateImage2x from '@CourseBuilderPublic/images/empty-state-illustration-2x.webp';

const Curriculum = () => {
  const courseId = getCourseId();
  const courseCurriculumQuery = useCourseCurriculumQuery(courseId);
  const [allCollapsed, setAllCollapsed] = useState(false);
  const [content, setContent] = useState<CurriculumTopic[]>([]);

  useEffect(() => {
    if (!courseCurriculumQuery.data) {
      return;
    }

    setContent(courseCurriculumQuery.data);
  }, [courseCurriculumQuery.data]);

  if (courseCurriculumQuery.isLoading) {
    return <LoadingOverlay />;
  }

  if (!courseCurriculumQuery.data) {
    return null;
  }

  return (
    <div css={styles.container}>
      <div css={styles.wrapper}>
        <CanvasHead
          title={__('Curriculum', 'tutor')}
          rightButton={
            <Button variant="text" onClick={() => setAllCollapsed(previous => !previous)}>
              {allCollapsed ? __('Expand All', 'tutor') : __('Collapse All', 'tutor')}
            </Button>
          }
        />

        <div>
          <Show
            when={content}
            fallback={
              <EmptyState
                emptyStateImage={emptyStateImage}
                emptyStateImage2x={emptyStateImage2x}
                imageAltText="Empty State Image"
                title="Create the course journey from here!"
                description="when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries"
                actions={
                  <Button variant="secondary" icon={<SVGIcon name="plusSquareBrand" width={24} height={25} />}>
                    {__('Add Topic', 'tutor')}
                  </Button>
                }
              />
            }
          >
            <div css={styles.topicWrapper}>
              <For each={content}>
                {(topic, index) => {
                  return (
                    <Topic
                      key={index}
                      topic={topic}
                      allCollapsed={allCollapsed}
                      onDelete={() => setContent(previous => previous.filter((_, idx) => idx !== index))}
                    />
                  );
                }}
              </For>
            </div>
          </Show>
        </div>
      </div>
    </div>
  );
};

export default Curriculum;

const styles = {
  container: css`
    padding: ${spacing[32]} ${spacing[64]};
  `,
  wrapper: css`
    max-width: 1076px;
    width: 100%;
    ${styleUtils.display.flex('column')};
    gap: ${spacing[32]};
  `,

  topicWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
  `,
};
