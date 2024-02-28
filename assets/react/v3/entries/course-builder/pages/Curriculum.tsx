import React from 'react';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';
import ReferenceModal from '@Components/modals/ReferenceModal';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import { __ } from '@wordpress/i18n';
import { css } from '@emotion/react';
import { spacing } from '@Config/styles';

const Curriculum = () => {
  const { showModal, closeModal } = useModal();
  return (
    <div css={styles.wrapper}>
      <CanvasHead
        title={__('Curriculum', 'tutor')}
        rightButton={<Button variant='text'>{__('Expand All', 'tutor')}</Button>}
      />

      <Button
        onClick={() =>
          showModal({
            component: ReferenceModal,
            props: {
              icon: <SVGIcon name='note' height={24} width={24} />,
              title: __('Title', 'tutor'),
              subtitle: __('Subtitle', 'tutor'),
              actions: (
                <>
                  <Button variant='secondary' onClick={() => closeModal({ action: 'CLOSE' })}>
                    {__('Cancel', 'tutor')}
                  </Button>
                  <Button variant='primary' onClick={() => closeModal({ action: 'CONFIRM' })}>
                    {__('Confirm', 'tutor')}
                  </Button>
                </>
              ),
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
