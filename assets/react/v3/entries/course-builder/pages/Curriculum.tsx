import Button from '@Atoms/Button';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import EmptyState from '@Molecules/EmptyState';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { spacing } from '@Config/styles';
import SVGIcon from '@Atoms/SVGIcon';
import emptyStateImage from '@CourseBuilderPublic/images/empty-state-illustration.webp';
import emptyStateImage2x from '@CourseBuilderPublic/images/empty-state-illustration-2x.webp';

const Curriculum = () => {
  return (
    <div css={styles.wrapper}>
      <CanvasHead title={__('Curriculum', 'tutor')} rightButton={<Button variant='text'>Expand All</Button>} />

      <div css={styles.topicsWrapper}>
        <EmptyState
          emptyStateImage={emptyStateImage}
          emptyStateImage2x={emptyStateImage2x}
          imageAltText='Empty State Image'
          title='Create the course journey from here!'
          description='when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries'
          actions={
            <Button variant='secondary' icon={<SVGIcon name='plusSquare' />}>
              Add Topic
            </Button>
          }
        />
      </div>
    </div>
  );
};

export default Curriculum;

const styles = {
  wrapper: css`
    padding: ${spacing[24]} ${spacing[64]};
  `,
  topicsWrapper: css`
    margin-top: ${spacing[32]};
  `,
};
