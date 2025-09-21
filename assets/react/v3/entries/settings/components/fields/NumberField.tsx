import { type SettingsField } from '@Settings/contexts/SettingsContext';
import TextInput from '@TutorShared/atoms/TextInput';
import React from 'react';

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
    <TextInput type="number" value={value?.toString() || ''} onChange={handleChange} placeholder="Enter a number..." />
  );
};

export default NumberField;
