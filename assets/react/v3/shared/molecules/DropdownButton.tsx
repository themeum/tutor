import { type SerializedStyles, css, keyframes } from '@emotion/react';
import React, { useRef, useState, type MouseEvent, type ReactNode } from 'react';

import type { ButtonIconPosition, ButtonSize, ButtonVariant } from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, fontSize, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { AnimationType } from '@Hooks/useAnimation';
import { styleUtils } from '@Utils/style-utils';

import { typography } from '@Config/typography';
import Popover from './Popover';

interface DropdownOptionProps {
  type?: 'button' | 'submit';
  text: string | ReactNode;
  disabled?: boolean;
  onClick?: React.MouseEventHandler<HTMLButtonElement>;
  buttonContentCss?: SerializedStyles;
  isDanger?: boolean;
}

export const DropdownItem = ({
  text,
  type = 'button',
  disabled = false,
  onClick,
  buttonContentCss,
  isDanger = false,
}: DropdownOptionProps) => {
  return (
    <button
      type={type}
      css={styles.dropdownOption({
        disabled,
        isDanger,
      })}
      onClick={onClick}
    >
      <span css={[styles.dropdownOptionContent, buttonContentCss]}>{text}</span>
    </button>
  );
};

interface DropdownButtonProps {
  text: string | ReactNode;
  children: ReactNode;
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
  dropdownMaxWidth?: string;
  disabledDropdown?: boolean;
}

