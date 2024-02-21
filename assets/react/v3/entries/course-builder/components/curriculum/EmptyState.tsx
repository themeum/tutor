import Button from "@Atoms/Button";
import { borderRadius, colorPalate, spacing } from "@Config/styles";
import { typography } from "@Config/typography";
import { css } from "@emotion/react";

interface EmptyStateProps {
  emptyStateImage: string;
  title: string;
  description?: string;
  actions?: React.ReactNode;
}

const EmptyState = ({ emptyStateImage, title, description, actions }: EmptyStateProps) => {
  return (
    <div css={styles.bannerWrapper}>
      <div css={styles.imageWrapper({ emptyStateImage })} />
      <div css={styles.messageWrapper}>
        {/* 
          Color is not accurate, it should be #212327
        */}
        <h5 css={styles.title}>{title}</h5>
        {/* 
          Color is not accurate
        */}
        {!!description && <p css={styles.description}>{description}</p>}
        {!!actions && <div css={styles.actionWrapper}>{actions}</div>}
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
    text-align: center;
    padding-block: ${spacing[20]};
  `,
  imageWrapper: ({ emptyStateImage }: { emptyStateImage: string }) => css`
    background-image: url(${emptyStateImage});
    max-width: 412px;
    height: 140px;
    width: 100%;
    border-radius: ${borderRadius[10]};
    overflow: hidden;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
  `,
  messageWrapper: css`
    display: flex;
    flex-direction: column;
    max-width: 566px;
    gap: ${spacing[12]};
  `,
  title: css`
    ${typography.heading5()};
  `,
  description: css`
    ${typography.body()};
    color: ${colorPalate.text.neutral};
  `,
  actionWrapper: css`
    margin-top: ${spacing[32]};
    display: flex;
    justify-content: center;
    align-items: center;
    gap: ${spacing[12]};
  `,
};
