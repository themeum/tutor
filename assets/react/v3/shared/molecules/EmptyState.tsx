import Button, { ButtonVariant } from '@Atoms/Button';
import { colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';

interface EmptyStateProps {
  emptyStateImage: string;
  imageAltText: string;
  title?: string;
  content?: string;
  buttonText?: string;
  action?: () => void;
  orientation?: 'horizontal' | 'vertical';
}

const EmptyState = ({
  emptyStateImage,
  imageAltText,
  title,
  content,
  buttonText,
  action,
  orientation = 'horizontal',
}: EmptyStateProps) => {
  return (
    <div css={styles.bannerWrapper({ orientation })}>
      <img src={emptyStateImage} alt={imageAltText} />
      <div css={styles.messageWrapper({ orientation })}>
        {!!title && <h5 css={styles.title}>{title}</h5>}
        {!!content && <p css={styles.content}>{content}</p>}
        {!!buttonText && (
          <div css={styles.buttonWrapper}>
            <Button variant={ButtonVariant.primary} onClick={action}>
              {buttonText}
            </Button>
          </div>
        )}
      </div>
    </div>
  );
};

export default EmptyState;

const styles = {
  bannerWrapper: ({ orientation }: { orientation: 'horizontal' | 'vertical' }) => css`
    display: grid;
    place-items: center;
    justify-content: center;
    ${orientation === 'horizontal' &&
    css`
      grid-template-columns: 278px auto;
      gap: ${spacing[56]};
      justify-content: start;
    `}

    & img {
      max-width: 272px;
      max-height: 272px;
      width: 100%;
    }
  `,
  messageWrapper: ({ orientation }: { orientation: 'horizontal' | 'vertical' }) => css`
    display: flex;
    flex-direction: column;
    max-width: 432px;
    gap: ${spacing[8]};

    ${orientation === 'horizontal' &&
    css`
      max-width: 364px;
    `}
    ${orientation === 'vertical' &&
    css`
      text-align: center;
    `}
  `,
  title: css`
    ${typography.heading5()};
  `,
  content: css`
    ${typography.body()};
    color: ${colorPalate.text.neutral};
  `,
  buttonWrapper: css`
    margin-top: ${spacing[32]};
  `,
};
