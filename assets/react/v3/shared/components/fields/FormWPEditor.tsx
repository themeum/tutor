import type { FormControllerProps } from '@Utils/form';
import FormFieldWrapper from './FormFieldWrapper';
import WPEditor from '@Atoms/WPEditor';

interface FormWPEditorProps extends FormControllerProps<string | null> {
  label?: string;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string) => void;
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
