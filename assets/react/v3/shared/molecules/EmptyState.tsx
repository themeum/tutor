import Button from '@Atoms/Button';
import { borderRadius, colorPalate, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';

interface EmptyStateProps {
  emptyStateImage: string;
  imageAltText: string;
  title: string;
  description?: string;
  actions: React.ReactNode;
}

const EmptyState = ({ emptyStateImage, imageAltText, title, description, actions }: EmptyStateProps) => {
  return (
    <div css={styles.bannerWrapper}>
      <img src={emptyStateImage} alt={imageAltText} />
      <div css={styles.messageWrapper}>
        <h5 css={styles.title}>{title}</h5>
        {!!description && <p css={styles.description}>{description}</p>}
        <div css={styles.actionWrapper}>{actions}</div>
      </div>
    </div>
  );
};

export default EmptyState;

const styles = {
  bannerWrapper: css`
    display: grid;
    place-items: center;
    justify-content: center;
    gap: ${spacing[36]};
    padding-block: ${spacing[20]};

    & img {
      max-width: 412px;
      max-height: 140px;
      width: 100%;
      border-radius: ${borderRadius[10]};
      overflow: hidden;
      object-position: center;
      object-fit: cover;
    }
  `,
  messageWrapper: css`
    display: flex;
    flex-direction: column;
    max-width: 566px;
    gap: ${spacing[12]};
    text-align: center;
  `,
  title: css`
    ${typography.heading5()};
    color: ${colorTokens.text.primary};
  `,
  description: css`
    ${typography.body()};
    color: ${colorTokens.text.hints};
  `,
  actionWrapper: css`
    margin-top: ${spacing[32]};
    display: flex;
    justify-content: center;
    align-items: center;
    gap: ${spacing[12]};
  `,
};
