import { borderRadius, colorPalate, colorTokens, fontSize, lineHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import type { ReactNode } from 'react';

export type BadgeVariant = 'neutral' | 'success' | 'warning';

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
  success: {
    background: colorTokens.background.success.fill40,
  },
  warning: {
    background: colorTokens.background.warning.fill40,
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
    min-width: 60px;
  `,
};
