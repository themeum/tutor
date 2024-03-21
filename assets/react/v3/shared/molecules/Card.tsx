import { useRef, type ReactNode, useState } from 'react';
import { css } from '@emotion/react';
import { animated } from '@react-spring/web';

import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorPalate, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import Show from '@Controls/Show';

import { useCollapseExpandAnimation } from '@Hooks/useCollapseExpandAnimation';

interface CardProps {
	children: ReactNode;
	hasBorder?: boolean;
	title: string | ReactNode;
	subtitle?: string;
	actionTray?: ReactNode;
	collapsed?: boolean;
	noSeparator?: boolean;
}

const Card = ({
	children,
	hasBorder = false,
	title,
	subtitle,
	actionTray,
	collapsed = false,
	noSeparator = false,
}: CardProps) => {
	const [isCollapsed, setIsCollapsed] = useState<boolean>(collapsed);
	const cardRef = useRef<HTMLDivElement>(null);

	const collapseAnimation = useCollapseExpandAnimation({
		ref: cardRef,
		isOpen: !isCollapsed,
	});

	return (
		<div css={styles.wrapper(hasBorder)}>
			<div css={styles.headerWrapper(isCollapsed || noSeparator)}>
				<div css={styles.headerAndAction}>
					<div css={styles.header}>
						<h5 css={styles.title}>{title}</h5>
						<Show when={subtitle}>
							<p css={styles.subtitle}>{subtitle}</p>
						</Show>
					</div>

					<div css={styles.actions}>
						<Show when={actionTray}>{actionTray}</Show>
						<button css={styles.collapseButton} type="button" onClick={() => setIsCollapsed(!isCollapsed)}>
							<SVGIcon
								name={isCollapsed ? 'chevronDown' : 'chevronUp'}
								width={24}
								height={24}
								style={styles.arrowUpDown}
							/>
						</button>
					</div>
				</div>
			</div>
			<animated.div style={{ ...collapseAnimation }}>
				<div ref={cardRef} css={styles.cardBody}>
					{children}
				</div>
			</animated.div>
		</div>
	);
};

export default Card;

const styles = {
	wrapper: (hasBorder: boolean) => css`
    width: 100%;
    border-radius: ${borderRadius.card};
    background-color: ${colorPalate.basic.white};
    box-shadow: ${shadow.card};

    ${
			hasBorder &&
			css`
				box-shadow: none;
				border: 1px solid ${colorTokens.stroke.divider};
    	`
		}
  `,
	headerWrapper: (collapsed: boolean) => css`
		padding: ${spacing[8]};
		display: flex;
		flex-direction: column;
		justify-content: center;
		gap: ${spacing[4]};

		${
			!collapsed &&
			css`
				border-bottom: 1px solid ${colorTokens.stroke.divider};;
			`
		}
	`,
	headerAndAction: css`
		display: flex;
		justify-content: space-between;
		align-items: center;
	`,
	header: css`
		display: flex;
		flex-direction: column;
		padding: ${spacing[4]} ${spacing[16]};
	`,
	subtitle: css`
		${typography.caption()};
		color: ${colorTokens.text.subdued};
	`,
	title: css`
		${typography.body('medium')};
		color: ${colorTokens.text.primary};
		display: flex;
		align-items: center;
	`,
	collapseButton: css`
		${styleUtils.resetButton};
		display: flex;
		align-items: center;
	`,
	arrowUpDown: css`
    color: ${colorTokens.icon.default};
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: ${spacing[6]};
  `,
	cardBody: css`
		padding: ${spacing[16]} ${spacing[24]} ${spacing[32]} ${spacing[24]};
	`,
	actions: css`
		display: flex;
		align-items: center;
		gap: ${spacing[8]};
	`,
};
