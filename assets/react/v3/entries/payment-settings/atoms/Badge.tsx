import Show from '@Controls/Show';
import { borderRadius, colorTokens, fontSize, lineHeight, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import type { ReactNode } from 'react';

export type BadgeVariant = 'neutral' | 'success' | 'warning';

interface BadgeProps {
  children: ReactNode;
  variant?: BadgeVariant;
  icon?: React.ReactNode;
}

const Badge = ({ children, variant = 'neutral', icon }: BadgeProps) => {
  return (
    <div css={styles.wrapper({ variant })}>
      <Show when={icon}>{icon}</Show>
      {children}
    </div>
  );
};

export default Badge;

const colorMapping = {
  neutral: {
    background: 'transparent',
    iconColor: colorTokens.icon.default,
  },
  success: {
    background: colorTokens.background.success.fill40,
    iconColor: colorTokens.icon.success,
  },
  warning: {
    background: colorTokens.background.warning.fill40,
    iconColor: colorTokens.icon.warning,
  },
} as const;

const styles = {
  wrapper: ({ variant }: { variant: BadgeVariant }) => css`
    font-size: ${fontSize[12]};
    line-height: ${lineHeight[16]};
    padding: ${spacing[4]} ${spacing[8]};
    background-color: ${colorMapping[variant].background};
    color: #202223;
    border-radius: ${borderRadius[4]};
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: ${spacing[8]};
    min-width: 60px;

    svg {
      color: ${colorMapping[variant].iconColor};
    }
  `,
};
