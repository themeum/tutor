import { colorPalate } from '@Config/styles';
import { css } from '@emotion/react';
import React from 'react';

interface DividerProps {
  strokeWidth?: number;
}
const Divider = ({ strokeWidth = 1 }: DividerProps) => {
  return <div css={styles.divider(strokeWidth)} />;
};

export default Divider;

const styles = {
  divider: (strokeWidth: number) => css`
    width: 100%;
    height: ${strokeWidth}px;
    background-color: ${colorPalate.surface.neutral.hover};
  `,
};
