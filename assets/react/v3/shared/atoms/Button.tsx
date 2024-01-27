import SVGIcon from "@Atoms/SVGIcon";
import {
  borderRadius,
  colorPalate,
  colorPalateTutor,
  fontSize,
  fontWeight,
  lineHeight,
  shadow,
  spacing,
  zIndex,
} from "@Config/styles";
import { typography } from "@Config/typography";
import { css, keyframes, SerializedStyles } from "@emotion/react";
import { styleUtils } from "@Utils/style-utils";
import React, { ReactNode } from "react";

export enum ButtonVariant {
  primary = "primary",
  secondary = "secondary",
  outlined = "outlined",
  tertiary = "tertiary",
  danger = "danger",
  text = "text",
}

export enum ButtonSize {
  large = "large",
  medium = "medium",
  small = "small",
}

export enum ButtonIconPosition {
  left = "left",
  right = "right",
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

    ${size === ButtonSize.large &&
      css`
        padding: ${spacing[12]} ${spacing[32]};
      `}

    ${size === ButtonSize.small &&
      css`
        font-size: ${fontSize[13]};
        line-height: ${lineHeight[20]};
        padding: ${spacing[6]} ${spacing[16]};
      `}
    
    ${variant === ButtonVariant.primary &&
      css`
        background-color: ${colorPalateTutor.action.primary.default};
        color: ${colorPalateTutor.text.white};

        &:hover {
          background-color: ${colorPalateTutor.action.primary.hover};
        }

        &:active {
          background-color: ${colorPalateTutor.action.primary.active};
        }

        &:focus {
          box-shadow: ${shadow.focus};
        }

        ${(disabled || loading) &&
          css`
            background-color: ${colorPalateTutor.action.primary.disable};
            color: ${colorPalateTutor.text.disable};
          `}
      `}

    ${variant === ButtonVariant.secondary &&
      css`
        background-color: ${colorPalateTutor.action.secondary.default};
        color: ${colorPalateTutor.text.brand};

        &:hover {
          background-color: ${colorPalateTutor.action.secondary.hover};
        }

        &:active {
          background-color: ${colorPalateTutor.action.secondary.active};
        }

        &:focus {
          box-shadow: ${shadow.focus};
        }

        ${(disabled || loading) &&
          css`
            background-color: ${colorPalateTutor.action.primary.disable};
            color: ${colorPalateTutor.text.disable};
          `}
      `}

    ${variant === ButtonVariant.outlined &&
      css`
        background-color: ${colorPalateTutor.action.outline.default};
        color: ${colorPalateTutor.text.brand};
        box-shadow: inset 0 0 0 1px ${colorPalateTutor.stroke.brand};

        &:hover {
          background-color: ${colorPalateTutor.action.outline.hover};
        }

        &:active {
          background-color: ${colorPalateTutor.action.outline.active};
        }

        &:focus {
          box-shadow: inset 0 0 0 1px ${colorPalateTutor.stroke.brand},
            ${shadow.focus};
        }

        ${(disabled || loading) &&
          css`
            color: ${colorPalateTutor.text.disable};
            box-shadow: inset 0 0 0 1px
              ${colorPalateTutor.action.outline.disable};
          `}
      `}

    ${variant === ButtonVariant.tertiary &&
      css`
        background-color: ${colorPalateTutor.background.white};
        color: ${colorPalateTutor.text.subdued};
        box-shadow: inset 0 0 0 1px ${colorPalateTutor.stroke.default};

        &:hover {
          background-color: ${colorPalateTutor.background.hover};
          box-shadow: inset 0 0 0 1px ${colorPalateTutor.stroke.hover};
        }

        &:active {
          background-color: ${colorPalateTutor.background.active};
          box-shadow: inset 0 0 0 1px ${colorPalateTutor.stroke.hover};
        }

        &:focus {
          box-shadow: inset 0 0 0 1px ${colorPalateTutor.stroke.default},
            ${shadow.focus};
        }

        ${(disabled || loading) &&
          css`
            color: ${colorPalateTutor.text.disable};
            box-shadow: inset 0 0 0 1px
              ${colorPalateTutor.action.outline.disable};
          `}
      `}

    ${variant === ButtonVariant.danger &&
      css`
        background-color: ${colorPalateTutor.background.status.errorFail};
        color: ${colorPalateTutor.text.error};

        &:hover {
          background-color: ${colorPalateTutor.background.status.errorFail};
        }

        &:active {
          background-color: ${colorPalateTutor.background.status.errorFail};
        }

        &:focus {
          box-shadow: ${shadow.focus};
        }

        ${(disabled || loading) &&
          css`
            background-color: ${colorPalateTutor.action.primary.disable};
            color: ${colorPalateTutor.text.disable};
          `}
      `}

    ${variant === ButtonVariant.text &&
      css`
        background-color: transparent;
        color: ${colorPalateTutor.text.title};

        &:hover {
          text-decoration: underline;
        }

        &:active {
          color: ${colorPalateTutor.text.primary};
        }

        &:focus {
          color: ${colorPalateTutor.text.primary};
          box-shadow: ${shadow.focus};
        }

        svg {
          color: ${colorPalateTutor.icon.default};
        }

        ${(disabled || loading) &&
          css`
            color: ${colorPalateTutor.text.disable};
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
  type?: "submit" | "button";
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
      type = "button",
      children,
      variant = ButtonVariant.primary,
      size = ButtonSize.medium,
      icon,
      iconPosition = ButtonIconPosition.left,
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
        css={[
          styles.button(variant, size, iconPosition, loading, disabled),
          buttonCss,
        ]}
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
  }
);

export default Button;
