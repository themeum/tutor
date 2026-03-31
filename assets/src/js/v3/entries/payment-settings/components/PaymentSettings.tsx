import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { FormProvider } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import ProBadge from '@TutorShared/atoms/ProBadge';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { useModal } from '@TutorShared/components/modals/Modal';

import ConfirmationModal from '@TutorShared/components/modals/ConfirmationModal';
import { tutorConfig } from '@TutorShared/config/config';
import { Breakpoint, colorTokens, fontSize, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';

import { usePaymentContext } from '../contexts/payment-context';
import { type PaymentSettings, convertPaymentMethods, initialPaymentSettings } from '../services/payment';
import PaymentMethods from './PaymentMethods';
import ManualPaymentModal from './modals/ManualPaymentModal';
import PaymentGatewaysModal from './modals/PaymentGatewaysModal';

const TaxSettingsPage = () => {
  const { payment_gateways, payment_settings } = usePaymentContext();
  const { showModal } = useModal();

  const form = useFormWithGlobalError<PaymentSettings>({
    defaultValues: {
      ...initialPaymentSettings,
      payment_methods: convertPaymentMethods([], payment_gateways),
    },
    mode: 'all',
  });
  const { reset } = form;
  const formData = form.watch();

  const tutorOptionSaved = () => {
    reset(form.getValues());
  };

  useEffect(() => {
    window.addEventListener('tutor_option_saved', tutorOptionSaved);
    return () => window.removeEventListener('tutor_option_saved', tutorOptionSaved);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (form.formState.isDirty) {
      document.getElementById('save_tutor_option')?.removeAttribute('disabled');
    }
  }, [form.formState.isDirty]);

  useEffect(() => {
    if (payment_settings) {
      const methods = convertPaymentMethods(payment_settings.payment_methods ?? [], payment_gateways);

      reset({
        ...payment_settings,
        payment_methods: methods,
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [reset, payment_settings]);

  return (
    <div css={styles.wrapper} data-isdirty={form.formState.isDirty ? 'true' : undefined}>
      <h6 css={styles.title}>
        {__('Payment Methods', 'tutor')}
        <Button
          variant="text"
          buttonCss={styles.resetButton}
          icon={<SVGIcon name="rotate" width={22} height={22} />}
          onClick={async () => {
            const { action } = await showModal({
              component: ConfirmationModal,
              props: {
                title: __('Reset to Default Settings?', 'tutor'),
                description:
                  // prettier-ignore
                  __( 'WARNING! This will overwrite all customized settings of this section and reset them to default. Proceed with caution.', 'tutor'),
                confirmButtonText: __('Reset', 'tutor'),
              },
              depthIndex: zIndex.highest,
            });

            if (action === 'CONFIRM') {
              reset({
                ...initialPaymentSettings,
                payment_methods: convertPaymentMethods([], payment_gateways),
              });
              document.getElementById('save_tutor_option')?.removeAttribute('disabled');
            }
          }}
        >
          {__('Reset to Default', 'tutor')}
        </Button>
      </h6>
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
                  {__('Add New Gateway', 'tutor')}
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
                  {__('Add New Gateway', 'tutor')}
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
              {__('Add Manual Payment', 'tutor')}
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
    display: flex;
    justify-content: space-between;
    align-items: center;
  `,
  resetButton: css`
    font-size: ${fontSize[16]};
    padding: 0;
    color: #757c8e;

    &:hover {
      color: ${colorTokens.action.primary};
    }
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

    ${Breakpoint.smallMobile} {
      flex-direction: column;
    }
  `,
  noPaymentMethod: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
};
