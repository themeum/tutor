import WPEditor from '@Atoms/WPEditor';
import type { FormControllerProps } from '@Utils/form';
import FormFieldWrapper from './FormFieldWrapper';

interface FormWPEditorProps extends FormControllerProps<string | null> {
  label?: string;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string) => void;
  generateWithAi?: boolean;
  onClickAiButton?: () => void;
}

const FormWPEditor = ({
  label,
  field,
  fieldState,
  disabled,
  readOnly,
  loading,
  placeholder,
  helpText,
  onChange,
  generateWithAi = false,
  onClickAiButton,
}: FormWPEditorProps) => {
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
      generateWithAi={generateWithAi}
      onClickAiButton={onClickAiButton}
    >
      {() => {
        return (
          <>
            <WPEditor
              value={field.value ?? ''}
              onChange={(value) => {
                field.onChange(value);

                if (onChange) {
                  onChange(value);
                }
              }}
            />
          </>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormWPEditor;
