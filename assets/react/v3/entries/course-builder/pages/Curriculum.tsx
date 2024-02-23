import React from 'react';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';
import ReferenceModal from '@Components/modals/ReferenceModal';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import { __ } from '@wordpress/i18n';
import { css } from '@emotion/react';
import { spacing } from '@Config/styles';
import AddLessonModal from '@Components/modals/AddLessonModal';

const Curriculum = () => {
  const { showModal } = useModal();
  return (
    <div css={styles.wrapper}>
      <CanvasHead title={__('Curriculum', 'tutor')} rightButton={<Button variant='text'>Expand All</Button>} />

      <Button
        onClick={() =>
          showModal({
            component: AddLessonModal,
            props: {
              icon: <SVGIcon name='note' height={24} width={24} />,
              title: 'Lesson',
              subtitle: 'Topic: Learn to use ChatGPT effectively',
            },
          })
        }
      >
        Show Modal
      </Button>
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
