import Alert from '@TutorShared/atoms/Alert';
import ComponentErrorBoundary from '@TutorShared/components/ComponentErrorBoundary';
import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormDateInput from '@TutorShared/components/fields/FormDateInput';
import FormFileUploader from '@TutorShared/components/fields/FormFileUploader';
import FormImageInput from '@TutorShared/components/fields/FormImageInput';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormRadioGroup from '@TutorShared/components/fields/FormRadioGroup';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import FormSwitch from '@TutorShared/components/fields/FormSwitch';
import FormTextareaInput from '@TutorShared/components/fields/FormTextareaInput';
import FormTimeInput from '@TutorShared/components/fields/FormTimeInput';
import FormVideoInput from '@TutorShared/components/fields/FormVideoInput';
import { type FormControllerProps } from '@TutorShared/utils/form';
import { type Option } from '@TutorShared/utils/types';
import React from 'react';
import { Controller, type RegisterOptions, type UseFormReturn } from 'react-hook-form';

interface FieldRendererProps {
  name: string;
  label?: string;
  helpText?: string;
  infoText?: string;
  placeholder?: string;
  type: string;
  options?: Option<string | number>[];
  defaultValue?: unknown;
  rules?: Exclude<RegisterOptions, 'valueAsNumber' | 'valueAsDate' | 'setValueAs'>;
  form: UseFormReturn;
}

const FieldRenderer: React.FC<FieldRendererProps> = ({
  name,
  label,
  helpText,
  infoText,
  placeholder,
  type,
  options,
  defaultValue,
  rules,
  form,
}) => {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const renderField = (controllerProps: FormControllerProps<any>) => {
    const field = (() => {
      switch (type) {
        case 'text':
          return <FormInput {...controllerProps} label={label} placeholder={placeholder} helpText={helpText} />;
        case 'textarea':
          return <FormTextareaInput {...controllerProps} label={label} placeholder={placeholder} helpText={helpText} />;
        case 'select':
          return (
            <FormSelectInput
              {...controllerProps}
              label={label}
              options={options || []}
              placeholder={placeholder}
              helpText={helpText}
            />
          );
        case 'radio':
          return <FormRadioGroup {...controllerProps} label={label} options={options || []} />;
        case 'checkbox':
          return <FormCheckbox {...controllerProps} label={label} />;
        case 'switch':
          return <FormSwitch {...controllerProps} label={label} helpText={helpText} />;
        case 'date':
          return <FormDateInput {...controllerProps} label={label} placeholder={placeholder} helpText={helpText} />;
        case 'time':
          return <FormTimeInput {...controllerProps} label={label} placeholder={placeholder} helpText={helpText} />;
        case 'image':
          return <FormImageInput {...controllerProps} label={label} helpText={helpText} infoText={infoText} />;
        case 'video':
          return <FormVideoInput {...controllerProps} label={label} helpText={helpText} infoText={infoText} />;
        case 'uploader':
          return <FormFileUploader {...controllerProps} label={label} helpText={helpText} />;
        default:
          return <Alert type="danger">Unsupported field type: {type}</Alert>;
      }
    })();

    return (
      <ComponentErrorBoundary
        componentName={`field ${name}`}
        onError={(error, errorInfo) => {
          console.warn(`Field ${name} failed to render:`, { error, errorInfo });
        }}
      >
        {field}
      </ComponentErrorBoundary>
    );
  };

  return (
    <Controller
      name={name}
      control={form.control}
      defaultValue={defaultValue ?? ''}
      rules={rules}
      render={(controllerProps) => renderField(controllerProps)}
    />
  );
};

export default FieldRenderer;
