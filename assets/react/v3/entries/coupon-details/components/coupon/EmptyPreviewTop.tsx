import { typography } from '@/v3/shared/config/typography';
import SVGIcon from '@Atoms/SVGIcon';
import { colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

const EmptyPreviewTop = () => {
  return (
    <div css={styles.wrapper}>
      <SVGIcon name="receiptPercent" width={32} height={32} />
      <div css={styles.description}>{__('Coupon preview will appear here', 'tutor')}</div>
    </div>
  );
};
export default EmptyPreviewTop;

const styles = {
  wrapper: css`
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: ${spacing[12]};
    padding: ${spacing[32]} ${spacing[20]};

    svg {
      color: ${colorTokens.icon.hints};
    }
  `,
  description: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
};
