import { type SerializedStyles, css } from '@emotion/react';
import { type ReactNode, useRef, useState } from 'react';

import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, fontSize, fontWeight, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import type { IconCollection, Option } from '@Utils/types';

import FormFieldWrapper from './FormFieldWrapper';

interface FormInputWithPresetsProps extends FormControllerProps<string | null> {
  content?: string | ReactNode;
  contentPosition?: 'left' | 'right';
  showVerticalBar?: boolean;
  type?: 'number' | 'text';
  size?: 'regular' | 'large';
  label?: string;
  placeholder?: string;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  isHidden?: boolean;
  helpText?: string;
  removeOptionsMinWidth?: boolean;
  onChange?: (value: string) => void;
  presetOptions?: Option<string>[];
  selectOnFocus?: boolean;
  wrapperCss?: SerializedStyles;
  contentCss?: SerializedStyles;
  removeBorder?: boolean;
}

const FormInputWithPresets = ({
  field,
  fieldState,
  content,
  contentPosition = 'left',
  showVerticalBar = true,
  type = 'text',
  size = 'regular',
  label,
  placeholder = '',
  disabled,
  readOnly,
  loading,
  helpText,
  removeOptionsMinWidth = true,
  onChange,
  presetOptions = [],
  selectOnFocus = false,
  wrapperCss,
  contentCss,
  removeBorder = false,
}: FormInputWithPresetsProps) => {
  const fieldValue = field.value ?? '';

  const ref = useRef<HTMLInputElement>(null);
  const [isOpen, setIsOpen] = useState(false);

  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  return (
    <FormFieldWrapper
      fieldState={fieldState}
      field={field}
      label={label}
      disabled={disabled}
      readOnly={readOnly}
      loading={loading}
      helpText={helpText}
      removeBorder={removeBorder}
      placeholder={placeholder}
    >
      {(inputProps) => {
        const { css: inputCss, ...restInputProps } = inputProps;

        return (
          <>
            <div css={[styles.inputWrapper(!!fieldState.error, removeBorder), wrapperCss]} ref={triggerRef}>
              {content && contentPosition === 'left' && (
                <div css={[styles.inputLeftContent(showVerticalBar, size), contentCss]}>{content}</div>
              )}
              <input
                {...restInputProps}
                css={[inputCss, styles.input(contentPosition, showVerticalBar, size)]}
                onClick={() => setIsOpen(true)}
                autoComplete="off"
                readOnly={readOnly}
                ref={(element) => {
                  field.ref(element);
                  // @ts-ignore
                  ref.current = element; // this is not ideal but it is the only way to set ref to the input element
                }}
                onFocus={() => {
                  if (!selectOnFocus || !ref.current) {
                    return;
                  }
                  ref.current.select();
                }}
                value={fieldValue}
                onChange={(event) => {
                  const value =
                    type === 'number'
                      ? event.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')
                      : event.target.value;

                  field.onChange(value);

                  if (onChange) {
                    onChange(value);
                  }
                }}
                data-input
              />

              {content && contentPosition === 'right' && (
                <div css={styles.inputRightContent(showVerticalBar, size)}>{content}</div>
              )}
            </div>

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)} onEscape={() => setIsOpen(false)}>
              <div
                css={[
                  styles.optionsWrapper,
                  {
                    left: position.left,
                    top: position.top,
                    maxWidth: triggerWidth,
                  },
                ]}
                ref={popoverRef}
              >
                <ul css={[styles.options(removeOptionsMinWidth)]}>
                  {presetOptions.map((option) => (
                    <li
                      key={String(option.value)}
                      css={styles.optionItem({
                        isSelected: option.value === field.value,
                      })}
                    >
                      <button
                        type="button"
                        css={styles.label}
                        onClick={() => {
                          field.onChange(option.value);
                          onChange?.(option.value);
                          setIsOpen(false);
                        }}
                      >
                        <Show when={option.icon}>
                          <SVGIcon name={option.icon as IconCollection} width={32} height={32} />
                        </Show>
                        <span>{option.label}</span>
                      </button>
                    </li>
                  ))}
                </ul>
              </div>
            </Portal>
          </>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormInputWithPresets;

const styles = {
  mainWrapper: css`
    width: 100%;
  `,
  inputWrapper: (hasFieldError: boolean, removeBorder: boolean) => css`
    display: flex;
    align-items: center;

    ${
      !removeBorder &&
      css`
        border: 1px solid ${colorTokens.stroke.default};
        border-radius: ${borderRadius[6]};
        box-shadow: ${shadow.input};
        background-color: ${colorTokens.background.white};
      `
    }

    ${
      hasFieldError &&
      css`
        border-color: ${colorTokens.stroke.danger};
        background-color: ${colorTokens.background.status.errorFail};
      `
    };

    &:focus-within {
      ${styleUtils.inputFocus};

      ${
        hasFieldError &&
        css`
          border-color: ${colorTokens.stroke.danger};
        `
      }
    }
  `,
  input: (contentPosition: string, showVerticalBar: boolean, size: string) => css`
    /** Increasing the css specificity */
    &[data-input] {
      ${typography.body()};
      border: none;
      box-shadow: none;
      background-color: transparent;
      padding-${contentPosition}: 0;

      ${
        showVerticalBar &&
        css`
          padding-${contentPosition}: ${spacing[10]};
        `
      };

      ${
        size === 'large' &&
        css`
          font-size: ${fontSize[24]};
          font-weight: ${fontWeight.medium};
          height: 34px;
          ${
            showVerticalBar &&
            css`
              padding-${contentPosition}: ${spacing[12]};
            `
          };
        `
      }

      &:focus {
        box-shadow: none;
        outline: none;
      }
    }
  `,
  label: css`
    ${styleUtils.resetButton};
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    margin: 0 ${spacing[12]};
    padding: ${spacing[6]} 0;
    text-align: left;
    line-height: ${lineHeight[24]};
    word-break: break-all;
    cursor: pointer;

    span {
      flex-shrink: 0;
    }
  `,
  optionsWrapper: css`
		position: absolute;
		width: 100%;
	`,
  options: (removeOptionsMinWidth: boolean) => css`
		z-index: ${zIndex.dropdown};
		background-color: ${colorTokens.background.white};
		list-style-type: none;
		box-shadow: ${shadow.popover};
		padding: ${spacing[4]} 0;
		margin: 0;
		max-height: 500px;
		border-radius: ${borderRadius[6]};
		${styleUtils.overflowYAuto};

		${
      !removeOptionsMinWidth &&
      css`
				min-width: 200px;
			`
    }
	`,
  optionItem: ({ isSelected = false }: { isSelected: boolean }) => css`
		${typography.body()};
		min-height: 36px;
		height: 100%;
		width: 100%;
		display: flex;
		align-items: center;
		transition: background-color 0.3s ease-in-out;
		cursor: pointer;

		&:hover {
			background-color: ${colorTokens.background.hover};
		}

		${
      isSelected &&
      css`
				background-color: ${colorTokens.background.active};
				position: relative;

				&::before {
					content: '';
					position: absolute;
					top: 0;
					left: 0;
					width: 3px;
					height: 100%;
					background-color: ${colorTokens.action.primary.default};
					border-radius: 0 ${borderRadius[6]} ${borderRadius[6]} 0;
				}
			`
    }
	`,
  inputLeftContent: (showVerticalBar: boolean, size: string) => css`
    ${typography.small()}
    ${styleUtils.flexCenter()}
    height: 40px;
    min-width: 48px;
    color: ${colorTokens.icon.subdued};
    padding-inline: ${spacing[12]};

    ${
      size === 'large' &&
      css`
        ${typography.body()}
      `
    }

    ${
      showVerticalBar &&
      css`
        border-right: 1px solid ${colorTokens.stroke.default};
      `
    }
  `,
  inputRightContent: (showVerticalBar: boolean, size: string) => css`
    ${typography.small()}
    ${styleUtils.flexCenter()}
    height: 40px;
    min-width: 48px;
    color: ${colorTokens.icon.subdued};
    padding-inline: ${spacing[12]};

    ${
      size === 'large' &&
      css`
        ${typography.body()}
      `
    }

    ${
      showVerticalBar &&
      css`
        border-left: 1px solid ${colorTokens.stroke.default};
      `
    }
  `,
};
