import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, fontSize, fontWeight, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { css, keyframes, SerializedStyles } from '@emotion/react';
import { styleUtils } from '@Utils/style-utils';
import React, { ReactNode } from 'react';

export type ButtonVariant = 'primary' | 'secondary' | 'outlined' | 'tertiary' | 'danger' | 'text';
export type ButtonSize = 'large' | 'medium' | 'small';
export type ButtonIconPosition = 'left' | 'right';

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
    disabled: boolean
  ) => css`
    ${styleUtils.resetButton};
    display: inline-block;
    font-size: ${fontSize[15]};
    line-height: ${lineHeight[24]};
    font-weight: ${fontWeight.medium};
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

    ${size === 'large' &&
    css`
      padding: ${spacing[12]} ${spacing[32]};
    `}

    ${size === 'small' &&
    css`
      font-size: ${fontSize[13]};
      line-height: ${lineHeight[20]};
      padding: ${spacing[6]} ${spacing[16]};
    `}
    
    ${variant === 'primary' &&
    css`
      background-color: ${colorTokens.action.primary.default};
      color: ${colorTokens.text.white};

      &:hover {
        background-color: ${colorTokens.action.primary.hover};
      }

      &:active {
        background-color: ${colorTokens.action.primary.active};
      }

      &:focus {
        box-shadow: ${shadow.focus};
      }

      ${(disabled || loading) &&
      css`
        background-color: ${colorTokens.action.primary.disable};
        color: ${colorTokens.text.disable};
      `}
    `}

    ${variant === 'secondary' &&
    css`
      background-color: ${colorTokens.action.secondary.default};
      color: ${colorTokens.text.brand};

      &:hover {
        background-color: ${colorTokens.action.secondary.hover};
      }

      &:active {
        background-color: ${colorTokens.action.secondary.active};
      }

      &:focus {
        box-shadow: ${shadow.focus};
      }

      ${(disabled || loading) &&
      css`
        background-color: ${colorTokens.action.primary.disable};
        color: ${colorTokens.text.disable};
      `}
    `}

    ${variant === 'outlined' &&
    css`
      background-color: ${colorTokens.action.outline.default};
      color: ${colorTokens.text.brand};
      box-shadow: inset 0 0 0 1px ${colorTokens.stroke.brand};

      &:hover {
        background-color: ${colorTokens.action.outline.hover};
      }

      &:active {
        background-color: ${colorTokens.action.outline.active};
      }

      &:focus {
        box-shadow: inset 0 0 0 1px ${colorTokens.stroke.brand}, ${shadow.focus};
      }

      ${(disabled || loading) &&
      css`
        color: ${colorTokens.text.disable};
        box-shadow: inset 0 0 0 1px ${colorTokens.action.outline.disable};
      `}
    `}

    ${variant === 'tertiary' &&
    css`
      background-color: ${colorTokens.background.white};
      color: ${colorTokens.text.subdued};
      box-shadow: inset 0 0 0 1px ${colorTokens.stroke.default};

      &:hover {
        background-color: ${colorTokens.background.hover};
        box-shadow: inset 0 0 0 1px ${colorTokens.stroke.hover};
      }

      &:active {
        background-color: ${colorTokens.background.active};
        box-shadow: inset 0 0 0 1px ${colorTokens.stroke.hover};
      }

      &:focus {
        box-shadow: inset 0 0 0 1px ${colorTokens.stroke.default}, ${shadow.focus};
      }

      ${(disabled || loading) &&
      css`
        color: ${colorTokens.text.disable};
        box-shadow: inset 0 0 0 1px ${colorTokens.action.outline.disable};
      `}
    `}

    ${variant === 'danger' &&
    css`
      background-color: ${colorTokens.background.status.errorFail};
      color: ${colorTokens.text.error};

      &:hover {
        background-color: ${colorTokens.background.status.errorFail};
      }

      &:active {
        background-color: ${colorTokens.background.status.errorFail};
      }

      &:focus {
        box-shadow: ${shadow.focus};
      }

      ${(disabled || loading) &&
      css`
        background-color: ${colorTokens.action.primary.disable};
        color: ${colorTokens.text.disable};
      `}
    `}

    ${variant === 'text' &&
    css`
      background-color: transparent;
      color: ${colorTokens.text.subdued};
      padding: ${spacing[4]} ${spacing[8]};

      svg {
        color: ${colorTokens.icon.default};
      }

      &:hover {
        text-decoration: underline;
        color: ${colorTokens.text.primary};

        svg {
          color: ${colorTokens.icon.brand};
        }
      }

      &:active {
        color: ${colorTokens.text.title};
      }

      &:focus {
        color: ${colorTokens.text.title};
        box-shadow: ${shadow.focus};
        svg {
          color: ${colorTokens.icon.brand};
        }
      }

      ${(disabled || loading) &&
      css`
        color: ${colorTokens.text.disable};

        svg {
          color: ${colorTokens.icon.disable};
        }
      `}
    `}

    ${(disabled || loading) &&
    css`
      pointer-events: none;
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
    ${iconPosition === 'right' &&
    css`
      margin-right: 0;
      margin-left: ${spacing[6]};
    `}
  `,
  spinner: css`
    position: absolute;
    visibility: visible;
    display: flex;
    top: 50%;
    left: 50%;
    transform: translateX(-50%) translateY(-50%);
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
      variant = 'primary',
      size = 'medium',
      icon,
      iconPosition = 'left',
      loading = false,
      disabled = false,
      tabIndex,
      onClick,
      buttonCss,
      buttonContentCss,
    },
    ref
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
          {icon && iconPosition === 'left' && <span css={styles.buttonIcon(iconPosition)}>{icon}</span>}
          {children}
          {icon && iconPosition === 'right' && <span css={styles.buttonIcon(iconPosition)}>{icon}</span>}
        </span>
      </button>
    );
  }
);

export default Button;
