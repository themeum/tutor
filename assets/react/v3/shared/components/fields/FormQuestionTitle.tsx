import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { FormControllerProps } from '@Utils/form';
import { type SerializedStyles, css } from '@emotion/react';

import Show from '@Controls/Show';
import { isDefined } from '@Utils/types';
import FormFieldWrapper from './FormFieldWrapper';
import { useEffect, useRef, useState } from 'react';

interface FormQuestionTitleProps extends FormControllerProps<string | null> {
	label?: string;
	maxLimit?: number;
	disabled?: boolean;
	readOnly?: boolean;
	loading?: boolean;
	placeholder?: string;
	helpText?: string;
	onChange?: (value: string | number) => void;
	onKeyDown?: (keyName: string) => void;
	isHidden?: boolean;
	isSecondary?: boolean;
	removeBorder?: boolean;
	dataAttribute?: string;
	isInlineLabel?: boolean;
	style?: SerializedStyles;
}

const FormQuestionTitle = ({
	label,
	maxLimit,
	field,
	fieldState,
	disabled,
	readOnly,
	loading,
	placeholder,
	helpText,
	onChange,
	onKeyDown,
	isHidden,
	isSecondary = false,
	removeBorder,
	dataAttribute,
	isInlineLabel = false,
	style,
}: FormQuestionTitleProps) => {
	const inputValue = field.value ?? '';
	const inputRef = useRef<HTMLInputElement>(null);

	const [isEdit, setIsEdit] = useState<boolean>(false);
	const [previousValue, setPreviousValue] = useState<string>(inputValue);

	let characterCount:
		| {
				maxLimit: number;
				inputCharacter: number;
		  }
		| undefined = undefined;

	if (maxLimit) {
		characterCount = { maxLimit, inputCharacter: inputValue.toString().length };
	}

	const additionalAttributes = {
		...(isDefined(dataAttribute) && { [dataAttribute]: true }),
	};

	// biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
	useEffect(() => {
		if (isDefined(inputRef.current)) {
			inputRef.current.focus();
			setPreviousValue(inputValue);
		}
	}, [isEdit, inputRef.current]);

	return (
		<div css={styles.container({ isEdit })}>
			<Show when={!isEdit}>
				<div css={styles.placeholder} onClick={() => setIsEdit(true)} role="button">
					{field.value || placeholder}
				</div>
			</Show>

			<Show when={isEdit}>
				<FormFieldWrapper
					label={label}
					field={field}
					fieldState={fieldState}
					disabled={disabled}
					readOnly={readOnly}
					loading={loading}
					helpText={helpText}
					isHidden={isHidden}
					characterCount={characterCount}
					isSecondary={isSecondary}
					removeBorder={removeBorder}
					isInlineLabel={isInlineLabel}
					inputStyle={style}
				>
					{(inputProps) => {
						return (
							<>
								<div css={styles.inputContainer(false)}>
									<input
										{...field}
										{...inputProps}
										{...additionalAttributes}
										type="text"
										ref={inputRef}
										value={inputValue}
										onChange={(event) => {
											const { value } = event.target;

											field.onChange(value);

											if (onChange) {
												onChange(value);
											}
										}}
										onKeyDown={(event) => {
											if (event.key === 'Enter') {
												setIsEdit(false);
											}
											if (event.key === 'Escape') {
												field.onChange(previousValue);
												setIsEdit(false);
											}
											onKeyDown?.(event.key);
										}}
										autoComplete="off"
									/>
								</div>
							</>
						);
					}}
				</FormFieldWrapper>
			</Show>
			<div data-action-buttons css={styles.actionButtonWrapper({ isEdit })}>
				<Show
					when={isEdit}
					fallback={
						<Button
							buttonCss={css`${styles.actionButton}`}
							type="button"
							variant="text"
							size="small"
							onClick={() => setIsEdit(true)}
						>
							<SVGIcon name="edit" height={24} width={24} />
						</Button>
					}
				>
					<>
						<Button
							buttonCss={styles.actionButton}
							variant="text"
							size="small"
							onClick={() => {
								setIsEdit(false);
							}}
							disabled={field.value === previousValue}
						>
							<SVGIcon name="checkMark" height={24} width={24} />
						</Button>
						<Button
							buttonCss={styles.actionButton}
							variant="text"
							size="small"
							onClick={() => {
								field.onChange(previousValue);
								setIsEdit(false);
							}}
						>
							<SVGIcon name="lineCross" height={24} width={24} />
						</Button>
					</>
				</Show>
			</div>
		</div>
	);
};

export default FormQuestionTitle;

const styles = {
	container: ({
		isEdit,
	}: {
		isEdit: boolean;
	}) => css`
    position: relative;
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    gap: ${spacing[8]};
		height: 46px;
    width: 100%;
    padding: 0 ${spacing[16]} 0 ${spacing[8]};
    border-radius: ${borderRadius[6]};

    &:hover {
      background-color: ${colorTokens.background.white};
      color: ${colorTokens.text.subdued};
      cursor: text;

			[data-action-buttons] {
				opacity: 1;
			}
    };

    ${
			isEdit &&
			css`
      background-color: ${colorTokens.background.white};
      color: ${colorTokens.text.subdued};
    `
		}
  `,
	inputContainer: (isClearable: boolean) => css`
    position: relative;
    display: flex;
		transition: background 0.15s ease-in-out;

    & input {
      ${typography.heading6()}
			color: ${colorTokens.text.primary};
			border: none;
			background: none;
			padding: 0;
      ${isClearable && `padding-right: ${spacing[36]};`};
      width: 100%;

			&:focus {
				box-shadow: none;
			}
    }
  `,
	clearButton: css`
    position: absolute;
    right: ${spacing[2]};
    top: ${spacing[2]};
    width: 36px;
    height: 36px;
    border-radius: ${borderRadius[2]};
    background: transparent;

    button {
      padding: ${spacing[10]};
    }
  `,
	placeholder: css`
    ${typography.heading6()}
    color: ${colorTokens.text.hints};
  `,
	actionButtonWrapper: ({
		isEdit,
	}: {
		isEdit: boolean;
	}) => css`
    display: flex;
		align-items: center;
		gap: ${spacing[8]};
    opacity: 0;
    transition: opacity 0.15s ease-in-out;

		${
			isEdit &&
			css`
				opacity: 1;
			`
		}

  `,
	actionButton: css`
		padding-inline: 0;
	`,
};
