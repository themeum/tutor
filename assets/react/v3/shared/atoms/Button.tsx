import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import { type SerializedStyles, css, keyframes } from '@emotion/react';
import React, { type ReactNode } from 'react';

export type ButtonVariant = 'primary' | 'secondary' | 'tertiary' | 'danger' | 'text' | 'WP';
export type ButtonSize = 'large' | 'regular' | 'small';
export type ButtonIconPosition = 'left' | 'right';

interface ButtonProps {
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
      type = 'button',
      children,
      variant = 'primary',
      isOutlined = false,
      size = 'regular',
      icon,
      iconPosition = 'left',
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
        css={[styles.button({ variant, isOutlined, size, loading, disabled }), buttonCss]}
        onClick={onClick}
        tabIndex={tabIndex}
        disabled={disabled}
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
    );
  },
);

export default Button;

const spin = keyframes`
  0% {
    transform: rotate(0);
  }

  100% {
    transform: rotate(360deg);
  }
`;

const styles = {
  button: ({
    variant,
    isOutlined,
    size,
    loading,
    disabled,
  }: {
    variant: ButtonVariant;
    isOutlined: boolean;
    size: ButtonSize;
    loading: boolean;
    disabled: boolean;
  }) => css`
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

		${
      size === 'large' &&
      css`
			padding: ${spacing[12]} ${spacing[40]};
		`
    }

		${
      size === 'small' &&
      css`
			${typography.small('medium')};
			padding: ${spacing[6]} ${spacing[16]};
		`
    }
    
    ${
      variant === 'primary' &&
      css`
			background-color: ${colorTokens.action.primary.default};
			color: ${colorTokens.text.white};

				svg {
					color: ${colorTokens.icon.white};
				}

				&:hover {
					background-color: ${colorTokens.action.primary.hover};
				}

				&:focus {
					background-color: ${colorTokens.action.primary.hover};
					box-shadow: ${shadow.focus};
				}

				&:active {
					background-color: ${colorTokens.action.primary.active};
					box-shadow: none;
				}

			${
        isOutlined &&
        css`
				background-color: transparent;
				box-shadow: inset 0 0 0 1px ${colorTokens.stroke.brand};
				color: ${colorTokens.text.brand};

						svg {
							color: ${colorTokens.icon.brand};
						}

						&:hover {
							color: ${colorTokens.text.white};

							svg {
								color: ${colorTokens.icon.white};
							}
						}

						&:focus {
							color: ${colorTokens.text.white};

							svg {
								color: ${colorTokens.icon.white};
							}
						}

						&:active {
							color: ${colorTokens.text.white};

					svg {
						color: ${colorTokens.icon.white};
					}
				}
			`
      }

			${
        (disabled || loading) &&
        css`
				background-color: ${colorTokens.action.primary.disable};
				color: ${colorTokens.text.disable};
				svg {
					color: ${colorTokens.icon.disable.default};
				}

				${
          isOutlined &&
          css`
					background-color: transparent;
					box-shadow: inset 0 0 0 1px ${colorTokens.action.outline.disable};

					svg {
						color: ${colorTokens.icon.disable.default};
					}
				`
        }
			`
      }
		`
    }

    ${
      variant === 'WP' &&
      css`
			background-color: ${colorTokens.action.primary.wp};
			color: ${colorTokens.text.white};

			svg {
				color: ${colorTokens.icon.white};
			}

			&:hover {
				background-color: ${colorTokens.action.primary.wp_hover};
			}

			&:focus {
				background-color: ${colorTokens.action.primary.wp_hover};
				box-shadow: ${shadow.focus};
			}

			&:active {
				background-color: ${colorTokens.action.primary.wp};
				box-shadow: none;
			}

			${
        isOutlined &&
        css`
				background-color: transparent;
				box-shadow: inset 0 0 0 1px ${colorTokens.action.primary.wp};
				color: ${colorTokens.text.wp};

					svg {
						color: ${colorTokens.icon.brand};
					}

					&:hover {
						color: ${colorTokens.text.white};

						svg {
							color: ${colorTokens.icon.white};
						}
					}

					&:focus {
						color: ${colorTokens.text.white};

						svg {
							color: ${colorTokens.icon.white};
						}
					}

					&:active {
						color: ${colorTokens.text.white};

					svg {
						color: ${colorTokens.icon.white};
					}
				}
			`
      }

			${
        (disabled || loading) &&
        css`
				background-color: ${colorTokens.action.primary.disable};
				color: ${colorTokens.text.disable};
				svg {
					color: ${colorTokens.icon.disable.default};
				}

				${
          isOutlined &&
          css`
					background-color: transparent;
					box-shadow: inset 0 0 0 1px ${colorTokens.action.outline.disable};

					svg {
						color: ${colorTokens.icon.disable.default};
					}
				`
        }
			`
      }
		`
    }

    ${
      variant === 'secondary' &&
      css`
			background-color: ${colorTokens.action.secondary.default};
			color: ${colorTokens.text.brand};

				svg {
					color: ${colorTokens.icon.brand};
				}

				&:hover {
					background-color: ${colorTokens.action.secondary.hover};
				}

				&:focus {
					background-color: ${colorTokens.action.secondary.hover};
					box-shadow: ${shadow.focus};
				}

				&:active {
					background-color: ${colorTokens.action.secondary.active};
					box-shadow: none;
				}

			${
        isOutlined &&
        css`
				background-color: transparent;
				box-shadow: inset 0 0 0 1px ${colorTokens.stroke.neutral};
				color: ${colorTokens.text.brand};

				svg {
					color: ${colorTokens.icon.brand};
				}
			`
      }

			${
        (disabled || loading) &&
        css`
				background-color: ${colorTokens.action.primary.disable};
				color: ${colorTokens.text.disable};

						svg {
							color: ${colorTokens.icon.disable.default};
						}

				${
          isOutlined &&
          css`
					background-color: transparent;
					box-shadow: inset 0 0 0 1px ${colorTokens.action.outline.disable};

					svg {
						color: ${colorTokens.icon.disable.default};
					}
				`
        }
			`
      }
		`
    }

    ${
      variant === 'tertiary' &&
      css`
			background-color: ${colorTokens.action.outline.default};
			color: ${colorTokens.text.subdued};
			box-shadow: inset 0 0 0 1px ${colorTokens.stroke.default};

				svg {
					color: ${colorTokens.icon.hints};
				}

				&:hover {
					background-color: ${colorTokens.background.hover};
					box-shadow: inset 0 0 0 1px ${colorTokens.stroke.hover};
					color: ${colorTokens.text.title};

					svg {
						color: ${colorTokens.icon.brand};
					}
				}

				&:focus {
					box-shadow: inset 0 0 0 1px ${colorTokens.stroke.default}, ${shadow.focus};
					color: ${colorTokens.text.title};

					svg {
						color: ${colorTokens.icon.brand};
					}
				}

				&:active {
					background-color: ${colorTokens.background.active};
					box-shadow: inset 0 0 0 1px ${colorTokens.stroke.hover};
					color: ${colorTokens.text.title};

					svg {
						color: ${colorTokens.icon.hints};
					}
				}

			${
        isOutlined &&
        css`
				background-color: transparent;
			`
      }

			${
        (disabled || loading) &&
        css`
				background-color: ${colorTokens.action.primary.disable};
				color: ${colorTokens.text.disable};
				box-shadow: inset 0 0 0 1px ${colorTokens.action.outline.disable};

						svg {
							color: ${colorTokens.icon.disable.default};
						}

				${
          isOutlined &&
          css`
					background-color: transparent;
					box-shadow: inset 0 0 0 1px ${colorTokens.action.outline.disable};

					svg {
						color: ${colorTokens.icon.disable.default};
					}
				`
        }
			`
      }
		`
    }

    ${
      variant === 'danger' &&
      css`
			background-color: ${colorTokens.background.status.errorFail};
			color: ${colorTokens.text.error};

				svg {
					color: ${colorTokens.icon.error};
				}

				&:hover {
					background-color: ${colorTokens.background.status.errorFail};
				}

				&:focus {
					box-shadow: ${shadow.focus};
				}

				&:active {
					background-color: ${colorTokens.background.status.errorFail};
					box-shadow: none;
				}

			${
        isOutlined &&
        css`
				background-color: transparent;
				box-shadow: inset 0 0 0 1px ${colorTokens.stroke.danger};
			`
      }

			${
        (disabled || loading) &&
        css`
				background-color: ${colorTokens.action.primary.disable};
				color: ${colorTokens.text.disable};

						svg {
							color: ${colorTokens.icon.disable.default};
						}

				${
          isOutlined &&
          css`
					background-color: transparent;
					box-shadow: inset 0 0 0 1px ${colorTokens.action.outline.disable};

					svg {
						color: ${colorTokens.icon.disable.default};
					}
				`
        }
			`
      }
		`
    }

    ${
      variant === 'text' &&
      css`
			background-color: transparent;
			color: ${colorTokens.text.subdued};
			padding: ${spacing[8]};

			${
        size === 'large' &&
        css`
				padding: ${spacing[12]} ${spacing[8]};
			`
      }

			${
        size === 'small' &&
        css`
				padding: ${spacing[4]} ${spacing[8]};
			`
      }

				svg {
					color: ${colorTokens.icon.hints};
				}

				&:hover,
				&:focus {
					color: ${colorTokens.text.title};
					svg {
						color: ${colorTokens.icon.brand};
					}
				}

				&:active {
					svg {
						color: ${colorTokens.icon.hints};
					}
				}

			${
        (disabled || loading) &&
        css`
				color: ${colorTokens.text.disable};

				svg {
					color: ${colorTokens.icon.disable.default};
				}
			`
      }
		`
    }

    &:disabled {
			cursor: not-allowed;
			color: ${colorTokens.text.disable};

			svg {
				color: ${colorTokens.icon.disable.default};
			}

			&:hover {
				color: ${colorTokens.text.disable};

				svg {
					color: ${colorTokens.icon.disable.default};
				}
			}
		}
	`,
  buttonContent: ({ loading, disabled }: { loading: boolean; disabled: boolean }) => css`
		${styleUtils.display.flex()};
		align-items: center;

		${
      loading &&
      !disabled &&
      css`
			color: transparent;
		`
    }
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
		${
      iconPosition === 'right' &&
      css`
			margin-right: 0;
			margin-left: ${spacing[4]};
		`
    }

		${
      loading &&
      css`
			opacity: 0;
		`
    }

    ${
      !hasChildren &&
      css`
			margin-inline: 0;
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
};
