import { useModal } from '@Components/modals/Modal';
import { spacing } from '@Config/styles';
import { css } from '@emotion/react';
import ThreeDots from '@Molecules/ThreeDots';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { useFormContext } from 'react-hook-form';
import { TaxSettings } from '../services/tax';
import StaticConfirmationModal from './modals/StaticConfirmationModal';
import { ColumnDataType } from './TaxRates';

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
              form.setValue('activeCountry', data.locationId);
            }
          }}
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
            });
            if (action == 'CONFIRM') {
              const activeCountry = form.getValues('activeCountry');
              const rates = form.getValues('rates').filter((rate) => rate.country !== data.locationId);
              form.setValue('rates', rates, { shouldDirty: true });
              if (activeCountry == data.locationId) {
                form.setValue('activeCountry', null);
              }
            }
          }}
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
