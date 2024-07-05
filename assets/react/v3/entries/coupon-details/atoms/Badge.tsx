import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { styleUtils } from '@Utils/style-utils';
import React from 'react';

const badgeVariants = {
	default: {
		background: colorTokens.background.status.drip,
		foreground: colorTokens.text.status.primary,
		border: colorTokens.stroke.neutral,
	},
	secondary: {
		background: colorTokens.background.status.cancelled,
		foreground: colorTokens.text.status.cancelled,
		border: colorTokens.stroke.status.cancelled,
	},
	critical: {
		background: colorTokens.background.status.errorFail,
		foreground: colorTokens.text.status.failed,
		border: colorTokens.stroke.status.fail,
	},
	warning: {
		background: colorTokens.background.status.warning,
		foreground: colorTokens.text.status.pending,
		border: colorTokens.stroke.status.warning,
	},
	success: {
		background: colorTokens.background.status.success,
		foreground: colorTokens.text.status.completed,
		border: colorTokens.stroke.status.success,
	},
	outline: {
		background: colorTokens.background.white,
		foreground: colorTokens.text.status.cancelled,
		border: colorTokens.stroke.status.cancelled,
	},
};

type Variant = 'default' | 'secondary' | 'critical' | 'warning' | 'success' | 'outline';

interface BadgeProps extends React.HTMLAttributes<HTMLDivElement> {
	variant?: Variant;
}

export const Badge = React.forwardRef<HTMLDivElement, BadgeProps>(
	({ className, children, variant = 'default' }, ref) => {
		return (
			<div ref={ref} className={className} css={styles.badge(variant)}>
				{children}
			</div>
		);
	},
);

Badge.displayName = 'Badge';

const styles = {
	badge: (variant: Variant) => css`
		${typography.small('medium')};
		display: inline-flex;
		align-items: center;
		border-radius: ${borderRadius[30]};
		padding: ${spacing[4]} ${spacing[8]};
		max-height: 24px;
		${styleUtils.textEllipsis};

		border: 1px solid ${badgeVariants[variant].border};
		background-color: ${badgeVariants[variant].background};
		color: ${badgeVariants[variant].foreground};
	`,
};
