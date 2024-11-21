import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import Show from '@Controls/Show';
import { borderRadius, colorTokens, lineHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { PaymentGateway, PaymentSettings, useInstallPaymentMutation } from '../services/payment';
import { __ } from '@wordpress/i18n';
import Badge from '../atoms/Badge';
import { FormWithGlobalErrorType } from '@Hooks/useFormWithGlobalError';

interface PaymentGatewayItemProps {
  data: PaymentGateway;
  onInstallSuccess: () => void;
  form: FormWithGlobalErrorType<PaymentSettings>;
}

const PaymentGatewayItem = ({ data, onInstallSuccess, form }: PaymentGatewayItemProps) => {
  const installPaymentMutation = useInstallPaymentMutation();

  const handleInstallClick = async () => {
    const response = await installPaymentMutation.mutateAsync({
      slug: data.name,
    });

    if (response.status_code === 200) {
      onInstallSuccess();

      // Append fields to settings
      form.setValue(
        'payment_methods',
        [
          ...form.getValues('payment_methods'),
          { ...data, fields: data.fields.map(({ name, value }) => ({ name, value })) },
        ],
        {
          shouldDirty: true,
        }
      );
    }
  };

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>
        <img src={data.icon} alt={data.label} />
        <span>{data.label}</span>
        <Show when={data.support_subscription}>
          <Badge variant="success">{__('Supports Subscriptions', 'tutor')}</Badge>
        </Show>
      </div>
      <div>
        {data.is_installed ? (
          <span css={styles.installed}>
            <SVGIcon name="tickMarkGreen" />
            {__('Installed', 'tutor')}
          </span>
        ) : (
          <Button
            variant="secondary"
            size="small"
            disabled={!data.is_installable}
            onClick={handleInstallClick}
            loading={installPaymentMutation.isPending}
          >
            {__('Install', 'tutor')}
          </Button>
        )}
      </div>
    </div>
  );
};

const styles = {
  wrapper: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: ${spacing[12]} ${spacing[16]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[6]};
    min-height: 56px;
  `,
  title: css`
    ${typography.body('medium')};
    line-height: ${lineHeight[20]};
    display: flex;
    align-items: center;
    gap: ${spacing[8]};

    img {
      height: 24px;
      width: 24px;
    }
  `,
  installed: css`
    ${typography.body()};
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
    color: ${colorTokens.text.success};
  `,
};

export default PaymentGatewayItem;
