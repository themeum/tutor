import { borderRadius, lineHeight, shadow } from '@Config/styles';
import { css } from '@emotion/react';
import React, { type ReactNode } from 'react';

import type { ButtonSize, ButtonVariant } from './Button';

interface ButtonGroupProps {
  children?: ReactNode;
  variant?: ButtonVariant;
  size?: ButtonSize;
  gap?: number;
  fullWidth?: boolean;
  disabled?: boolean;
}

const ButtonGroup = ({
  children,
  variant: groupVariant = 'primary',
  size,
  gap,
  fullWidth = false,
  disabled: groupDisabled = false,
}: ButtonGroupProps) => {
  return (
    <div css={styles.buttonContainer(gap, fullWidth)}>
      {React.Children.map(children, (child) => {
        if (React.isValidElement(child)) {
          const { variant, disabled } = child.props;
          const childProps = {
            variant: variant || groupVariant,
            size,
            disabled: disabled || groupDisabled,
            buttonCss: styles.buttonCss(variant || groupVariant, gap, fullWidth),
            buttonContentCss: styles.buttonContentCss(fullWidth),
          };

          return React.cloneElement(child, childProps);
        }
        return child;
      })}
    </div>
  );
};
export default ButtonGroup;

const styles = {
  buttonContainer: (gap: number | undefined, fullWidth: boolean) => css`
    display: flex;
    overflow: hidden;

    ${
      !fullWidth &&
      css`
      width: fit-content;
    `
    }

    ${
      gap &&
      css`
      gap: ${gap}px;
    `
    }
  `,
  buttonCss: (variant: ButtonVariant, gap: number | undefined, fullWidth: boolean) => css`
    ${
      variant === 'secondary' &&
      css`
      border: none;
      line-height: ${lineHeight[20]};
      box-shadow: ${shadow.combinedButton};

      &:hover {
        box-shadow: ${shadow.combinedButton};
      }

      ${
        gap &&
        css`
        box-shadow: ${shadow.combinedButtonExtend};

        &:hover {
          box-shadow: ${shadow.combinedButtonExtend};
        }
      `
      }
    `
    }

    &:last-of-type {
      ${
        variant === 'secondary' &&
        css`
        box-shadow: ${shadow.combinedButtonExtend};

        &:hover {
          box-shadow: ${shadow.combinedButtonExtend};
        }
      `
      }
    }

    ${
      !gap &&
      css`
      border-radius: 0;
      &:first-of-type {
        border-radius: ${borderRadius[6]} 0px 0px ${borderRadius[6]};
      }
      &:last-of-type {
        border-radius: 0px ${borderRadius[6]} ${borderRadius[6]} 0px;
      }
    `
    }

    ${
      fullWidth &&
      css`
      flex: auto;
    `
    }
  `,
  buttonContentCss: (fullWidth: boolean) => css`
    ${
      fullWidth &&
      css`
      justify-content: center;
    `
    }
  `,
};