const DropdownButton = ({
  type = 'button',
  text,
  children,
  variant = 'primary',
  size = 'regular',
  icon,
  iconPosition = 'left',
  loading = false,
  disabled = false,
  tabIndex = -1,
  onClick,
  buttonCss,
  buttonContentCss,
  dropdownMaxWidth = '140px',
  disabledDropdown = false,
}: DropdownButtonProps) => {
  const dropdownTriggerRef = useRef<HTMLButtonElement>(null);
  const [isOpen, setIsOpen] = useState(false);

  return (
    <>
      <div css={styles.wrapper}>
        <button
          type={type}
          css={[
            styles.button({
              variant,
              size,
              loading,
              disabled,
            }),
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
          <span
            css={[
              styles.buttonContent({
                loading,
                disabled,
              }),
              buttonContentCss,
            ]}
          >
            {icon && iconPosition === 'left' && (
              <span
                css={styles.buttonIcon({
                  iconPosition,
                })}
              >
                {icon}
              </span>
            )}
            {text}
            {icon && iconPosition === 'right' && (
              <span
                css={styles.buttonIcon({
                  iconPosition,
                })}
              >
                {icon}
              </span>
            )}
          </span>
        </button>
        <button
          ref={dropdownTriggerRef}
          type="button"
          disabled={disabled || disabledDropdown}
          css={[
            styles.button({
              variant,
              size,
              loading: false,
              disabled: disabled || disabledDropdown,
            }),
            styles.dropdownButton({
              variant,
              disabled: disabled || disabledDropdown,
            }),
          ]}
          onClick={() => setIsOpen(!isOpen)}
        >
          <SVGIcon name="chevronDown" width={24} height={24} />
        </button>
      </div>
      <Popover
        gap={4}
        maxWidth={dropdownMaxWidth}
        arrow="top"
        triggerRef={dropdownTriggerRef}
        isOpen={isOpen}
        closePopover={() => setIsOpen(false)}
        animationType={AnimationType.slideUp}
        hideArrow
      >
        <div css={styles.dropdownWrapper}>
          {React.Children.map(children, (child) => {
            if (React.isValidElement(child)) {
              const childProps: DropdownOptionProps = {
                ...child.props,
                onClick: (event: MouseEvent<HTMLButtonElement>) => {
                  setIsOpen(false);
                  child.props?.onClick(event);
                },
              };
              return React.cloneElement(child, childProps);
            }

            return child;
          })}
        </div>
      </Popover>
    </>
  );
};

DropdownButton.Item = DropdownItem;
export default DropdownButton;

const spin = keyframes`
  0% {
    transform: rotate(0);
  }

  100% {
    transform: rotate(360deg);
  }
`;

const styles = {
  wrapper: css`
    ${styleUtils.display.inlineFlex()};
    align-items: center;
  `,
  button: ({
    variant,
    size,
    loading,
    disabled,
  }: {
    variant: ButtonVariant;
    size: ButtonSize;
    loading: boolean;
    disabled: boolean;
  }) => css`
    ${styleUtils.resetButton};
    ${typography.caption('medium')}
    display: inline-block;
    text-align: center;
    text-decoration: none;
    vertical-align: middle;
    cursor: pointer;
    user-select: none;
    background-color: transparent;
    border: 0;
    padding: ${spacing[8]} ${spacing[16]};
    border-radius: ${borderRadius[6]} 0 0 ${borderRadius[6]};
    z-index: ${zIndex.level};
    transition-property: box-shadow, background, opacity;
    transition-duration: 150ms;
    transition-timing-function: ease-in-out;
    position: relative;

    &:focus,
    &:active {
      z-index: ${zIndex.positive};
    }

    ${
      size === 'large' &&
      css`
      padding: ${spacing[12]} ${spacing[32]};
    `
    }

    ${
      size === 'small' &&
      css`
      font-size: ${fontSize[13]};
      line-height: ${lineHeight[20]};
      padding: ${spacing[6]} ${spacing[16]};
    `
    }
    
    ${
      variant === 'primary' &&
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

        ${
          (disabled || loading) &&
          css`
          background-color: ${colorTokens.action.primary.disable};
          color: ${colorTokens.text.disable};
        `
        }
      `
    }

    ${
      variant === 'secondary' &&
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

        ${
          (disabled || loading) &&
          css`
          background-color: ${colorTokens.action.primary.disable};
          color: ${colorTokens.text.disable};
        `
        }
      `
    }

    ${
      variant === 'secondary' &&
      css`
        background-color: ${colorTokens.action.outline.default};
        color: ${colorTokens.text.brand};
        box-shadow: 0 0 0 1px ${colorTokens.stroke.brand};

        &:hover {
          background-color: ${colorTokens.action.outline.hover};
        }

        &:active {
          background-color: ${colorTokens.action.outline.active};
        }

        &:focus {
          box-shadow: 0 0 0 1px ${colorTokens.stroke.brand}, ${shadow.focus};
        }

        ${
          (disabled || loading) &&
          css`
          color: ${colorTokens.text.disable};
          box-shadow: 0 0 0 1px ${colorTokens.action.outline.disable};
        `
        }
      `
    }

    ${
      variant === 'tertiary' &&
      css`
        background-color: ${colorTokens.background.white};
        color: ${colorTokens.text.subdued};
        box-shadow: 0 0 0 1px ${colorTokens.stroke.default};

        &:hover {
          background-color: ${colorTokens.background.hover};
          box-shadow: 0 0 0 1px ${colorTokens.stroke.hover};
          z-index: ${zIndex.positive};
        }

        &:active {
          background-color: ${colorTokens.background.active};
          box-shadow: 0 0 0 1px ${colorTokens.stroke.hover};
        }

        &:focus {
          box-shadow: 0 0 0 1px ${colorTokens.stroke.default}, ${shadow.focus};
          z-index: calc(${zIndex.positive} + 1);
        }

        ${
          (disabled || loading) &&
          css`
          color: ${colorTokens.text.disable};
          box-shadow: 0 0 0 1px ${colorTokens.action.outline.disable};
        `
        }
      `
    }

    ${
      variant === 'danger' &&
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

        ${
          (disabled || loading) &&
          css`
          background-color: ${colorTokens.action.primary.disable};
          color: ${colorTokens.text.disable};
        `
        }
      `
    }

    ${
      variant === 'text' &&
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

        ${
          (disabled || loading) &&
          css`
          color: ${colorTokens.text.disable};

          svg {
            color: ${colorTokens.icon.disable};
          }
        `
        }
    `
    }

    ${
      (disabled || loading) &&
      css`
        pointer-events: none;
      `
    }
  `,
  buttonContent: ({ loading, disabled }: { loading: boolean; disabled: boolean }) => css`
    display: flex;
    align-items: center;

    ${
      loading &&
      !disabled &&
      css`
        color: transparent;
      `
    }
  `,
  buttonIcon: ({ iconPosition }: { iconPosition: ButtonIconPosition }) => css`
    display: grid;
    place-items: center;
    margin-right: ${spacing[6]};
    ${
      iconPosition === 'right' &&
      css`
        margin-right: 0;
        margin-left: ${spacing[6]};
      `
    }
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
  dropdownButton: ({ variant, disabled }: { variant: ButtonVariant; disabled: boolean }) => css`
    ${styleUtils.flexCenter()}
    padding: ${spacing[8]};
    border-left: 1px solid transparent;
    border-radius: 0 ${borderRadius[6]} ${borderRadius[6]} 0;

    ${
      variant === 'primary' &&
      css`
        border-color: ${colorTokens.stroke.brand};

        :focus {
          border-color: transparent;
        }
      `
    }

    ${
      variant === 'danger' &&
      css`
        border-color: ${colorTokens.stroke.danger};
      `
    }

    ${
      disabled &&
      css`
        border-color: ${colorTokens.stroke.disable};
      `
    }
  `,
  dropdownWrapper: css`
    display: flex;
    flex-direction: column;
    padding-block: ${spacing[6]};
  `,
  dropdownOption: ({ disabled, isDanger }: { disabled: boolean; isDanger: boolean }) => css`
    ${styleUtils.resetButton};
    width: 100%;
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[20]};
    transition: background-color 0.3s ease-in-out;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    border: 2px solid transparent;

    ${
      isDanger &&
      css`
        color: ${colorTokens.text.error};
      `
    }

    :hover {
      background-color: ${colorTokens.background.hover};
      color: ${isDanger ? colorTokens.text.error : colorTokens.text.title};
    }

    :focus,
    :active {
      border-color: ${colorTokens.stroke.brand};
    }

    ${
      disabled &&
      css`
        pointer-events: none;
        color: ${colorTokens.text.disable};
      `
    }
  `,
  dropdownOptionContent: css`
    display: flex;
    align-items: center;
  `,
};
