import { css, keyframes, type SerializedStyles } from '@emotion/react';
import React, { useRef, useState, type MouseEvent, type ReactNode } from 'react';

import type { ButtonIconPosition, ButtonSize, ButtonVariant } from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { borderRadius, colorTokens, fontSize, lineHeight, shadow, spacing, zIndex } from '@TutorShared/config/styles';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { styleUtils } from '@TutorShared/utils/style-utils';

import { typography } from '@TutorShared/config/typography';
import { POPOVER_PLACEMENTS, type PopoverPlacement } from '@TutorShared/hooks/usePortalPopover';
import Popover from './Popover';

interface DropdownOptionProps {
  type?: 'button' | 'submit';
  text: string | ReactNode;
  disabled?: boolean;
  onClick?: React.MouseEventHandler<HTMLButtonElement>;
  buttonContentCss?: SerializedStyles;
  isDanger?: boolean;
  icon?: React.ReactNode;
}

export const DropdownItem = ({
  text,
  type = 'button',
  disabled = false,
  onClick,
  buttonContentCss,
  isDanger = false,
  icon,
  ...props
}: DropdownOptionProps) => {
  return (
    <button
      type={type}
      css={styles.dropdownOption({
        disabled,
        isDanger,
      })}
      disabled={disabled}
      onClick={onClick}
      {...props}
    >
      {icon && <>{icon}</>}
      <span css={[styles.dropdownOptionContent, buttonContentCss]}>{text}</span>
    </button>
  );
};

interface DropdownButtonProps {
  text: string | ReactNode;
  children: ReactNode;
  variant?: ButtonVariant;
  placement?: PopoverPlacement;
  animationType?: AnimationType;
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
  placement = POPOVER_PLACEMENTS.BOTTOM_RIGHT,
  animationType = AnimationType.slideUp,
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
  ...props
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
          disabled={disabled || loading}
          {...props}
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
          data-cy="dropdown-trigger"
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
              size,
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
        placement={placement}
        triggerRef={dropdownTriggerRef}
        isOpen={isOpen}
        closePopover={() => setIsOpen(false)}
        animationType={animationType}
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
    border-radius: ${borderRadius[6]};

    :focus-within {
      box-shadow: ${shadow.focus};
    }
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
    color: ${colorTokens.text.primary};
    border: 0;
    padding: ${spacing[8]} ${spacing[16]};
    border-radius: ${borderRadius[6]} 0 0 ${borderRadius[6]};
    z-index: ${zIndex.level};
    transition-property: box-shadow, background-color, opacity;
    transition-duration: 150ms;
    transition-timing-function: ease-in-out;
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

      &:not(:disabled) {
        &:hover,
        &:focus {
          background-color: ${colorTokens.action.primary.hover};
          color: ${colorTokens.text.white};
        }

        &:active {
          background-color: ${colorTokens.action.primary.active};
          color: ${colorTokens.text.white};
        }
      }

      ${(disabled || loading) &&
      css`
        background-color: ${colorTokens.action.primary.disable};
        color: ${colorTokens.text.disable};

        &:hover,
        &:focus,
        &:active {
          background-color: ${colorTokens.action.primary.disable};
          color: ${colorTokens.text.disable};
        }
      `}
    `}

    ${variant === 'secondary' &&
    css`
      background-color: ${colorTokens.action.secondary.default};
      color: ${colorTokens.text.brand};

      &:hover:not(:disabled) {
        background-color: ${colorTokens.action.secondary.hover};
      }

      &:active:not(:disabled) {
        background-color: ${colorTokens.action.secondary.active};
      }

      ${(disabled || loading) &&
      css`
        background-color: ${colorTokens.action.primary.disable};
        color: ${colorTokens.text.disable};
      `}
    `}

    ${variant === 'secondary' &&
    css`
      background-color: ${colorTokens.action.outline.default};
      color: ${colorTokens.text.brand};
      box-shadow: 0 0 0 1px ${colorTokens.stroke.brand};

      &:hover:not(:disabled) {
        background-color: ${colorTokens.action.outline.hover};
      }

      &:active:not(:disabled) {
        background-color: ${colorTokens.action.outline.active};
      }

