import { type SettingsField } from '@Settings/contexts/SettingsContext';
import TextInput from '@TutorShared/atoms/TextInput';
import React from 'react';
import { fieldStyles } from './fieldStyles';

interface TextFieldProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
}

const TextField: React.FC<TextFieldProps> = ({ field, value, onChange }) => {
  const getInputType = (): 'text' | 'number' => {
    // TextInput component only supports 'text' and 'number' types
    // For email, url, password we'll use 'text' and rely on HTML5 validation if needed
    return 'text';
  };

  const getPlaceholder = () => {
    switch (field.type) {
      case 'email':
        return 'Enter email address...';
      case 'url':
        return 'Enter URL...';
      case 'password':
        return 'Enter password...';
      default:
        return 'Enter text...';
    }
  };

  return (
    <div css={fieldStyles.fieldRow}>
      <div css={fieldStyles.labelContainer}>
        <label css={fieldStyles.label}>{field.label}</label>
        {field.label_title && <div css={fieldStyles.labelTitle}>{field.label_title}</div>}
        {field.desc && (
          <div css={fieldStyles.description}>
            <div dangerouslySetInnerHTML={{ __html: field.desc }} />
          </div>
        )}
      </div>

      <div css={fieldStyles.inputContainer}>
        <TextInput
          type={getInputType()}
          value={value?.toString() || ''}
          onChange={onChange}
          placeholder={getPlaceholder()}
        />
      </div>
    </div>
  );
};

export default TextField;
