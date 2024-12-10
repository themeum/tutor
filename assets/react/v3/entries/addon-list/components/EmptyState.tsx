import { spacing } from '@/v3/shared/config/styles';
import { typography } from '@/v3/shared/config/typography';
import { css } from '@emotion/react';
import emptyStateBanner from '@Images/addons-empty-state.webp';
import { __ } from '@wordpress/i18n';

function EmptyState() {
  return (
    <div css={styles.wrapper}>
      <img src={emptyStateBanner} alt={__('Empty state banner', 'tutor')} />
      <p>{__('No matching results found.', 'tutor')}</p>
    </div>
  );
}

export default EmptyState;

const styles = {
  wrapper: css`
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: ${spacing[20]};
    margin-top: ${spacing[96]};

    img {
      max-width: 160px;
    }

    p {
      ${typography.body('medium')};
      margin-bottom: 0;
    }
  `,
};
