import type React from 'react';
import { css, type SerializedStyles } from '@emotion/react';

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
  action?: React.ReactNode;
  wrapperCss?: SerializedStyles;
}

const alertStyles = {
  text: {
    warning: '#D47E00',
    success: '#D47E00',
    danger: '#f44337',
    info: '#2B49CA',
    primary: '#2B49CA',
  },
  icon: {
    warning: '#FAB000',
    success: '#FAB000',
    danger: '#f55e53',
    info: '#2B49CA',
    primary: '#2B49CA',
  },
  background: {
    warning: '#FBFAE9',
    success: '#FBFAE9',
    danger: '#fdd9d7',
    info: '#E4EBFC',
    primary: '#E4EBFC',
  },
};

const Alert = ({ children, type = 'warning', icon, action, wrapperCss }: AlertProps) => {
  const hasAction = !!action;

  return (
    <div css={[styles.wrapper({ type, hasAction }), wrapperCss]}>
      <div css={styles.content}>
        <Show when={icon}>
          {(iconName) => <SVGIcon style={styles.icon({ type })} name={iconName} height={24} width={24} />}
        </Show>
        <span css={styles.text({ hasAction })}>{children}</span>
      </div>
      <Show when={action}>
        <div css={styles.action}>{action}</div>
      </Show>
    </div>
  );
};

export default Alert;

const styles = {
  wrapper: ({ type, hasAction }: { type: AlertType; hasAction?: boolean }) => css`
    ${typography.caption()};
    display: flex;
    align-items: ${hasAction ? 'center' : 'start'};
    justify-content: ${hasAction ? 'space-between' : 'flex-start'};
    padding: ${hasAction ? `${spacing[8]}` : `${spacing[8]} ${spacing[12]}`};
    width: 100%;
    border-radius: ${borderRadius.card};
    gap: ${hasAction ? spacing[12] : spacing[4]};
    background-color: ${alertStyles.background[type]};
    color: ${alertStyles.text[type]};
  `,

  content: css`
    display: flex;
    align-items: start;
    gap: ${spacing[4]};
    flex: 1;
  `,

  text: ({ hasAction }: { hasAction?: boolean }) => css`
    ${hasAction &&
    css`
      ${typography.small()};
      color: inherit;
      text-wrap: pretty;
    `}
  `,

  icon: ({ type }: { type: AlertType }) => css`
    color: ${alertStyles.icon[type]};
    flex-shrink: 0;
  `,

  action: css`
    flex-shrink: 0;
  `,
};
