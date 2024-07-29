import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { type VariantProps, createVariation } from '@Utils/create-variation';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import type React from 'react';

interface AiButtonProps extends React.HTMLAttributes<HTMLButtonElement>, VariantProps<typeof buttonVariants> {
  type?: 'button' | 'submit';
}

const AiButton = ({ className, variant, size, children, type = 'button', ...props }: AiButtonProps) => {
  return (
    <button type={type} css={buttonVariants({ variant, size })} className={className} {...props}>
      {children}
    </button>
  );
};

export default AiButton;

const styles = {
  base: css`
		${styleUtils.resetButton};
		${typography.small('medium')};
		display: inline-flex;
		justify-content: center;
		align-items: center;
		white-space: nowrap;
		border-radius: ${borderRadius[54]};
		transition: color 0.3s ease, filter 0.3s ease;
		&:focus-visible {
			outline: none;
			shadow: ${shadow.button};
		}
	`,
  default: css`
		background: linear-gradient(73.09deg, #FF9645 18.05%, #FF6471 30.25%, #CF6EBD 55.42%, #A477D1 71.66%, #3E64DE 97.9%);
		color: ${colorTokens.text.white};

		&:hover {
			filter: hue-rotate(120deg);
		}
	`,
  secondary: css`
		background-color: ${colorTokens.action.secondary.default};
		color: ${colorTokens.text.primary};
	`,
  outline: css`
		position: relative;
		&::before {
			content: '';
			position: absolute;
			inset: 0;
			background: linear-gradient(73.09deg, #FF9645 18.05%, #FF6471 30.25%, #CF6EBD 55.42%, #A477D1 71.66%, #3E64DE 97.9%);
			color: ${colorTokens.text.primary};
			border: 2px solid transparent;
			border-radius: ${borderRadius[54]};
			-webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
			-webkit-mask-composite: xor;
			mask-composite: exclude;
		}

		&:hover {
			&::before {
				content: '';
				display: none;
			}
			background: linear-gradient(73.09deg, #FF9645 18.05%, #FF6471 30.25%, #CF6EBD 55.42%, #A477D1 71.66%, #3E64DE 97.9%);
			color: ${colorTokens.text.white};
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
  },
};

const buttonVariants = createVariation(
  {
    variants: {
      variant: {
        default: styles.default,
        secondary: styles.secondary,
        outline: styles.outline,
      },
      size: {
        default: styles.size.default,
        sm: styles.size.sm,
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  },
  styles.base,
);
