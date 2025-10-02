import { type SettingsField as SettingsFieldType, useSettings } from '@Settings/contexts/SettingsContext';
import React from 'react';

// Field components
import CheckboxField from '@Settings/components/fields/CheckboxField';
import CheckGroup from '@Settings/components/fields/CheckGroup';
import NumberField from '@Settings/components/fields/NumberField';
import RadioField from '@Settings/components/fields/RadioField';
import SelectField from '@Settings/components/fields/SelectField';
import TextareaField from '@Settings/components/fields/TextareaField';
import TextField from '@Settings/components/fields/TextField';
import ToggleSwitch from '@Settings/components/fields/ToggleSwitch';
import AnchorField from './fields/AnchorField';
import CheckboxNotification from './fields/CheckboxNotification';
import CheckGroupNested from './fields/CheckGroupNested';
import SegmentedField from './fields/SegmentedField';
import ToggleSingle from './fields/ToggleSingle';
import UploadField from './fields/UploadField';

interface SettingsFieldProps {
  field: SettingsFieldType;
}

const SettingsField: React.FC<SettingsFieldProps> = ({ field }) => {
  const { state, dispatch } = useSettings();

  const val = state.values[field.key] ?? field.default ?? '';
  const value = field.event ? val[field.event] : val;

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const handleChange = (newValue: any) => {
    dispatch({
      type: 'UPDATE_VALUE',
      payload: { key: field.key, value: field.event ? { ...val, [field.event]: newValue } : newValue },
    });
  };

  // Segments fields should be rendered with full width layout
  if (field.type === 'segments') {
    return <SegmentedField field={field} value={value} onChange={handleChange} />;
  }

  const commonProps = {
    field,
    value,
    onChange: handleChange,
  };

  switch (field.type) {
    case 'toggle_switch':
    case 'toggle_switch_button':
      return <ToggleSwitch {...commonProps} />;

    case 'toggle_single':
      return <ToggleSingle {...commonProps} />;

    case 'select':
      return <SelectField {...commonProps} />;

    case 'number':
      return <NumberField {...commonProps} />;

    case 'radio_vertical':
    case 'radio_horizontal':
    case 'radio_horizontal_full':
      return <RadioField {...commonProps} />;

    case 'checkbox_vertical':
    case 'checkbox_horizontal':
      return <CheckboxField {...commonProps} />;

    case 'checkbox_notification':
      return <CheckboxNotification {...commonProps} />;

    case 'checkgroup':
      // Check if it has group_options (nested fields) or options (simple checkboxes)
      if (field.group_options && Array.isArray(field.group_options)) {
        return <CheckGroupNested {...commonProps} />;
      } else {
        return <CheckGroup {...commonProps} />;
      }

    case 'checkgroup_vertical':
    case 'checkgroup_horizontal':
      return <CheckGroup {...commonProps} />;

    case 'upload_full':
      return <UploadField {...commonProps} />;

    case 'anchor':
      return <AnchorField {...commonProps} />;

    case 'textarea':
      return <TextareaField {...commonProps} />;

    case 'text':
    case 'email':
    case 'url':
    case 'password':
    default:
      return <TextField {...commonProps} />;
  }
};

export default SettingsField;
