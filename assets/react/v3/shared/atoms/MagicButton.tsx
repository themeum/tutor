import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { type VariantProps, createVariation } from '@Utils/create-variation';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import React from 'react';

interface MagicButtonProps extends React.HTMLAttributes<HTMLButtonElement>, VariantProps<typeof buttonVariants> {
  type?: 'button' | 'submit';
  disabled?: boolean;
  roundedFull?: boolean;
}

const MagicButton = React.forwardRef<HTMLButtonElement, MagicButtonProps>(
  ({ className, variant, size, children, type = 'button', disabled = false, roundedFull = true, ...props }, ref) => (
    <button
      type={type}
      ref={ref}
      css={buttonVariants({ variant, size, rounded: roundedFull ? 'true' : 'false' })}
      className={className}
      disabled={disabled}
      {...props}
    >
      {children}
    </button>
  ),
);

export default MagicButton;

const styles = {
  base: css`
		${styleUtils.resetButton};
		${typography.small('medium')};
		display: inline-flex;
		gap: ${spacing[4]};
		width: 100%;
		justify-content: center;
		align-items: center;
		white-space: nowrap;
		transition: 0.5s;

		&:focus-visible {
			outline: none;
			shadow: ${shadow.button};
		}

		&:disabled {
			background: ${colorTokens.action.primary.disable};
			pointer-events: none;
			color: ${colorTokens.text.disable};
			border-color: ${colorTokens.stroke.disable};
			&::before {
				display: none;
			}
		}
	`,
  default: css`
		background: linear-gradient(73.09deg, #FF9645 18.05%, #FF6471 30.25%, #CF6EBD 55.42%, #A477D1 71.66%, #3E64DE 97.9%);
		color: ${colorTokens.text.white};

		&:hover {
			background: linear-gradient(71.97deg, #FF9645 18.57%, #FF6471 63.71%, #CF6EBD 87.71%, #9B62D4 107.71%, #3E64DE 132.85%);
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
			border: 1px solid;
			background: linear-gradient(73.09deg, #FF9645 18.05%, #FF6471 30.25%, #CF6EBD 55.42%, #A477D1 71.66%, #3E64DE 97.9%);
			color: ${colorTokens.text.primary};
			border: 1px solid transparent;
			-webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
			-webkit-mask-composite: xor;
			mask-composite: exclude;
		}

		&:hover {
			&::before {
				background: linear-gradient(0deg, #FF9645 18.05%, #FF6471 30.25%, #CF6EBD 55.42%, #A477D1 71.66%, #3E64DE 97.9%);
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
  size: {
    default: css`
			height: 32px;
			padding-inline: ${spacing[16]};
			padding-block: ${spacing[4]};
		`,
    sm: css`
			height: 24px;
			padding-inline: ${spacing[12]};
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
