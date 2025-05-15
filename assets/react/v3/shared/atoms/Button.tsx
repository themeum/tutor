import { type SerializedStyles, css, keyframes } from '@emotion/react';
import React, { type ReactNode } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { type VariantProps, createVariation } from '@TutorShared/utils/create-variation';
import { styleUtils } from '@TutorShared/utils/style-utils';

export type ButtonVariant = 'primary' | 'secondary' | 'tertiary' | 'danger' | 'text' | 'WP';
export type ButtonSize = 'large' | 'regular' | 'small';
export type ButtonIconPosition = 'left' | 'right';

interface ButtonProps extends React.HTMLAttributes<HTMLButtonElement>, VariantProps<typeof buttonVariants> {
  children?: ReactNode;
  variant?: ButtonVariant;
  isOutlined?: boolean;
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
      variant = 'primary',
      isOutlined = false,
      size = 'regular',
      loading = false,
      children,
      type = 'button',
      disabled = false,
      icon,
      iconPosition = 'left',
      buttonCss,
      buttonContentCss,
      onClick,
      tabIndex,
      ...props
    },
    ref,
  ) => (
    <button
      type={type}
      ref={ref}
      css={[
        buttonVariants({
          variant,
          outlined: isOutlined ? variant : 'none',
          size,
          isLoading: loading ? 'true' : 'false',
        }),
        buttonCss,
      ]}
      disabled={disabled || loading}
      onClick={onClick}
      tabIndex={tabIndex}
      {...props}
    >
      {loading && !disabled && (
        <span css={styles.spinner}>
          <SVGIcon name="spinner" width={18} height={18} />
        </span>
      )}
      <span css={[styles.buttonContent({ loading, disabled }), buttonContentCss]}>
        {icon && iconPosition === 'left' && (
          <span css={styles.buttonIcon({ iconPosition, loading, hasChildren: !!children })}>{icon}</span>
        )}
        {children}
        {icon && iconPosition === 'right' && (
          <span css={styles.buttonIcon({ iconPosition, loading, hasChildren: !!children })}>{icon}</span>
        )}
      </span>
    </button>
  ),
);

Button.displayName = 'Button';

export default Button;

const spin = keyframes`
  0% {
    transform: rotate(0);
  }

  100% {
    transform: rotate(360deg);
  }
`;

const disabledStyles = {
  notOutlined: css`
    &:disabled {
      background-color: ${colorTokens.action.primary.disable};
      color: ${colorTokens.text.disable};
      svg {
        color: ${colorTokens.icon.disable.default};
      }
    }
  `,
  outlined: css`
    &:disabled {
      background-color: transparent;
      border: none;
      outline: 1px solid ${colorTokens.action.outline.disable};
      color: ${colorTokens.text.disable};
      svg {
        color: ${colorTokens.icon.disable.default};
      }
    }
  `,
  text: css`
    &:disabled {
      color: ${colorTokens.text.disable};
      svg {
        color: ${colorTokens.icon.disable.default};
      }
    }
  `,
};

