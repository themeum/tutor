import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { css } from '@emotion/react';
import React from 'react';
import SVGIcon from './SVGIcon';
import Tooltip from './Tooltip';

interface BoxProps extends React.HTMLAttributes<HTMLDivElement> {
	bordered?: boolean;
}

export const Box = React.forwardRef<HTMLDivElement, BoxProps>(({ children, className, bordered = false }, ref) => {
	return (
		<div ref={ref} className={className} css={styles.wrapper(bordered)}>
			{children}
		</div>
	);
});

Box.displayName = 'Box';

interface BoxTitleProps extends React.HTMLAttributes<HTMLDivElement> {
	separator?: boolean;
	tooltip?: string | React.ReactNode;
}

export const BoxTitle = React.forwardRef<HTMLDivElement, BoxTitleProps>(
	({ children, className, separator = false, tooltip }, ref) => {
		return (
			<div ref={ref} className={className} css={styles.title(separator)}>
				<span>{children}</span>
				<Show when={tooltip}>
					<Tooltip content={tooltip}>
						<SVGIcon name="info" width={20} height={20} />
					</Tooltip>
				</Show>
			</div>
		);
	},
);

BoxTitle.displayName = 'BoxTitle';

export const BoxSubtitle = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
	({ children, className }, ref) => {
		return (
			<div ref={ref} className={className} css={styles.subtitle}>
				<span>{children}</span>
			</div>
		);
	},
);

BoxSubtitle.displayName = 'BoxSubtitle';

const styles = {
	wrapper: (bordered: boolean) => css`
		background-color: ${colorTokens.background.white};
		border-radius: ${borderRadius[8]};
		padding: ${spacing[12]} ${spacing[20]} ${spacing[20]};

		${bordered &&
		css`
			border: 1px solid ${colorTokens.stroke.default};
		`}
	`,
	title: (separator: boolean) => css`
		${typography.body('medium')};
		color: ${colorTokens.text.title};
		display: flex;
		gap: ${spacing[4]};
		align-items: center;

		${separator &&
		css`
			border-bottom: 1px solid ${colorTokens.stroke.divider};
			padding: ${spacing[12]} ${spacing[20]};
		`}

		& > div {
			height: 20px;

			svg {
				color: ${colorTokens.icon.hints};
			}
		}

		& > span {
			display: inline-block;
		}
	`,
	subtitle: css`
		${typography.caption()};
		color: ${colorTokens.text.hints};
	`,
};
