import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, fontSize, fontWeight, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css, keyframes, SerializedStyles } from '@emotion/react';
import { styleUtils } from '@Utils/style-utils';
import React, { ReactNode } from 'react';

export enum ButtonVariant {
  primary = 'primary',
  secondary = 'secondary',
  critical = 'critical',
  plain = 'plain',
  plainMonochrome = 'plainMonochrome',
  plainCritical = 'plainCritical',
}

export enum ButtonSize {
  default = 'default',
  large = 'large',
  slim = 'slim',
}

export enum ButtonIconPosition {
  left = 'left',
  right = 'right',
}

const spin = keyframes`
  0% {
    transform: rotate(0);
  }

  100% {
    transform: rotate(360deg);
  }
`;

const styles = {
  button: (
    variant: ButtonVariant,
    size: ButtonSize,
    iconPosition: ButtonIconPosition | undefined,
    loading: boolean,
    disabled: boolean,
  ) => css`
    ${styleUtils.resetButton};
    display: inline-block;
    ${typography.body('medium')};
    text-align: center;
    text-decoration: none;
    vertical-align: middle;
    cursor: pointer;
    user-select: none;
    background-color: transparent;
    border: 0;
    padding: ${spacing[8]} ${spacing[16]};
    border-radius: ${borderRadius[6]};
    z-index: ${zIndex.level};
    transition: all 150ms ease-in-out;
    position: relative;

    ${size === ButtonSize.large &&
    css`
      font-size: ${fontSize[16]};
      padding: ${spacing[12]} ${spacing[32]};
    `}

    ${size === ButtonSize.slim &&
    css`
      padding: 5px ${spacing[16]};
    `}
    
    ${variant === ButtonVariant.primary &&
    css`
      background-color: ${colorPalate.actions.primary.default};
      color: ${colorPalate.basic.white};
      box-shadow: ${shadow.button};

      &:hover {
        background-color: ${colorPalate.actions.primary.hover};
      }

      &:active {
        background-color: ${colorPalate.actions.primary.pressed};
      }

      ${(disabled || loading) &&
      css`
        background-color: ${colorPalate.actions.primary.disabled};
        color: ${colorPalate.text.disabled};
      `}
    `}

    ${variant === ButtonVariant.secondary &&
    css`
      background-color: ${colorPalate.actions.secondary.default};
      color: ${colorPalate.text.default};
      font-weight: ${fontWeight.regular};
      line-height: ${lineHeight[18]};
      border: 1px solid ${colorPalate.border.default};
      transition: box-shadow 0.2s ease;

      &:hover {
        background-color: ${colorPalate.actions.secondary.default};
        box-shadow: ${shadow.button};
      }

      &:active {
        background-color: ${colorPalate.actions.secondary.pressed};
      }

      ${(disabled || loading) &&
      css`
        background-color: ${colorPalate.actions.secondary.disabled};
        color: ${colorPalate.text.disabled};
        border-color: ${colorPalate.border.disabled};
      `}
    `}

    ${variant === ButtonVariant.critical &&
    css`
      background-color: ${colorPalate.actions.critical.default};
      color: ${colorPalate.basic.white};
      box-shadow: ${shadow.button};

      &:hover {
        background-color: ${colorPalate.actions.critical.hover};
      }

      &:active {
        background-color: ${colorPalate.actions.critical.pressed};
      }

      ${(disabled || loading) &&
      css`
        background-color: ${colorPalate.actions.critical.disabled};
        color: ${colorPalate.text.disabled};
      `}
    `}

    ${variant === ButtonVariant.plain &&
    css`
      background-color: transparent;
      font-weight: ${fontWeight.regular};
      color: ${colorPalate.interactive.default};
      padding: ${spacing[2]} ${spacing[6]};
      border-radius: ${borderRadius[4]};

      &:hover {
        color: ${colorPalate.interactive.hover};
        text-decoration: underline;
      }

      &:active {
        color: ${colorPalate.interactive.depressed};
      }

      ${(disabled || loading) &&
      css`
        color: ${colorPalate.interactive.disabled};
      `}
    `}

    ${variant === ButtonVariant.plainMonochrome &&
    css`
      background-color: transparent;
      font-weight: ${fontWeight.regular};
      color: ${colorPalate.text.neutral};
      padding: ${spacing[2]} ${spacing[6]};
      border-radius: ${borderRadius[4]};

      &:hover {
        color: ${colorPalate.text.default};
        text-decoration: underline;
      }

      &:active {
        color: ${colorPalate.text.default};
      }

      ${(disabled || loading) &&
      css`
        color: ${colorPalate.interactive.disabled};
      `}
    `}

    ${variant === ButtonVariant.plainCritical &&
    css`
      background-color: transparent;
      font-weight: ${fontWeight.regular};
      color: ${colorPalate.interactive.critical.default};
      padding: ${spacing[2]} ${spacing[6]};
      border-radius: ${borderRadius[4]};

      &:hover {
        color: ${colorPalate.interactive.critical.hover};
        text-decoration: underline;
      }

      &:active {
        color: ${colorPalate.interactive.critical.depressed};
      }

      ${(disabled || loading) &&
      css`
        color: ${colorPalate.interactive.critical.disabled};
      `}
    `}

    ${(disabled || loading) &&
    css`
      pointer-events: none;
      box-shadow: none;
    `}
  `,
  buttonContent: (loading: boolean, disabled: boolean) => css`
    display: flex;
    align-items: center;

    ${loading &&
    !disabled &&
    css`
      color: transparent;
    `}
  `,
  buttonIcon: (iconPosition: ButtonIconPosition) => css`
    display: grid;
    place-items: center;
    margin-right: ${spacing[6]};
    ${iconPosition === ButtonIconPosition.right &&
    css`
      margin-right: 0;
      margin-left: ${spacing[6]};
    `}
  `,
  spinner: css`
    position: absolute;
    visibility: visible;
    display: flex;
    left: 50%;
    transform: translate(-50%);
    & svg {
      animation: ${spin} 1.5s linear infinite;
    }
  `,
};

interface ButtonProps {
  children?: ReactNode;
  variant?: ButtonVariant;
  type?: 'submit' | 'button';
  size?: ButtonSize;
  icon?: React.ReactNode;
  iconPosition?: ButtonIconPosition;
  disabled?: boolean;
  loading?: boolean;
  onClick?: React.MouseEventHandler<HTMLButtonElement>;
  tabIndex?: number;
  buttonCss?: SerializedStyles;
  buttonContentCss?: SerializedStyles;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      type = 'button',
      children,
      variant = ButtonVariant.primary,
      size = ButtonSize.default,
      icon,
      iconPosition = ButtonIconPosition.left,
      loading = false,
      disabled = false,
      tabIndex,
      onClick,
      buttonCss,
      buttonContentCss,
    },
    ref,
  ) => {
    return (
      <button
        type={type}
        ref={ref}
        css={[styles.button(variant, size, iconPosition, loading, disabled), buttonCss]}
        onClick={onClick}
        tabIndex={tabIndex}
      >
        {loading && !disabled && (
          <span css={styles.spinner}>
            <SVGIcon name="spinner" width={18} height={18} />
          </span>
        )}
        <span css={[styles.buttonContent(loading, disabled), buttonContentCss]}>
          {icon && iconPosition === ButtonIconPosition.left && (
            <span css={styles.buttonIcon(iconPosition)}>{icon}</span>
          )}
          {children}
          {icon && iconPosition === ButtonIconPosition.right && (
            <span css={styles.buttonIcon(iconPosition)}>{icon}</span>
          )}
        </span>
      </button>
    );
  },
);

export default Button;
