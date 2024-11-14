import Show from '@/v3/shared/controls/Show';
import Button from '@Atoms/Button';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { type SerializedStyles, css } from '@emotion/react';
import type { ReactNode } from 'react';

interface EmptyStateProps {
  emptyStateImage: string;
  imageAltText: string;
  title?: string;
  content?: string | ReactNode;
  buttonText?: string;
  action?: () => void;
  orientation?: 'horizontal' | 'vertical';
  messageWrapper?: SerializedStyles;
  isDisabledButton?: boolean;
}

const EmptyState = ({
  emptyStateImage,
  imageAltText,
  title,
  content,
  buttonText,
  action,
  messageWrapper,
  orientation = 'horizontal',
  isDisabledButton = false,
}: EmptyStateProps) => {
  return (
    <div css={styles.bannerWrapper({ orientation })}>
      <img src={emptyStateImage} alt={imageAltText} />
      <div css={[styles.messageWrapper({ orientation }), messageWrapper]}>
        <Show when={!!title}>
          <h5 css={styles.title}>{title}</h5>
        </Show>
        <Show when={!!content}>
          <div css={styles.content}>{content}</div>
        </Show>
        <Show when={!!buttonText}>
          <div css={styles.buttonWrapper}>
            <Button variant="primary" onClick={action} disabled={isDisabledButton}>
              {buttonText}
            </Button>
          </div>
        </Show>
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
    ${
      orientation === 'horizontal' &&
      css`
      grid-template-columns: 278px auto;
      gap: ${spacing[56]};
      justify-content: start;
    `
    }

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

    ${
      orientation === 'horizontal' &&
      css`
      max-width: 364px;
    `
    }
    ${
      orientation === 'vertical' &&
      css`
      text-align: center;
    `
    }
  `,
  title: css`
    ${typography.heading5()};
  `,
  content: css`
    ${typography.body()};
    color: ${colorTokens.text.hints};
  `,
  buttonWrapper: css`
    margin-top: ${spacing[32]};
  `,
};
