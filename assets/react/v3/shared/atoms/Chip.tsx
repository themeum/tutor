import { borderRadius, colorPalate, letterSpacing, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { rgba } from 'polished';
import React, { ReactNode } from 'react';

type Variant = 'regular' | 'outlined';
type Color = 'default' | 'success' | 'warning' | 'danger';
type Size = 'regular' | 'small';

interface ChipProps {
  children: ReactNode;
  variant?: Variant;
  color?: Color;
  size?: Size;
}

const Chip = ({ children, variant = 'regular', color = 'default', size = 'regular' }: ChipProps) => {
  return <div css={styles.wrapper({ variant, color, size })}>{children}</div>;
};

export default Chip;

const colorMapping = {
  default: {
    border: colorPalate.border.default,
    background: rgba(colorPalate.border.default, 0.05),
    color: colorPalate.border.default,
  },
  success: {
    border: colorPalate.border.success.default,
    background: rgba(colorPalate.border.success.default, 0.1),
    color: colorPalate.border.success.default,
  },
  danger: {
    border: colorPalate.border.critical.default,
    background: rgba(colorPalate.border.critical.default, 0.1),
    color: colorPalate.border.critical.default,
  },
  warning: {
    border: colorPalate.surface.warning.default,
    background: rgba(colorPalate.surface.warning.default, 0.1),
    color: colorPalate.text.default,
  },
} as const;

const styles = {
  wrapper: ({ variant, color, size }: { variant: Variant; color: Color; size: Size }) => css`
    ${typography.body()};
    padding: ${spacing[4]} ${spacing[8]};
    background-color: ${colorPalate.surface.selected.default};
    border: 1px solid ${colorPalate.border.disabled};
    border-radius: ${borderRadius[50]};
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 60px;

    ${size === 'small' &&
    css`
      padding: 0 ${spacing[8]};
      min-width: 45px;
      ${typography.tiny()};
      border-radius: ${borderRadius[14]};
      letter-spacing: ${letterSpacing.wide};
    `}

    ${variant === 'outlined' &&
    css`
      background-color: ${colorMapping[color].background};
      border-color: ${colorMapping[color].border};
      color: ${colorMapping[color].color};
    `}
  `,
};
