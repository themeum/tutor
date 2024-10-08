import { POPOVER_ANIMATION_DURATION_WITH_THRESHOLD } from '@/v3/shared/config/constants';
import { useModal } from '@Components/modals/Modal';
import { spacing, zIndex } from '@Config/styles';
import ThreeDots from '@Molecules/ThreeDots';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { useFormContext } from 'react-hook-form';
import type { TaxSettings } from '../services/tax';
import type { ColumnDataType } from './TaxRates';
import StaticConfirmationModal from './modals/StaticConfirmationModal';

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
        arrowPosition="left"
        isOpen={isOpen}
        onClick={() => {
          setIsOpen(true);
        }}
        closePopover={() => setIsOpen(false)}
      >
        <ThreeDots.Option
          text={__('Edit', 'tutor')}
          onClick={() => {
            if (typeof data.locationId === 'string') {
              setTimeout(() => {
                return form.setValue('active_country', String(data.locationId));
              }, POPOVER_ANIMATION_DURATION_WITH_THRESHOLD + 5);
            }
          }}
          onClosePopover={() => setIsOpen(false)}
        />
        <ThreeDots.Option
          text={__('Delete', 'tutor')}
          isTrash={true}
          onClick={async () => {
            const { action } = await showModal({
              component: StaticConfirmationModal,
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
