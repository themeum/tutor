import { spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { FormControllerProps } from '@Utils/form';
import { css } from '@emotion/react';

import SVGIcon from '@Atoms/SVGIcon';
import AITextModal from '@Components/modals/AITextModal';
import { useModal } from '@Components/modals/Modal';
import { __ } from '@wordpress/i18n';
import FormFieldWrapper from './FormFieldWrapper';

interface FormTextareaInputProps extends FormControllerProps<string | null> {
  label?: string;
  rows?: number;
  columns?: number;
  maxLimit?: number;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string | number) => void;
  onKeyDown?: (keyName: string) => void;
  isHidden?: boolean;
  enableResize?: boolean;
  isSecondary?: boolean;
  isMagicAi?: boolean;
  generateWithAi?: boolean;
}

const DEFAULT_ROWS = 6;

const FormTextareaInput = ({
  label,
  rows = DEFAULT_ROWS,
  columns,
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
  enableResize = true,
  isSecondary = false,
  isMagicAi = false,
  generateWithAi = false,
}: FormTextareaInputProps) => {
  const inputValue = field.value ?? '';
  const { showModal } = useModal();

  let characterCount: { maxLimit: number; inputCharacter: number } | undefined = undefined;

  if (maxLimit) {
    characterCount = { maxLimit, inputCharacter: inputValue.toString().length };
  }

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      readOnly={readOnly}
      loading={loading}
      placeholder={placeholder}
      helpText={helpText}
      isHidden={isHidden}
      characterCount={characterCount}
      isSecondary={isSecondary}
      isMagicAi={isMagicAi}
      generateWithAi={generateWithAi}
      onClickAiButton={() => {
        showModal({
          component: AITextModal,
          isMagicAi: true,
          props: {
            title: __('AI Studio', 'tutor'),
            icon: <SVGIcon name="magicAiColorize" width={24} height={24} />,
            field,
            fieldState,
            is_html: true,
            fieldLabel: __('Craft Your Course Description', 'tutor'),
            fieldPlaceholder: __(
              'Provide a brief overview of your course topic, target audience, and key takeaways',
              'tutor',
            ),
          },
        });
      }}
    >
      {(inputProps) => {
        return (
          <>
            <div css={styles.container(enableResize)}>
              <textarea
                {...field}
                {...inputProps}
                value={inputValue}
                onChange={(event) => {
                  const { value } = event.target;
                  if (maxLimit && value.trim().length > maxLimit) {
                    return;
                  }

                  field.onChange(value);

                  if (onChange) {
                    onChange(value);
                  }
                }}
                onKeyDown={(event) => {
                  onKeyDown?.(event.key);
                }}
                autoComplete="off"
                rows={rows}
                cols={columns}
              />
            </div>
          </>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormTextareaInput;

const styles = {
  container: (enableResize = false) => css`
    position: relative;
    display: flex;

    textarea {
      ${typography.body()};
      height: auto;
      padding: ${spacing[8]} ${spacing[12]};
      resize: none;

      ${
        enableResize &&
        css`
        resize: vertical;
      `
      }
    }
  `,
};
