import { type SettingsField } from '@Settings/contexts/SettingsContext';
import Select from '@TutorShared/atoms/Select';
import type { Option } from '@TutorShared/utils/types';
import React from 'react';

interface SelectFieldProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
}

const SelectField: React.FC<SelectFieldProps> = ({ field, value, onChange }) => {
  // Convert field options to Select component format
  const options: Option<string>[] = field.options
    ? Object.entries(field.options).map(([optionValue, optionLabel]) => ({
        value: optionValue,
        label:
          typeof optionLabel === 'object' && optionLabel !== null
            ? // eslint-disable-next-line @typescript-eslint/no-explicit-any
              (optionLabel as any).post_title || (optionLabel as any).label || optionValue
            : String(optionLabel),
      }))
    : [];

  // Find the selected option
  const selectedOption = options.find((option) => option.value === value) || null;

  const handleChange = (selectedOption: Option<string> | null) => {
    onChange(selectedOption?.value || '');
  };

  return (
    <Select
      options={options}
      value={selectedOption}
      onChange={handleChange}
      placeholder="Select an option..."
      isSearchable={field.searchable}
    />
  );
};

export default SelectField;
