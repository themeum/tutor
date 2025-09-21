import { type SettingsField as SettingsFieldType, useSettings } from '@Settings/contexts/SettingsContext';
import React from 'react';

// Field components
import { css } from '@emotion/react';
import CheckboxField from '@Settings/components/fields/CheckboxField';
import CheckGroup from '@Settings/components/fields/CheckGroup';
import NumberField from '@Settings/components/fields/NumberField';
import RadioField from '@Settings/components/fields/RadioField';
import SelectField from '@Settings/components/fields/SelectField';
import TextareaField from '@Settings/components/fields/TextareaField';
import TextField from '@Settings/components/fields/TextField';
import ToggleSwitch from '@Settings/components/fields/ToggleSwitch';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import AnchorField from './fields/AnchorField';
import CheckboxNotification from './fields/CheckboxNotification';
import CheckGroupNested from './fields/CheckGroupNested';
import ToggleSingle from './fields/ToggleSingle';
import UploadField from './fields/UploadField';

interface SettingsFieldProps {
  field: SettingsFieldType;
}

const styles = {
  fieldRow: css`
    display: block;
    margin-bottom: ${spacing[16]};

    @media (min-width: 992px) {
      display: flex;
    }
  `,

  labelColumn: css`
    width: 100%;
    margin-bottom: ${spacing[8]};

    @media (min-width: 992px) {
      width: 33.333333%;
      margin-bottom: 0;
      padding-right: ${spacing[16]};
    }
  `,

  labelContainer: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
  `,

  label: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.title};
    margin: 0;
  `,

  labelTitle: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,

  inputColumn: css`
    width: 100%;

    @media (min-width: 992px) {
      width: 66.666667%;
    }
  `,

  inputContainer: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,

  description: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;

    p {
      margin: 0;
    }

    a {
      color: ${colorTokens.text.brand};
      text-decoration: none;

      &:hover {
        text-decoration: underline;
      }
    }
  `,
};

const SettingsField: React.FC<SettingsFieldProps> = ({ field }) => {
  const { state, dispatch } = useSettings();

  const value = state.values[field.key] ?? field.default ?? '';

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const handleChange = (newValue: any) => {
    dispatch({
      type: 'UPDATE_VALUE',
      payload: { key: field.key, value: newValue },
    });
  };

  const renderField = () => {
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

  return (
    <div css={styles.fieldRow}>
      <div css={styles.labelColumn}>
        <div css={styles.labelContainer}>
          <label css={styles.label}>{field.label}</label>
          {field.label_title && <div css={styles.labelTitle}>{field.label_title}</div>}
        </div>
      </div>

      <div css={styles.inputColumn}>
        <div css={styles.inputContainer}>
          {renderField()}

          {field.desc && (
            <div css={styles.description}>
              <div dangerouslySetInnerHTML={{ __html: field.desc }} />
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default SettingsField;
