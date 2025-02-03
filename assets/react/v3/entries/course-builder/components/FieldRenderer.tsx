import Alert from '@TutorShared/atoms/Alert';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormRadioGroup from '@TutorShared/components/fields/FormRadioGroup';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import FormTextareaInput from '@TutorShared/components/fields/FormTextareaInput';
import React from 'react';
import { Controller } from 'react-hook-form';

interface FieldRendererProps {
  name: string;
  label?: string;
  placeholder?: string;
  type: string;
  options?: any[];
  defaultValue?: any;
  rules?: any;
  form: any;
}

const FieldRenderer: React.FC<FieldRendererProps> = ({
  name,
  label,
  placeholder,
  type,
  options,
  defaultValue,
  rules,
  form,
}) => {
  const renderField = (controllerProps: any) => {
    switch (type) {
      case 'text':
        return <FormInput {...controllerProps} label={label} placeholder={placeholder} />;
      case 'textarea':
        return <FormTextareaInput {...controllerProps} label={label} placeholder={placeholder} />;
      case 'select':
        return <FormSelectInput {...controllerProps} label={label} options={options || []} placeholder={placeholder} />;
      case 'radio':
        return <FormRadioGroup {...controllerProps} label={label} options={options || []} />;
      default:
        return <Alert type="danger">Unsupported field type: {type}</Alert>;
    }
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
