import { css, keyframes, type SerializedStyles } from '@emotion/react';
import React, { type ReactNode } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { createVariation, type VariantProps } from '@TutorShared/utils/create-variation';
import { styleUtils } from '@TutorShared/utils/style-utils';

export type ButtonVariant = 'primary' | 'secondary' | 'tertiary' | 'danger' | 'text' | 'WP';
export type ButtonSize = 'large' | 'regular' | 'small';
export type ButtonIconPosition = 'left' | 'right';

// Base props that are common to both button and anchor elements
interface BaseButtonProps extends VariantProps<typeof buttonVariants> {
  variant?: ButtonVariant;
  isOutlined?: boolean;
  size?: ButtonSize;
  iconPosition?: ButtonIconPosition;
  disabled?: boolean;
  loading?: boolean;
  tabIndex?: number;
  buttonCss?: SerializedStyles;
  buttonContentCss?: SerializedStyles;
  id?: string;
}

// Props for regular buttons with children
interface RegularButtonProps extends BaseButtonProps {
  isIconOnly?: false;
  children: ReactNode;
  icon?: React.ReactNode;
}

// Props for icon-only buttons
interface IconOnlyButtonProps extends BaseButtonProps {
  isIconOnly: true;
  children?: never; // TypeScript will error if children is provided
  iconPosition?: never; // Icon position is not applicable for icon-only buttons
  icon: React.ReactNode; // Icon is required
  'aria-label': string; // aria-label is required for accessibility
}

// Button element specific props
interface ButtonElementProps extends React.HTMLAttributes<HTMLButtonElement> {
  as?: 'button';
  type?: 'submit' | 'button' | 'reset';
  onClick?: React.MouseEventHandler<HTMLButtonElement>;
  form?: string;
  name?: string;
  value?: string;
  // Explicitly forbid anchor-specific props
  href?: never;
  target?: never;
  rel?: never;
  download?: never;
}

// Anchor element specific props
interface AnchorElementProps extends React.HTMLAttributes<HTMLAnchorElement> {
  as: 'a';
  href: string;
  target?: '_blank' | '_self' | '_parent' | '_top';
  rel?: string;
  download?: string;
  onClick?: React.MouseEventHandler<HTMLAnchorElement>;
  // Explicitly forbid button-specific props
  type?: never;
  form?: never;
  name?: never;
  value?: never;
}

// Combine content props with element props
type ButtonWithRegularContent = (RegularButtonProps & ButtonElementProps) | (RegularButtonProps & AnchorElementProps);
type ButtonWithIconOnlyContent =
  | (IconOnlyButtonProps & ButtonElementProps)
  | (IconOnlyButtonProps & AnchorElementProps);

export type ButtonProps = ButtonWithRegularContent | ButtonWithIconOnlyContent;

