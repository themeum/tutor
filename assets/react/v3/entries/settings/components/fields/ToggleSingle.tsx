import { type SettingsField } from '@Settings/contexts/SettingsContext';
import Switch from '@TutorShared/atoms/Switch';
import React from 'react';
import { fieldStyles } from './fieldStyles';

interface ToggleSingleProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
}

const ToggleSingle: React.FC<ToggleSingleProps> = ({ field, value, onChange }) => {
  const isChecked = value === 'on' || value === true || value === '1';

  const handleChange = (checked: boolean) => {
    onChange(checked ? 'on' : 'off');
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
        <Switch checked={isChecked} onChange={handleChange} size="regular" labelPosition="left" />
      </div>
    </div>
  );
};

export default ToggleSingle;
