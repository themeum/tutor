import { type SettingsField } from '@Settings/contexts/SettingsContext';
import Switch from '@TutorShared/atoms/Switch';
import React from 'react';

interface ToggleSwitchProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
}

const ToggleSwitch: React.FC<ToggleSwitchProps> = ({ field, value, onChange }) => {
  const isChecked = value === 'on' || value === true || value === '1';

  const handleToggle = (checked: boolean) => {
    onChange(checked ? 'on' : 'off');
  };

  return <Switch checked={isChecked} onChange={handleToggle} label={field.label_title} disabled={false} />;
};

export default ToggleSwitch;
