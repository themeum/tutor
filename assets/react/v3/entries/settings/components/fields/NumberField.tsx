import { type SettingsField } from '@Settings/contexts/SettingsContext';
import TextInput from '@TutorShared/atoms/TextInput';
import React from 'react';
import { fieldStyles } from './fieldStyles';

interface NumberFieldProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
}

const NumberField: React.FC<NumberFieldProps> = ({ field, value, onChange }) => {
  const handleChange = (newValue: string) => {
    if (!newValue) {
      onChange('');
      return;
    }

    if (field.number_type === 'integer') {
      const intValue = parseInt(newValue, 10);
      onChange(isNaN(intValue) ? '' : intValue);
    } else {
      const floatValue = parseFloat(newValue);
      onChange(isNaN(floatValue) ? '' : floatValue);
    }
  };

  return (
    <div css={fieldStyles.fieldRow}>
      <div css={fieldStyles.labelColumn}>
        <div css={fieldStyles.labelContainer}>
          <label css={fieldStyles.label}>{field.label}</label>
          {field.label_title && <div css={fieldStyles.labelTitle}>{field.label_title}</div>}
          {field.desc && (
            <div css={fieldStyles.description}>
              <div dangerouslySetInnerHTML={{ __html: field.desc }} />
            </div>
          )}
        </div>
      </div>

      <div css={fieldStyles.inputColumn}>
        <div css={fieldStyles.inputContainer}>
          <TextInput
            type="number"
            value={value?.toString() || ''}
            onChange={handleChange}
            placeholder="Enter a number..."
          />
        </div>
      </div>
    </div>
  );
};

export default NumberField;
