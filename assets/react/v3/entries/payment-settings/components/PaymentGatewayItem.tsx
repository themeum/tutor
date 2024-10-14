import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import Show from '@Controls/Show';
import { borderRadius, colorTokens, lineHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { PaymentGateway } from '../services/payment';
import { __ } from '@wordpress/i18n';
import Badge from '../atoms/Badge';

interface PaymentGatewayItemProps {
  data: PaymentGateway;
}

const PaymentGatewayItem = ({ data }: PaymentGatewayItemProps) => {
  const handleInstallClick = () => {
    // @TODO
    console.log('Install clicked');
  };

  return (
    <div css={styles.wrapper}>
      <div css={styles.title}>
        <img src={data.icon} alt={data.label} />
        <span>{data.label}</span>
        <Show when={data.support_recurring}>
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
          <Button variant="secondary" size="small" onClick={handleInstallClick}>
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
