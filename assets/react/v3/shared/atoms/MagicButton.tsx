import { css } from '@emotion/react';
import React from 'react';

import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { type VariantProps, createVariation } from '@Utils/create-variation';
import { styleUtils } from '@Utils/style-utils';
import LoadingSpinner from './LoadingSpinner';

interface MagicButtonProps extends React.HTMLAttributes<HTMLButtonElement>, VariantProps<typeof buttonVariants> {
  type?: 'button' | 'submit';
  disabled?: boolean;
  roundedFull?: boolean;
  loading?: boolean;
}

const MagicButton = React.forwardRef<HTMLButtonElement, MagicButtonProps>(
  (
    { className, variant, size, children, type = 'button', disabled = false, roundedFull = true, loading, ...props },
    ref,
  ) => (
    <button
      type={type}
      ref={ref}
      css={buttonVariants({ variant, size, rounded: roundedFull ? 'true' : 'false' })}
      className={className}
      disabled={disabled}
      {...props}
    >
      <span css={styles.buttonSpan}>{loading ? <LoadingSpinner size={24} /> : children}</span>
    </button>
  ),
);

export default MagicButton;

const styles = {
  buttonSpan: css`
    ${styleUtils.flexCenter()};
    z-index: ${zIndex.positive}; // This is a hack to make sure the text is on top of the gradient
  `,
  base: css`
    ${styleUtils.resetButton};
    ${typography.small('medium')};
    display: flex;
    gap: ${spacing[4]};
    width: 100%;
    justify-content: center;
    align-items: center;
    white-space: nowrap;
    position: relative;
    overflow: hidden;
    transition: box-shadow 0.5s ease;

    &:focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
    }

    &:disabled {
      cursor: not-allowed;
      background: ${colorTokens.action.primary.disable};
      pointer-events: none;
      color: ${colorTokens.text.disable};
      border-color: ${colorTokens.stroke.disable};
    }
  `,
  default: css`
    background: ${colorTokens.ai.gradient_1};
    color: ${colorTokens.text.white};

    &::before {
      content: '';
      position: absolute;
      inset: 0;
      background: ${colorTokens.ai.gradient_2};
      opacity: 0;
      transition: opacity 0.5s ease;
    }

    &:hover::before {
      opacity: 1;
    }
  `,
  secondary: css`
    background-color: ${colorTokens.action.secondary.default};
    color: ${colorTokens.text.brand};
    border-radius: ${borderRadius[6]};

    &:hover {
      background-color: ${colorTokens.action.secondary.hover};
    }
  `,
  outline: css`
		position: relative;
		&::before {
			content: '';
			position: absolute;
			inset: 0;
			background: ${colorTokens.ai.gradient_1};
			color: ${colorTokens.text.primary};
			border: 1px solid transparent;
      -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
      mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
			-webkit-mask-composite: xor;
			mask-composite: exclude;
		}

		&:hover {
			&::before {
				background: ${colorTokens.ai.gradient_2};
			}
		}
	`,
  primaryOutline: css`
    border: 1px solid ${colorTokens.brand.blue};
    color: ${colorTokens.brand.blue};

    &:hover {
      background-color: ${colorTokens.brand.blue};
      color: ${colorTokens.text.white};
    }
  `,
  primary: css`
    background-color: ${colorTokens.brand.blue};
    color: ${colorTokens.text.white};
  `,
  ghost: css`
    background-color: transparent;
    color: ${colorTokens.text.subdued};
    border-radius: ${borderRadius[4]};

    &:hover {
      color: ${colorTokens.text.primary};
    }
  `,
  plain: css`
    span {
      background: ${colorTokens.text.ai.gradient};
      background-clip: text;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;

      &:hover {
        background: ${colorTokens.ai.gradient_2};
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
    }
  `,
  size: {
    default: css`
      height: 32px;
      padding-inline: ${spacing[12]};
      padding-block: ${spacing[4]};
    `,
    sm: css`
      height: 24px;
      padding-inline: ${spacing[10]};
    `,
    icon: css`
      width: 32px;
      height: 32px;
    `,
  },
  rounded: {
    true: css`
      border-radius: ${borderRadius[54]};

			&::before {
				border-radius: ${borderRadius[54]};
			}
    `,
    false: css`
      border-radius: ${borderRadius[4]};

			&::before {
				border-radius: ${borderRadius[4]};
			}
    `,
  },
};

const buttonVariants = createVariation(
  {
    variants: {
      variant: {
        default: styles.default,
        primary: styles.primary,
        secondary: styles.secondary,
        outline: styles.outline,
        primary_outline: styles.primaryOutline,
        ghost: styles.ghost,
        plain: styles.plain,
      },
      size: {
        default: styles.size.default,
        sm: styles.size.sm,
        icon: styles.size.icon,
      },
      rounded: {
        true: styles.rounded.true,
        false: styles.rounded.false,
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
      rounded: 'true',
    },
  },
  styles.base,
);