const styles = {
  base: css`
    ${styleUtils.resetButton};
    ${styleUtils.display.inlineFlex()};
    justify-content: center;
    align-items: center;
    ${typography.caption('medium')};
    ${styleUtils.text.align.center};
    color: ${colorTokens.text.white};
    text-decoration: none;
    vertical-align: middle;
    cursor: pointer;
    user-select: none;
    background-color: transparent;
    border: 0;
    padding: ${spacing[8]} ${spacing[32]};
    border-radius: ${borderRadius[6]};
    z-index: ${zIndex.level};
    transition: all 150ms ease-in-out;
    position: relative;
    svg {
      color: ${colorTokens.icon.white};
    }

    &:disabled {
      cursor: not-allowed;
    }

    &:focus {
      box-shadow: ${shadow.focus};
    }

    &:focus-visible {
      box-shadow: none;
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
    }
  `,
  variant: {
    primary: css`
      background-color: ${colorTokens.action.primary.default};
      ${disabledStyles.notOutlined};

      &:not(:disabled) {
        &:hover,
        &:focus {
          background-color: ${colorTokens.action.primary.hover};
        }
        &:active {
          background-color: ${colorTokens.action.primary.active};
          color: ${colorTokens.text.white};
          svg {
            color: ${colorTokens.icon.white};
          }
        }
      }
    `,
    secondary: css`
      background-color: ${colorTokens.action.secondary.default};
      color: ${colorTokens.text.brand};
      svg {
        color: ${colorTokens.icon.brand};
      }
      ${disabledStyles.notOutlined};

      &:not(:disabled) {
        &:hover,
        &:focus {
          background-color: ${colorTokens.action.secondary.hover};
          color: ${colorTokens.text.brand};
        }
        &:active {
          background-color: ${colorTokens.action.secondary.active};
          color: ${colorTokens.text.brand};
        }
      }
    `,
    tertiary: css`
      outline: 1px solid ${colorTokens.stroke.default};
      color: ${colorTokens.text.subdued};
      svg {
        color: ${colorTokens.icon.hints};
      }
      ${disabledStyles.outlined};

      &:not(:disabled) {
        &:hover,
        &:focus {
          background-color: ${colorTokens.background.hover};
          outline: 1px solid ${colorTokens.stroke.hover};
          color: ${colorTokens.text.title};

          svg {
            color: ${colorTokens.icon.brand};
          }
        }

        &:active {
          background-color: ${colorTokens.background.active};
          svg {
            color: ${colorTokens.icon.hints};
          }
        }
      }
    `,
    danger: css`
      background-color: ${colorTokens.background.status.errorFail};
      color: ${colorTokens.text.error};
      svg {
        color: ${colorTokens.icon.error};
      }
      ${disabledStyles.notOutlined};

      &:not(:disabled) {
        &:hover,
        &:focus,
        &:active {
          background-color: ${colorTokens.background.status.errorFail};
        }
      }
    `,
    WP: css`
      background-color: ${colorTokens.action.primary.wp};
      ${disabledStyles.notOutlined};

      &:not(:disabled) {
        &:hover,
        &:focus {
          background-color: ${colorTokens.action.primary.wp_hover};
        }
        &:active {
          background-color: ${colorTokens.action.primary.wp};
        }
      }
    `,
    text: css`
      background-color: transparent;
      color: ${colorTokens.text.subdued};
      padding: ${spacing[8]};
      svg {
        color: ${colorTokens.icon.hints};
      }
      ${disabledStyles.text};

      &:not(:disabled) {
        &:hover,
        &:focus {
          background-color: transparent;
          color: ${colorTokens.text.brand};

          svg {
            color: ${colorTokens.icon.brand};
          }
        }
        &:active {
          background-color: transparent;
          color: ${colorTokens.text.subdued};
        }
      }
    `,
  },
  outlined: {
    primary: css`
      background-color: transparent;
      outline: 1px solid ${colorTokens.stroke.brand};
      color: ${colorTokens.text.brand};

      svg {
        color: ${colorTokens.icon.brand};
      }
      ${disabledStyles.outlined};

      &:not(:disabled) {
        &:hover,
        &:focus {
          color: ${colorTokens.text.white};

          svg {
            color: ${colorTokens.icon.white};
          }
        }
      }
    `,
    secondary: css`
      background-color: transparent;
      outline: 1px solid ${colorTokens.stroke.brand};
      color: ${colorTokens.text.brand};
      svg {
        color: ${colorTokens.icon.brand};
      }
      ${disabledStyles.outlined};

      &:not(:disabled) {
        &:hover,
        &:focus {
          background-color: ${colorTokens.action.secondary.hover};
        }
      }
    `,
    tertiary: css`
      background-color: transparent;
      ${disabledStyles.outlined};
    `,
    danger: css`
      background-color: transparent;
      border: 1px solid ${colorTokens.stroke.danger};
      ${disabledStyles.outlined};

      &:not(:disabled) {
        &:hover,
        &:focus {
          background-color: ${colorTokens.background.status.errorFail};
        }
      }
    `,
    WP: css`
      background-color: transparent;
      border: 1px solid ${colorTokens.action.primary.wp};
      color: ${colorTokens.action.primary.wp};
      svg {
        color: ${colorTokens.icon.wp};
      }
      ${disabledStyles.outlined};

      &:not(:disabled) {
        &:hover,
        &:focus {
          background-color: ${colorTokens.action.primary.wp_hover};
          color: ${colorTokens.text.white};

          svg {
            color: ${colorTokens.icon.white};
          }
        }
      }
    `,
    text: css`
      background-color: transparent;
      border: none;
      color: ${colorTokens.text.primary};
      ${disabledStyles.text};

      &:not(:disabled) {
        &:hover,
        &:focus {
          color: ${colorTokens.text.brand};
        }
      }
    `,
    none: css``,
  },
  size: {
    regular: css`
      padding: ${spacing[8]} ${spacing[32]};
      ${typography.caption('medium')};
      color: ${colorTokens.text.white};
    `,
    large: css`
      padding: ${spacing[12]} ${spacing[40]};
      ${typography.body('medium')};
      color: ${colorTokens.text.white};
    `,
    small: css`
      padding: ${spacing[6]} ${spacing[16]};
      ${typography.small('medium')};
      color: ${colorTokens.text.white};
    `,
  },
  isLoading: {
    true: css`
      opacity: 0.8;
      cursor: wait;
    `,
    false: css``,
  },
  iconWrapper: {
    left: css`
      order: -1;
    `,
    right: css`
      order: 1;
    `,
  },
  buttonContent: ({ loading, disabled }: { loading: boolean; disabled: boolean }) => css`
    ${styleUtils.display.flex()};
    align-items: center;

    ${loading &&
    !disabled &&
    css`
      color: transparent;
    `}
  `,
  buttonIcon: ({
    iconPosition,
    loading,
    hasChildren = true,
  }: {
    iconPosition: ButtonIconPosition;
    loading: boolean;
    hasChildren: boolean;
  }) => css`
    display: grid;
    place-items: center;
    margin-right: ${spacing[4]};
    ${iconPosition === 'right' &&
    css`
      margin-right: 0;
      margin-left: ${spacing[4]};
    `}

    ${loading &&
    css`
      opacity: 0;
    `}

    ${!hasChildren &&
    css`
      margin-inline: 0;
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
      animation: ${spin} 1s linear infinite;
    }
  `,
};

const buttonVariants = createVariation(
  {
    variants: {
      size: {
        regular: styles.size.regular,
        large: styles.size.large,
        small: styles.size.small,
      },
      isLoading: {
        true: styles.isLoading.true,
        false: styles.isLoading.false,
      },
      variant: {
        primary: styles.variant.primary,
        secondary: styles.variant.secondary,
        tertiary: styles.variant.tertiary,
        danger: styles.variant.danger,
        WP: styles.variant.WP,
        text: styles.variant.text,
      },
      outlined: {
        primary: styles.outlined.primary,
        secondary: styles.outlined.secondary,
        tertiary: styles.outlined.tertiary,
        danger: styles.outlined.danger,
        WP: styles.outlined.WP,
        text: styles.outlined.text,
        none: styles.outlined.none,
      },
    },
    defaultVariants: {
      variant: 'primary',
      outlined: 'none',
      size: 'regular',
      isLoading: 'false',
    },
  },
  styles.base,
);
