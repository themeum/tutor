import { type Discount, useOrderDiscountMutation } from '@OrderDetails/services/order';
import Button from '@TutorShared/atoms/Button';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';
import { tutorConfig } from '@TutorShared/config/config';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { formatPrice } from '@TutorShared/utils/currency';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { requiredRule } from '@TutorShared/utils/validation';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo } from 'react';
import { Controller } from 'react-hook-form';

interface DiscountModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  discount: Discount;
  total_price: number;
  order_id: number;
}

type FormField = Discount;

const discountTypeOptions = [
  {
    label: __('Amount', 'tutor'),
    value: 'flat',
  },
  {
    label: __('Percentage', 'tutor'),
    value: 'percentage',
  },
];

const calculatePercentage = (total: number, percent: number) => {
  return total * (percent / 100);
};

function DiscountModal({ title, closeModal, actions, discount, total_price, order_id }: DiscountModalProps) {
  const orderDiscountMutation = useOrderDiscountMutation();
  const form = useFormWithGlobalError<FormField>({
    defaultValues: discount,
    mode: 'onChange',
  });
  const type = form.watch('type');
  const value = form.watch('amount');
  const currencySymbol = tutorConfig.tutor_currency.symbol ?? '$';
  const content = type === 'flat' ? currencySymbol : '%';
  const priceAfterDiscount = useMemo(() => {
    const discountedPrice =
      type === 'percentage' ? total_price - calculatePercentage(total_price, value) : total_price - value;
    return Math.max(0, discountedPrice).toFixed(2);
  }, [type, value, total_price]);

  useEffect(() => {
    form.setFocus('type');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions} maxWidth={480}>
      <form
        onSubmit={form.handleSubmit((values) => {
          orderDiscountMutation.mutate({
            order_id,
            discount_type: values.type,
            discount_amount: values.amount,
            discount_reason: values.reason,
          });
          closeModal();
        })}
      >
        <div css={styles.formContent}>
          <div css={styles.inlineFields}>
            <Controller
              control={form.control}
              name="type"
              rules={{ ...requiredRule() }}
              render={(props) => (
                <FormSelectInput
                  {...props}
                  label={__('Discount Type', 'tutor')}
                  options={discountTypeOptions}
                  placeholder={__('Select discount type', 'tutor')}
                  onChange={() => {
                    form.setFocus('amount');
                  }}
                />
              )}
            />
            <Controller
              control={form.control}
              name="amount"
              rules={{
                ...requiredRule(),
                validate: (value) => {
                  if (type === 'percentage' && value > 100) {
                    return __('Should not be more than 100%.', 'tutor');
                  }
                  if (type === 'flat' && value > total_price) {
                    return __('Discount should not exceed the total price.', 'tutor');
                  }
                  return undefined;
                },
              }}
              render={(props) => (
                <FormInputWithContent
                  {...props}
                  label={__('Discount Value', 'tutor')}
                  content={content}
                  contentCss={type === 'flat' ? styleUtils.inputCurrencyStyle : undefined}
                  type="number"
                  selectOnFocus
                />
              )}
            />
          </div>

          <p css={styles.priceMessage}>
            {__('Price after the discount: ', 'tutor')} <strong>{formatPrice(Number(priceAfterDiscount))}</strong>
          </p>

          <div css={styles.reason}>
            <Controller
              control={form.control}
              name="reason"
              rules={{ ...requiredRule() }}
              render={(props) => (
                <FormInput
                  {...props}
                  label={__('Discount Reason', 'tutor')}
                  placeholder={__('Enter the reason of this discount', 'tutor')}
                />
              )}
            />
          </div>
        </div>
        <div css={styles.footer}>
          <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
            {__('Cancel', 'tutor')}
          </Button>
          <Button type="submit" size="small" variant="primary" loading={orderDiscountMutation.isPending}>
            {__('Apply', 'tutor')}
          </Button>
        </div>
      </form>
    </BasicModalWrapper>
  );
}

export default DiscountModal;

const styles = {
  inlineFields: css`
    display: flex;
    gap: ${spacing[16]};
  `,
  priceMessage: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
    margin-top: ${spacing[12]};

    strong {
      color: ${colorTokens.text.title};
    }
  `,
  reason: css`
    margin-top: ${spacing[12]};
  `,
  formContent: css`
    padding: ${spacing[20]} ${spacing[16]};
  `,
  footer: css`
    box-shadow: 0px 1px 0px 0px #e4e5e7 inset;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: end;
    gap: ${spacing[16]};
    padding-inline: ${spacing[16]};
  `,
};
