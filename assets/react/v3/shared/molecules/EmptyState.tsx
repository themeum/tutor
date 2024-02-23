import { borderRadius, colorPalate, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { css } from '@emotion/react';

interface EmptyStateProps {
  emptyStateImage: string;
  emptyStateImage2x?: string;
  imageAltText: string;
  title: string;
  description?: string;
  actions?: React.ReactNode;
}

const EmptyState = ({
  emptyStateImage,
  emptyStateImage2x,
  imageAltText,
  title,
  description,
  actions,
}: EmptyStateProps) => {
  return (
    <div css={styles.bannerWrapper}>
      <img src={emptyStateImage} alt={imageAltText} srcSet={emptyStateImage2x ? `${emptyStateImage2x} 2x` : ''} />
      <div css={styles.messageWrapper}>
        <h5 css={styles.title}>{title}</h5>
        <Show when={description}>
          <p css={styles.description}>{description}</p>
        </Show>
        <Show when={actions}>
          <div css={styles.actionWrapper}>{actions}</div>
        </Show>
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
      width: 412px;
      height: 140px;
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
    width: 100%;
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
    margin-top: ${spacing[20]};
    display: flex;
    justify-content: center;
    align-items: center;
    gap: ${spacing[12]};
  `,
};
