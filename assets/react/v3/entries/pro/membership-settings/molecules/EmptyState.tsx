import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import SVGIcon from '@Atoms/SVGIcon';
import Button from '@Atoms/Button';
import { borderRadius, colorTokens, lineHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import emptyStateImage from '@Images/membership-empty-state.webp';

interface EmptyStateProps {
  onActionClick: () => void;
}

const EmptyState = ({ onActionClick }: EmptyStateProps) => {
  return (
    <div css={styles.wrapper}>
      <img src={emptyStateImage} alt={__('No membership banner', 'tutor')} />
      <h5 css={styles.title}>{__('No Membership Added Yet', 'tutor')}</h5>
      <div css={styles.content}>{__('Set up memberships or package plans to sell on your site.', 'tutor')}</div>
      <Button
        variant="primary"
        isOutlined
        size="large"
        onClick={onActionClick}
        icon={<SVGIcon name="plus" width={24} height={24} />}
      >
        {__('New Membership Level', 'tutor')}
      </Button>
    </div>
  );
};

export default EmptyState;

const styles = {
  wrapper: css`
    display: flex;
    align-items: center;
    flex-direction: column;
    gap: ${spacing[8]};

    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[6]};
    padding: ${spacing[32]} ${spacing[24]};

    img {
      max-width: 234px;
    }
  `,
  title: css`
    ${typography.heading6('medium')};
    line-height: ${lineHeight[28]};
  `,
  content: css`
    ${typography.body()};
    line-height: ${lineHeight[22]};
    color: ${colorTokens.text.title};
    margin-bottom: ${spacing[12]};
    max-width: 306px;
    text-align: center;
  `,
};
