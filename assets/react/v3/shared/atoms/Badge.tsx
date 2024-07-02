import { borderRadius, colorPalate, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import type { ReactNode } from 'react';

export type BadgeVariant = 'neutral' | 'neutralDefault' | 'informational' | 'success' | 'warning' | 'attention' | 'success_fade';

interface BadgeProps {
  children: ReactNode;
  variant?: BadgeVariant;
}

const Badge = ({ children, variant = 'neutral' }: BadgeProps) => {
  return <div css={styles.wrapper({ variant })}>{children}</div>;
};

export default Badge;

const colorMapping = {
  neutral: {
    background: 'transparent',
  },
  neutralDefault: {
    background: colorPalate.surface.neutral.default,
  },
  informational: {
    background: colorPalate.surface.highlight.default,
  },
  success: {
    background: colorPalate.surface.success.default,
  },
  warning: {
    background: colorPalate.surface.warning.default,
  },
  attention: {
    background: colorPalate.surface.critical.subDuedDepressed,
  },
  success_fade: {
    background: colorTokens.background.status.success
  }
} as const;

const styles = {
  wrapper: ({ variant }: { variant: BadgeVariant }) => css`
    ${typography.body()};
    padding: ${spacing[2]} ${spacing[8]};
    background-color: ${colorMapping[variant].background};
    color: ${colorPalate.text.default};
    border-radius: ${borderRadius[50]};
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 60px;
  `,
};
