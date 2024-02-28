import React from 'react';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import { __ } from '@wordpress/i18n';
import { css } from '@emotion/react';
import { spacing } from '@Config/styles';
import AddAssignmentModal from '@Components/modals/AddAssignmentModal';

const Curriculum = () => {
  const { showModal, closeModal } = useModal();
  return (
    <div css={styles.wrapper}>
      <CanvasHead
        title={__('Curriculum', 'tutor')}
        rightButton={<Button variant="text">{__('Expand All', 'tutor')}</Button>}
      />

      <Button
        onClick={() =>
          showModal({
            component: AddAssignmentModal,
            props: {
              icon: <SVGIcon name="report" height={24} width={24} />,
              title: __('Assignment', 'tutor'),
              subtitle: __('Topic: Learn to use ChatGPT effectively', 'tutor'),
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
