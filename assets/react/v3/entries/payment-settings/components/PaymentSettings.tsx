import Show from '@/v3/shared/controls/Show';
import { getCountryByCode } from '@/v3/shared/utils/countries';
import Button from '@Atoms/Button';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { FormProvider } from 'react-hook-form';
import Card from '../molecules/Card';
import EmptyState from '../molecules/EmptyState';
import { type PaymentSettings, usePaymentSettingsQuery } from '../services/payment';
import PaymentMethods from './PaymentMethods';

const TaxSettingsPage = () => {
  const form = useFormWithGlobalError<PaymentSettings>({
    defaultValues: {},
  });
  const { reset } = form;

  const paymentSettingsQuery = usePaymentSettingsQuery();

  const ratesValue = paymentSettingsQuery.data?.payment_methods?.length
    ? paymentSettingsQuery.data.payment_methods
    : form.getValues('payment_methods');

  const formData = form.watch();

  useEffect(() => {
    if (form.formState.isDirty) {
      document.getElementById('save_tutor_option')?.removeAttribute('disabled');
    }
  }, [form.formState.isDirty]);

  useEffect(() => {
    if (paymentSettingsQuery.data) {
      reset(paymentSettingsQuery.data);
    }
  }, [reset, paymentSettingsQuery.data]);

  if (paymentSettingsQuery.isLoading) {
    return <LoadingSection />;
  }

  return (
    <div css={styles.wrapper} data-isdirty={form.formState.isDirty ? 'true' : undefined}>
      <h6 css={typography.heading5('medium')}>{__('Payment Methods', 'tutor')}</h6>
      <Show when={ratesValue.length} fallback={<div>No payment selected</div>}>
        <FormProvider {...form}>
          <PaymentMethods />
        </FormProvider>
      </Show>

      <input type="hidden" name="tutor_option[payment_settings]" value={JSON.stringify(formData)} />
    </div>
  );
};

export default TaxSettingsPage;

const styles = {
  wrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,
  saveButtonContainer: css`
    display: flex;
    justify-content: flex-end;
  `,
  backButton: css`
    ${typography.heading5('medium')};
    text-decoration: none;
    color: ${colorTokens.text.title};
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: start;

    svg {
      color: ${colorTokens.text.title};
    }

    &:hover {
      text-decoration: none;
      color: ${colorTokens.text.title};
    }
  `,
  emptyStateWrapper: css`
    margin-top: ${spacing[24]};
    margin-bottom: ${spacing[24]};

    img {
      margin-bottom: ${spacing[24]};
    }
  `,
};