const Button = React.forwardRef<HTMLButtonElement | HTMLAnchorElement, ButtonProps>((props, ref) => {
  const {
    variant = 'primary',
    isOutlined = false,
    size = 'regular',
    loading = false,
    children,
    disabled = false,
    icon,
    iconPosition = 'left',
    buttonCss,
    buttonContentCss,
    as = 'button',
    tabIndex,
    isIconOnly = false,
    ...restProps
  } = props;

  const baseStyles = [
    buttonVariants({
      variant,
      outlined: isOutlined ? variant : 'none',
      size,
      isLoading: loading ? 'true' : 'false',
      iconOnly: isIconOnly ? 'true' : 'false',
    }),
    buttonCss,
  ];

  const content = (
    <>
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
    </>
  );

  if (as === 'a') {
    const { href, target, rel, download, onClick, ...anchorProps } = restProps as Omit<
      AnchorElementProps,
      keyof BaseButtonProps | 'as'
    >;

    // Auto-add security attributes for external links
    const isExternalLink = typeof href === 'string' && (href.startsWith('http') || href.startsWith('//'));
    const finalRel = target === '_blank' && isExternalLink ? `${rel ? `${rel} ` : ''}noopener noreferrer` : rel;

    return (
      <a
        ref={ref as React.ForwardedRef<HTMLAnchorElement>}
        css={baseStyles}
        href={disabled || loading ? undefined : href}
        target={disabled || loading ? undefined : target}
        rel={finalRel}
        download={disabled || loading ? undefined : download}
        tabIndex={disabled || loading ? -1 : tabIndex}
        aria-disabled={disabled || loading}
        onClick={disabled || loading ? (e) => e.preventDefault() : onClick}
        role="button"
        data-size={size}
        {...anchorProps}
      >
        {content}
      </a>
    );
  }

  const {
    type = 'button',
    onClick,
    form,
    name,
    value,
    ...buttonProps
  } = restProps as Omit<ButtonElementProps, keyof BaseButtonProps | 'as'>;

  return (
    <button
      ref={ref as React.ForwardedRef<HTMLButtonElement>}
      type={type}
      css={baseStyles}
      disabled={disabled || loading}
      tabIndex={tabIndex}
      onClick={onClick}
      form={form}
      name={name}
      value={value}
      data-size={size}
      {...buttonProps}
    >
      {content}
    </button>
  );
});

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
    &:disabled,
    &[aria-disabled='true'] {
      background-color: ${colorTokens.action.primary.disable};
      color: ${colorTokens.text.disable};
      svg {
        color: ${colorTokens.icon.disable.default};
      }
    }
  `,
  outlined: css`
    &:disabled,
    &[aria-disabled='true'] {
      background-color: transparent;
      border: none;
      box-shadow: inset 0px 0px 0px 1px ${colorTokens.action.outline.disable};
      color: ${colorTokens.text.disable};
      svg {
        color: ${colorTokens.icon.disable.default};
      }
    }
  `,
  text: css`
    &:disabled,
    &[aria-disabled='true'] {
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

    &:disabled,
    &[aria-disabled='true'] {
      cursor: not-allowed;
    }

    &:not(:disabled):not([aria-disabled='true']) {
      &:focus {
        box-shadow: ${shadow.focus};
      }

      &:focus-visible {
        box-shadow: none;
        outline: 2px solid ${colorTokens.stroke.brand};
        outline-offset: 1px;
      }
    }
  `,
  variant: {
    primary: css`
      background-color: ${colorTokens.action.primary.default};
      ${disabledStyles.notOutlined};

      &:not(:disabled):not([aria-disabled='true']) {
        &:hover,
        &:focus {
          color: ${colorTokens.text.white};
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

      &:not(:disabled):not([aria-disabled='true']) {
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
      box-shadow: inset 0px 0px 0px 1px ${colorTokens.stroke.default};
      color: ${colorTokens.text.subdued};
      svg {
        color: ${colorTokens.icon.hints};
      }
      ${disabledStyles.outlined};

      &:not(:disabled):not([aria-disabled='true']) {
        &:hover,
        &:focus {
          background-color: ${colorTokens.background.hover};
          box-shadow: inset 0px 0px 0px 1px ${colorTokens.stroke.hover};
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

      &:not(:disabled):not([aria-disabled='true']) {
        &:hover,
        &:focus,
        &:active {
          background-color: ${colorTokens.background.status.errorFail};
          color: ${colorTokens.text.error};
        }
      }
    `,
    WP: css`
      background-color: ${colorTokens.action.primary.wp};
      ${disabledStyles.notOutlined};

      &:not(:disabled):not([aria-disabled='true']) {
        &:hover,
        &:focus {
          background-color: ${colorTokens.action.primary.wp_hover};
          color: ${colorTokens.text.white};
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

      &:not(:disabled):not([aria-disabled='true']) {
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
      box-shadow: inset 0px 0px 0px 1px ${colorTokens.stroke.brand};
      color: ${colorTokens.text.brand};

      svg {
        color: ${colorTokens.icon.brand};
      }
      ${disabledStyles.outlined};

      &:not(:disabled):not([aria-disabled='true']) {
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
      box-shadow: inset 0px 0px 0px 1px ${colorTokens.stroke.brand};
      color: ${colorTokens.text.brand};
      svg {
        color: ${colorTokens.icon.brand};
      }
      ${disabledStyles.outlined};

      &:not(:disabled):not([aria-disabled='true']) {
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

      &:not(:disabled):not([aria-disabled='true']) {
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

      &:not(:disabled):not([aria-disabled='true']) {
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

      &:not(:disabled):not([aria-disabled='true']) {
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
      min-height: 40px;
    `,
    large: css`
      padding: ${spacing[12]} ${spacing[40]};
      ${typography.body('medium')};
      color: ${colorTokens.text.white};
      min-height: 48px;
    `,
    small: css`
      padding: ${spacing[6]} ${spacing[16]};
      ${typography.small('medium')};
      color: ${colorTokens.text.white};
      min-height: 32px;
    `,
  },
  isIconOnly: {
    true: css`
      aspect-ratio: 1 / 1;
      &[data-size='regular'] {
        padding: ${spacing[8]};
        width: 40px;
      }
      &[data-size='large'] {
        padding: ${spacing[12]};
        width: 48px;
      }
      &[data-size='small'] {
        padding: ${spacing[6]};
        width: 32px;
      }
    `,
    false: css``,
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
  buttonContent: ({
    loading,
    disabled,
    isIconOnly,
  }: {
    loading: boolean;
    disabled: boolean;
    isIconOnly?: boolean;
  }) => css`
    ${styleUtils.display.flex()};
    align-items: center;
    ${isIconOnly && 'justify-content: center;'}

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
      iconOnly: {
        true: styles.isIconOnly.true,
        false: styles.isIconOnly.false,
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
      iconOnly: 'false',
    },
  },
  styles.base,
);
