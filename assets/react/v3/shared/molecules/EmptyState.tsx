import { css } from '@emotion/react';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { isDefined } from '@Utils/types';

type EmptyStateSize = 'small' | 'medium';

interface EmptyStateProps {
  emptyStateImage?: string;
  emptyStateImage2x?: string;
  imageAltText?: string;
  title: string;
  size?: EmptyStateSize;
  description?: string;
  actions?: React.ReactNode;
  removeBorder?: boolean;
}

const EmptyState = ({
  emptyStateImage,
  emptyStateImage2x,
  imageAltText,
  title,
  size = 'medium',
  description,
  actions,
  removeBorder = true,
}: EmptyStateProps) => {
  return (
    <div css={styles.bannerWrapper(size, removeBorder, !!isDefined(emptyStateImage))}>
      <Show when={emptyStateImage}>
        <img src={emptyStateImage} alt={imageAltText} srcSet={emptyStateImage2x ? `${emptyStateImage2x} 2x` : ''} />
      </Show>
      <div css={styles.messageWrapper(size)}>
        <h5 css={styles.title(size)}>{title}</h5>
        <Show when={description}>
          <p css={styles.description(size)}>{description}</p>
        </Show>
        <Show when={actions}>
          <div css={styles.actionWrapper(size)}>{actions}</div>
        </Show>
      </div>
    </div>
  );
};

export default EmptyState;

const styles = {
  bannerWrapper: (size: EmptyStateSize, removeBorder: boolean, hasImage: boolean) => css`
    display: grid;
    place-items: center;
    justify-content: center;
    gap: ${spacing[36]};
    padding: ${hasImage ? `${spacing[16]} ${spacing[20]}` : `${spacing[20]}`};

    ${
      !removeBorder &&
      css`
        border: 1px solid ${colorTokens.stroke.divider};
        border-radius: ${borderRadius.card};
        background-color: ${colorTokens.background.white};
      `
    }

    ${
      size === 'small' &&
      css`
      gap: ${spacing[12]};
      padding: ${hasImage ? spacing[12] : spacing[16]};
      padding-bottom: ${hasImage ? spacing[24] : undefined};
    `
    }

    & img {
      max-width: 640px;
      width: 100%;
      height: auto;
      border-radius: ${borderRadius[10]};
      overflow: hidden;
      object-position: center;
      object-fit: cover;
      ${
        size === 'small' &&
        css`
          max-width: 282px;
        `
      }
    }
  `,
  messageWrapper: (size: EmptyStateSize) => css`
    display: flex;
    flex-direction: column;
    max-width: 566px;
    width: 100%;
    gap: ${spacing[12]};
    text-align: center;

    ${
      size === 'small' &&
      css`
        gap: ${spacing[8]};
      `
    }
  `,
  title: (size: EmptyStateSize) => css`
    ${typography.heading5()};
    color: ${colorTokens.text.primary};

    ${
      size === 'small' &&
      css`
        ${typography.caption('medium')};
        color: ${colorTokens.text.primary};
      `
    }
  `,
  description: (size: EmptyStateSize) => css`
    ${typography.body()};
    color: ${colorTokens.text.hints};

    ${
      size === 'small' &&
      css`
        ${typography.tiny()};
        color: ${colorTokens.text.hints};
      `
    }
  `,
  actionWrapper: (size: EmptyStateSize) => css`
    margin-top: ${spacing[20]};
    display: flex;
    justify-content: center;
    align-items: center;
    gap: ${spacing[12]};

    ${
      size === 'small' &&
      css`
        gap: ${spacing[8]};
        margin-top: ${spacing[8]};
      `
    }
  `,
};
