import Show from '@Controls/Show';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import { colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { FormProvider } from 'react-hook-form';
import { type PaymentSettings, usePaymentSettingsQuery } from '../services/payment';
import PaymentMethods from './PaymentMethods';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import ManualPaymentModal from './modals/ManualPaymentModal';
import { useModal } from '@Components/modals/Modal';
import PaymentGatewaysModal from './modals/PaymentGatewaysModal';

const TaxSettingsPage = () => {
  const { showModal } = useModal();
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
          <div css={styles.paymentButtonWrapper}>
            <PaymentMethods />
            <div css={styles.buttonWrapper}>
              <Button
                variant="primary"
                isOutlined
                size="large"
                icon={<SVGIcon name="plus" width={24} height={24} />}
                onClick={() => {
                  showModal({
                    component: PaymentGatewaysModal,
                    props: {
                      title: __('Payment gateways', 'tutor'),
                      form: form,
                    },
                    depthIndex: zIndex.highest,
                  });
                }}
              >
                {__('Connect more gateway', 'tutor')}
              </Button>
              <Button
                variant="primary"
                isOutlined
                size="large"
                icon={<SVGIcon name="plus" width={24} height={24} />}
                onClick={() => {
                  showModal({
                    component: ManualPaymentModal,
                    props: {
                      title: __('Set up manual payment method', 'tutor'),
                      paymentForm: form,
                    },
                    depthIndex: zIndex.highest,
                  });
                }}
              >
                {__('Add manual payment', 'tutor')}
              </Button>
            </div>
          </div>
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
  emptyStateWrapper: css`
    margin-top: ${spacing[24]};
    margin-bottom: ${spacing[24]};

    img {
      margin-bottom: ${spacing[24]};
    }
  `,
  paymentButtonWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  buttonWrapper: css`
    display: flex;
    gap: ${spacing[16]};
  `,
};
