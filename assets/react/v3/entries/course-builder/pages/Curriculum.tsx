import Button from '@Atoms/Button';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import EmptyState from '@Molecules/EmptyState';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { spacing } from '@Config/styles';
import SVGIcon from '@Atoms/SVGIcon';
import emptyStateImage from '@CourseBuilderPublic/images/empty-state-illustration.webp';
import emptyStateImage2x from '@CourseBuilderPublic/images/empty-state-illustration-2x.webp';
import Show from '@Controls/Show';
import Topic from '@CourseBuilderComponents/curriculum/Topic';
import { useCourseCurriculumQuery } from '@CourseBuilderServices/curriculum';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import For from '@Controls/For';
import { styleUtils } from '@Utils/style-utils';

const Curriculum = () => {
  const courseId = getCourseId();
  const courseCurriculumQuery = useCourseCurriculumQuery(courseId);
  if (courseCurriculumQuery.isLoading) {
    return <LoadingOverlay />;
  }

  if (!courseCurriculumQuery.data) {
    return null;
  }

  const content = courseCurriculumQuery.data;
  console.log({ content });

  return (
    <div css={styles.container}>
      <div css={styles.wrapper}>
        <CanvasHead title={__('Curriculum', 'tutor')} rightButton={<Button variant="text">Expand All</Button>} />

        <div css={styles.content}>
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
                    Add Topic
                  </Button>
                }
              />
            }
          >
            <div css={styles.topicWrapper}>
              <For each={content}>
                {(topic, index) => {
                  return <Topic key={index} topic={topic} onToggle={() => {}} isCollapsed={false} />;
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
  `,
  content: css`
    padding: ${spacing[20]} 0;
  `,
  topicWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
  `,
};
