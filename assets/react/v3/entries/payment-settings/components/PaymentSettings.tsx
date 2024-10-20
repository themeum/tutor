import Show from '@Controls/Show';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import { colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { FormProvider } from 'react-hook-form';
import {
  convertPaymentMethods,
  initialPaymentSettings,
  type PaymentSettings,
  usePaymentSettingsQuery,
} from '../services/payment';
import PaymentMethods from './PaymentMethods';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import ManualPaymentModal from './modals/ManualPaymentModal';
import { useModal } from '@Components/modals/Modal';
import PaymentGatewaysModal from './modals/PaymentGatewaysModal';
import ProBadge from '@Atoms/ProBadge';
import { tutorConfig } from '@Config/config';
import { usePaymentContext } from '../contexts/payment-context';

const TaxSettingsPage = () => {
  const { payment_gateways } = usePaymentContext();
  const { methods } = convertPaymentMethods(initialPaymentSettings.payment_methods, payment_gateways);
  const { showModal } = useModal();

  const form = useFormWithGlobalError<PaymentSettings>({
    defaultValues: { ...initialPaymentSettings, payment_methods: methods },
  });
  const { reset } = form;
  const formData = form.watch();

  const paymentSettingsQuery = usePaymentSettingsQuery();

  useEffect(() => {
    if (form.formState.isDirty) {
      document.getElementById('save_tutor_option')?.removeAttribute('disabled');
      form.reset(form.getValues(), { keepValues: true });
    }
  }, [form.formState.isDirty]);

  useEffect(() => {
    if (paymentSettingsQuery.data) {
      const { methods, isModified } = convertPaymentMethods(
        paymentSettingsQuery.data.payment_methods,
        payment_gateways
      );

      reset({
        ...paymentSettingsQuery.data,
        payment_methods: methods,
      });

      // Programmatically click the save button
      if (isModified) {
        setTimeout(() => {
          document.getElementById('save_tutor_option')?.removeAttribute('disabled');
          document.getElementById('save_tutor_option')?.click();
        }, 100);
      }
    }
  }, [reset, paymentSettingsQuery.data]);

  if (paymentSettingsQuery.isLoading) {
    return <LoadingSection />;
  }

  return (
    <div css={styles.wrapper} data-isdirty={form.formState.isDirty ? 'true' : undefined}>
      <h6 css={styles.title}>{__('Payment Methods', 'tutor')}</h6>
      <FormProvider {...form}>
        <div css={styles.paymentButtonWrapper}>
          <PaymentMethods />
          <div css={styles.buttonWrapper}>
            <Show
              when={!tutorConfig.tutor_pro_url}
              fallback={
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
              }
            >
              <ProBadge>
                <Button
                  variant="tertiary"
                  isOutlined
                  size="large"
                  icon={<SVGIcon name="plus" width={24} height={24} />}
                  disabled
                >
                  {__('Connect more gateway', 'tutor')}
                </Button>
              </ProBadge>
            </Show>

            <Button
              variant="text"
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
              {__('Add manual method', 'tutor')}
            </Button>
          </div>
        </div>
      </FormProvider>

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
  title: css`
    ${typography.heading5('medium')};
    line-height: 1.6;
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
  noPaymentMethod: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
};
