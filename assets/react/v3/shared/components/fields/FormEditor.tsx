import { borderRadius, colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { RichTextEditor } from '@mantine/rte';

import FormFieldWrapper from './FormFieldWrapper';

const styles = {
	container: (hasError: boolean) => css`
    position: relative;
    display: flex;

    & input {
      ${typography.body()}
      width: 100%;
    }
    .mantine-RichTextEditor-root {
      width: 100%;
      ${
				hasError &&
				css`
        border-color: ${colorPalate.basic.critical};
      `
			}
    }
    .mantine-RichTextEditor-toolbar {
      background-color: #eff1f7;

      ${
				hasError &&
				css`
        background-color: ${colorPalate.surface.critical.neutral};
        border-color: ${colorPalate.basic.critical};
      `
			}
    }

    .mantine-UnstyledButton-root {
      ${styleUtils.resetButton};

      width: 32px;

      svg {
        width: 18px;
        height: 18px;
        stroke: ${colorPalate.icon.default};
        stroke-width: 2px;
      }
    }

    .ql-editor {
      min-height: 200px;
      color: ${colorPalate.text.default};
    }
  `,
	maxLimit: css`
    ${typography.caption()};
    color: ${colorPalate.text.neutral};
    text-transform: lowercase;
  `,
	clearButton: css`
    position: absolute;
    right: ${spacing[4]};
    top: ${spacing[6]};
    width: 26px;
    height: 26px;
    border-radius: ${borderRadius[2]};
    background: 'transparent';

    &:hover {
      background: ${colorPalate.surface.hover};
    }
  `,
	editorCss: css`
    padding: ${spacing[16]};
    min-height: 130px;
  `,
};

interface FormEditorProps extends FormControllerProps<string> {
	label?: string;
	disabled?: boolean;
	loading?: boolean;
	placeholder?: string;
	helpText?: string;
}

const FormEditor = ({ label, field, fieldState, disabled, loading, placeholder, helpText }: FormEditorProps) => {
	return (
		<FormFieldWrapper
			label={label}
			field={field}
			fieldState={fieldState}
			disabled={disabled}
			loading={loading}
			placeholder={placeholder}
			helpText={helpText}
		>
			{() => {
				return (
					<div css={styles.container(!!fieldState.error)}>
						<RichTextEditor
							value={field.value}
							onChange={field.onChange}
							controls={[
								['bold', 'italic', 'underline', 'strike'],
								['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
								['unorderedList', 'orderedList'],
								['link', 'image', 'video', 'blockquote', 'code'],
								['alignLeft', 'alignCenter', 'alignRight'],
								['sup', 'sub'],
							]}
							id="rte"
							sticky={false}
							placeholder={placeholder}
						/>
					</div>
				);
			}}
		</FormFieldWrapper>
	);
};

export default FormEditor;
