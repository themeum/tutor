import { type SettingsField } from '@Settings/contexts/SettingsContext';
import Select from '@TutorShared/atoms/Select';
import type { Option } from '@TutorShared/utils/types';
import React from 'react';
import { fieldStyles } from './fieldStyles';

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
  const selectedOption = options.find((option) => option.value === value) || undefined;

  const handleChange = (selectedOption: Option<string> | null) => {
    onChange(selectedOption?.value || '');
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
          <Select
            options={options}
            value={selectedOption}
            onChange={handleChange}
            placeholder="Select an option..."
            isSearchable={field.searchable}
          />
        </div>
      </div>
    </div>
  );
};

export default SelectField;
