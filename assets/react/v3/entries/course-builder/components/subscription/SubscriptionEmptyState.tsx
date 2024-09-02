import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import emptyStateImage2x from '@Images/empty-state-illustration-2x.webp';
import emptyStateImage from '@Images/empty-state-illustration.webp';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

export const SubscriptionEmptyState = ({ onCreateSubscription }: { onCreateSubscription: () => void }) => {
  return (
    <div css={styles.wrapper}>
      <div css={styles.banner}>
        <img
          src={emptyStateImage}
          srcSet={`${emptyStateImage} ${emptyStateImage2x} 2x`}
          alt={__('Empty state banner', 'tutor')}
        />
      </div>

      <div css={styles.content}>
        <h5>{__('Create subscription to boost your sell', 'tutor')}</h5>
        <p>
          {__(
            'When an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries',
            'tutor',
          )}
        </p>
      </div>

      <div css={styles.action}>
        <Button
          variant="secondary"
          icon={<SVGIcon name="plusSquareBrand" width={24} height={24} />}
          onClick={onCreateSubscription}
        >
          {__('Add Subscription', 'tutor')}
        </Button>
      </div>
    </div>
  );
};

const styles = {
  wrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[32]};
    justify-content: center;
    max-width: 640px;
    width: 100%;
    margin: ${spacing[40]} auto;
  `,
  content: css`
    display: grid;
    gap: ${spacing[12]};
    text-align: center;
    max-width: 566px;
    width: 100%;
    margin: 0 auto;

    h5 {
      ${typography.heading5('medium')};
      color: ${colorTokens.text.primary};
    }

    p {
      ${typography.caption()};
      color: ${colorTokens.text.hints};
    }
  `,
  action: css`
    display: flex;
    justify-content: center;
  `,
  banner: css`
    width: 100%;
    height: 232px;
    background-color: ${colorTokens.background.status.drip};
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: ${borderRadius[8]};
		position: relative;
		overflow: hidden;

    img {
      position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			object-fit: cover;
    }
  `,
};