      ${(disabled || loading) &&
      css`
        color: ${colorTokens.text.disable};
        box-shadow: 0 0 0 1px ${colorTokens.action.outline.disable};
      `}
    `}

    ${variant === 'tertiary' &&
    css`
      background-color: ${colorTokens.background.white};
      color: ${colorTokens.text.subdued};
      box-shadow: 0 0 0 1px ${colorTokens.stroke.default};

      &:hover:not(:disabled) {
        background-color: ${colorTokens.background.hover};
        box-shadow: 0 0 0 1px ${colorTokens.stroke.hover};
        z-index: ${zIndex.positive};
      }

      &:active:not(:disabled) {
        background-color: ${colorTokens.background.active};
        box-shadow: 0 0 0 1px ${colorTokens.stroke.hover};
      }

      ${(disabled || loading) &&
      css`
        color: ${colorTokens.text.disable};
        box-shadow: 0 0 0 1px ${colorTokens.action.outline.disable};
      `}
    `}

    ${variant === 'danger' &&
    css`
      background-color: ${colorTokens.background.status.errorFail};
      color: ${colorTokens.text.error};

      &:hover:not(:disabled) {
        background-color: ${colorTokens.background.status.errorFail};
      }

      &:active:not(:disabled) {
        background-color: ${colorTokens.background.status.errorFail};
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

      &:hover:not(:disabled) {
        text-decoration: underline;
        color: ${colorTokens.text.primary};

        svg {
          color: ${colorTokens.icon.brand};
        }
      }

      &:active:not(:disabled) {
        color: ${colorTokens.text.title};
      }

      &:focus:not(:disabled) {
        color: ${colorTokens.text.title};
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

    :disabled {
      cursor: not-allowed;
    }
  `,
  buttonContent: ({ loading, disabled }: { loading: boolean; disabled: boolean }) => css`
    display: flex;
    align-items: center;

    ${loading &&
    !disabled &&
    css`
      color: transparent;
    `}
  `,
  buttonIcon: ({ iconPosition }: { iconPosition: ButtonIconPosition }) => css`
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
  dropdownButton: ({ variant, size, disabled }: { variant: ButtonVariant; size: ButtonSize; disabled: boolean }) => css`
    ${styleUtils.flexCenter()}
    padding-inline: ${spacing[8]};
    border-left: 1px solid transparent;
    border-radius: 0 ${borderRadius[6]} ${borderRadius[6]} 0;

    svg {
      width: 24px;
      height: 24px;
    }

    ${variant === 'primary' &&
    css`
      border-color: ${colorTokens.stroke.brand};
    `}

    ${variant === 'danger' &&
    css`
      border-color: ${colorTokens.stroke.danger};
    `}

    ${disabled &&
    css`
      border-color: ${colorTokens.stroke.disable};
    `}

    ${size === 'large' &&
    css`
      padding-inline: ${spacing[12]};

      svg {
        width: 30px;
        height: 30px;
      }
    `}

    ${size === 'small' &&
    css`
      padding-inline: ${spacing[6]};

      svg {
        width: 20px;
        height: 20px;
      }
    `}
  `,
  dropdownWrapper: css`
    display: flex;
    flex-direction: column;
    padding-block: ${spacing[6]};
  `,
  dropdownOption: ({ disabled, isDanger }: { disabled: boolean; isDanger: boolean }) => css`
    ${styleUtils.resetButton};
    ${typography.body()};
    color: ${colorTokens.text.primary};
    width: 100%;
    padding: ${spacing[6]} ${spacing[16]} ${spacing[6]} ${spacing[20]};
    transition: background-color 0.3s ease-in-out;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    outline: 2px solid transparent;
    outline-offset: -2px;

    ${isDanger &&
    css`
      color: ${colorTokens.text.error};
    `}

    :hover {
      background-color: ${colorTokens.background.hover};
      color: ${isDanger ? colorTokens.text.error : colorTokens.text.title};
    }

    :focus,
    :active {
      outline-color: ${colorTokens.stroke.brand};
    }

    ${disabled &&
    css`
      pointer-events: none;
      color: ${colorTokens.text.disable};
    `}

    svg:first-of-type {
      color: ${colorTokens.icon.default};
    }
  `,
  dropdownOptionContent: css`
    display: flex;
    align-items: center;

    svg {
      flex-shrink: 0;
    }
  `,
};
