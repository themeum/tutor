import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';

import emptyStateImage2x from '@SharedImages/subscriptions-empty-state-2x.webp';
import emptyStateImage from '@SharedImages/subscriptions-empty-state.webp';

export const SubscriptionEmptyState = ({ onCreateSubscription }: { onCreateSubscription: () => void }) => {
  return (
    <div css={styles.wrapper}>
      <div css={styles.banner}>
        <img
          src={emptyStateImage}
          srcSet={`${emptyStateImage} ${emptyStateImage2x} 2x`}
          alt={__('Empty state banner', __TUTOR_TEXT_DOMAIN__)}
        />
      </div>

      <div css={styles.content}>
        <h5>{__('Boost Revenue with Subscriptions', __TUTOR_TEXT_DOMAIN__)}</h5>
        <p>
          {
            // prettier-ignore
            __('Offer flexible subscription plans to maximize your earnings and provide students with affordable access to your courses.', __TUTOR_TEXT_DOMAIN__)
          }
        </p>
      </div>

      <div css={styles.action}>
        <Button
          variant="secondary"
          icon={<SVGIcon name="plusSquareBrand" width={24} height={24} />}
          onClick={onCreateSubscription}
        >
          {__('Add Subscription', __TUTOR_TEXT_DOMAIN__)}
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
    padding-block: ${spacing[40]};
    margin-inline: auto;
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
