import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { useFormContext } from 'react-hook-form';

import ThreeDots from '@TutorShared/molecules/ThreeDots';

import ConfirmationModal from '@TutorShared/components/modals/ConfirmationModal';
import { useModal } from '@TutorShared/components/modals/Modal';
import { spacing, zIndex } from '@TutorShared/config/styles';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { POPOVER_PLACEMENTS } from '@TutorShared/hooks/usePortalPopover';

import type { TaxSettings } from '../services/tax';
import type { ColumnDataType } from './TaxRates';

interface MoreOptionsProps {
  data: ColumnDataType;
}

export const MoreOptions = ({ data }: MoreOptionsProps) => {
  const form = useFormContext<TaxSettings>();
  const [isOpen, setIsOpen] = useState(false);
  const { showModal } = useModal();

  return (
    <div css={styles.tableMoreOptions}>
      <ThreeDots
        placement={POPOVER_PLACEMENTS.BOTTOM}
        animationType={AnimationType.slideDown}
        isOpen={isOpen}
        onClick={() => {
          setIsOpen(true);
        }}
        closePopover={() => setIsOpen(false)}
        size="small"
        arrow={true}
      >
        <ThreeDots.Option
          text={__('Edit', 'tutor')}
          onClick={() => {
            if (typeof data.locationId === 'string') {
              form.setValue('active_country', data.locationId);
            }
          }}
          onClosePopover={() => setIsOpen(false)}
        />
        <ThreeDots.Option
          text={__('Delete', 'tutor')}
          isTrash={true}
          onClick={async () => {
            const { action } = await showModal({
              component: ConfirmationModal,
              props: {
                title: __('Delete Tax Rate', 'tutor'),
              },
              depthIndex: zIndex.highest,
            });
            if (action === 'CONFIRM') {
              const activeCountry = form.getValues('active_country');
              const rates = form.getValues('rates').filter((rate) => rate.country !== data.locationId);
              form.setValue('rates', rates, { shouldDirty: true });
              if (String(activeCountry) === String(data.locationId)) {
                form.setValue('active_country', null);
              }
            }
          }}
          onClosePopover={() => setIsOpen(false)}
        />
      </ThreeDots>
    </div>
  );
};

const styles = {
  tableMoreOptions: css`
    display: flex;
    align-items: center;
    gap: ${spacing[28]};
  `,
};
