import { useFormContext } from 'react-hook-form';
import { PaymentSettings } from '../services/payment';
import { typography } from '@/v3/shared/config/typography';
import { css } from '@emotion/react';
import { colorTokens, spacing } from '@/v3/shared/config/styles';
import For from '@/v3/shared/controls/For';
import PaymentItem from './PaymentItem';
import Button from '@/v3/shared/atoms/Button';
import { __ } from '@wordpress/i18n';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';

const PaymentMethods = () => {
  const form = useFormContext<PaymentSettings>();
  const paymentMethods = form.watch('payment_methods') ?? [];

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>Payment Methods</div>
      <div css={styles.methodWrapper}>
        <For each={paymentMethods}>{(option, index) => <PaymentItem data={option} paymentIndex={index} />}</For>
      </div>
      <div css={styles.buttonWrapper}>
        <Button variant="primary" isOutlined size="large" icon={<SVGIcon name="plus" width={24} height={24} />}>
          {__('Add payment method', 'tutor')}
        </Button>
        <Button variant="primary" isOutlined size="large" icon={<SVGIcon name="plus" width={24} height={24} />}>
          {__('Add manual payment', 'tutor')}
        </Button>
      </div>
    </div>
  );
};

export default PaymentMethods;

const styles = {
  wrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  title: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.subdued};
  `,
  methodWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  buttonWrapper: css`
    display: flex;
    gap: ${spacing[16]};
  `,
};
