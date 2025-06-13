import { css } from '@emotion/react';

import { borderRadius, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';

import Show from '@TutorShared/controls/Show';
import { type IconCollection } from '@TutorShared/icons/types';
import SVGIcon from './SVGIcon';

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
    danger: '#f44337',
    info: '#D47E00',
    primary: '#D47E00',
  },
  icon: {
    warning: '#FAB000',
    success: '#FAB000',
    danger: '#f55e53',
    info: '#FAB000',
    primary: '#FAB000',
  },
  background: {
    warning: '#FBFAE9',
    success: '#FBFAE9',
    danger: '#fdd9d7',
    info: '#FBFAE9',
    primary: '#FBFAE9',
  },
};

const Alert = ({ children, type = 'warning', icon }: AlertProps) => {
  return (
    <div css={styles.wrapper({ type })}>
      <Show when={icon}>
        {(iconName) => <SVGIcon style={styles.icon({ type })} name={iconName} height={24} width={24} />}
      </Show>
      <span>{children}</span>
    </div>
  );
};

export default Alert;

const styles = {
  wrapper: ({ type }: { type: AlertType }) => css`
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

  icon: ({ type }: { type: AlertType }) => css`
    color: ${alertStyles.icon[type]};
    flex-shrink: 0;
  `,
};
