import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import SVGIcon from './SVGIcon';
import type { IconCollection } from '@Utils/types';
import { typography } from '@Config/typography';

type AlertType = 'success' | 'warning' | 'danger' | 'info' | 'primary';

interface AlertProps {
	children: React.ReactNode;
	type?: AlertType;
	icon?: IconCollection;
}

const Alert = ({ children, type = 'warning', icon }: AlertProps) => {
	return (
		<div css={styles.wrapper({ type })}>
			<SVGIcon style={styles.icon({ type })} name={icon as IconCollection} height={24} width={24} />
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
    background-color: ${colorTokens.background.alert[type]};
    color: ${colorTokens.text.alert[type]};
  `,

	icon: ({
		type,
	}: {
		type: AlertType;
	}) => css`
    color: ${colorTokens.icon.alert[type]};
    flex-shrink: 0;
  `,
};
