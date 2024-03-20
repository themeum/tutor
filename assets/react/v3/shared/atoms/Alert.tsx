import { css } from '@emotion/react';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import type { IconCollection } from '@Utils/types';
import { typography } from '@Config/typography';

import SVGIcon from './SVGIcon';
import Show from '@Controls/Show';

type AlertType = 'success' | 'warning' | 'danger' | 'info' | 'primary';

interface AlertProps {
	children: React.ReactNode;
	type?: AlertType;
	icon?: IconCollection;
}

const alertStyles = {
	text: {
		warning: '#D47E00',
		success: '#D47E00',
		danger: '#D47E00',
		info: '#D47E00',
		primary: '#D47E00',
	},
	icon: {
		warning: '#FAB000',
		success: '#FAB000',
		danger: '#FAB000',
		info: '#FAB000',
		primary: '#FAB000',
	},
	background: {
		warning: '#FBFAE9',
		success: '#FBFAE9',
		danger: '#FBFAE9',
		info: '#FBFAE9',
		primary: '#FBFAE9',
	},
};

const Alert = ({ children, type = 'warning', icon }: AlertProps) => {
	return (
		<div css={styles.wrapper({ type })}>
			<Show when={icon}>
				<SVGIcon style={styles.icon({ type })} name={icon as IconCollection} height={24} width={24} />
			</Show>
			<span>{children}</span>
		</div>
	);
};

export default Alert;

const styles = {
	wrapper: ({
		type,
	}: {
		type: AlertType;
	}) => css`
    ${typography.caption()};
    display: flex;
    align-items: start;
    padding: ${spacing[8]} ${spacing[12]};
    width: 100%;
    border-radius: ${borderRadius.card};
    gap: ${spacing[4]};
    background-color: ${alertStyles.background[type]};
    color: ${alertStyles.text[type]};
  `,

	icon: ({
		type,
	}: {
		type: AlertType;
	}) => css`
    color: ${alertStyles.icon[type]};
    flex-shrink: 0;
  `,
};
