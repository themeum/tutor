import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, fontSize, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { useState } from 'react';

import Checkbox from '@Atoms/CheckBox';
import Chip from '@Atoms/Chip';
import { useDebounce } from '@Hooks/useDebounce';
import { type Tag, useCreateTagMutation, useTagListQuery } from '@Services/tags';
import { __ } from '@wordpress/i18n';
import FormFieldWrapper from './FormFieldWrapper';
import type { IconCollection, Option } from '@Utils/types';
import Show from '@Controls/Show';
import Button from '@Atoms/Button';

interface FormInputWithPresetsProps extends FormControllerProps<string | null> {
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
	isClearable?: boolean;
}

const FormInputWithPresets = ({
	field,
	fieldState,
	label,
	placeholder = '',
	disabled,
	readOnly,
	loading,
	helpText,
	removeOptionsMinWidth = true,
	onChange,
	presetOptions = [],
	isClearable = false,
}: FormInputWithPresetsProps) => {
	const fieldValue = field.value ?? '';

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
		>
			{(inputProps) => {
				const { css: inputCss, ...restInputProps } = inputProps;

				return (
					<div css={styles.mainWrapper}>
						<div css={styles.inputWrapper} ref={triggerRef}>
							<input
								{...restInputProps}
								css={[inputCss, styles.input]}
								onClick={() => setIsOpen(true)}
								autoComplete="off"
								readOnly={readOnly}
								placeholder={placeholder}
								value={fieldValue}
								onChange={(event) => {
									const { value } = event.target;

									field.onChange(value);

									if (onChange) {
										onChange(value);
									}
								}}
							/>
						</div>

						<Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
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
													onChange && onChange(option.value);
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
					</div>
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
	inputWrapper: css`
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
  `,
	input: css`
    ${typography.body()};
    width: 100%;
    ${styleUtils.textEllipsis};

    :focus {
      outline: none;
      box-shadow: ${shadow.focus};
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
};
