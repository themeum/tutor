import { css } from '@emotion/react';
import { type SettingsField } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Tabs, { type TabItem } from '@TutorShared/molecules/Tabs';
import React, { useState } from 'react';

// Import field components directly to avoid circular dependency
import AnchorField from './AnchorField';
import CheckboxField from './CheckboxField';
import CheckboxNotification from './CheckboxNotification';
import CheckGroup from './CheckGroup';
import CheckGroupNested from './CheckGroupNested';
import NumberField from './NumberField';
import RadioField from './RadioField';
import SelectField from './SelectField';
import TextareaField from './TextareaField';
import TextField from './TextField';
import ToggleSingle from './ToggleSingle';
import ToggleSwitch from './ToggleSwitch';
import UploadField from './UploadField';

interface SegmentedFieldProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
}

const styles = {
  container: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,

  header: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,

  title: css`
    ${typography.heading5('medium')};
    color: ${colorTokens.text.title};
    margin: 0;
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

  tabsContainer: css`
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[8]};
    background-color: ${colorTokens.background.white};
    overflow: hidden;
  `,

  tabContent: css`
    padding: ${spacing[24]};
  `,

  fieldsContainer: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[20]};
  `,
};

const fieldStyles = {
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

const SegmentedField: React.FC<SegmentedFieldProps> = ({ field, value, onChange }) => {
  const [activeTab, setActiveTab] = useState<string>(field.segments?.[0]?.slug || '');

  // Handle nested field value changes
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const handleNestedFieldChange = (fieldKey: string, fieldValue: any) => {
    const currentValues = typeof value === 'object' && value !== null ? value : {};
    const newValues = {
      ...currentValues,
      [fieldKey]: fieldValue,
    };
    onChange(newValues);
  };

  // Get value for a specific nested field
  const getNestedFieldValue = (fieldKey: string) => {
    if (typeof value === 'object' && value !== null) {
      return value[fieldKey];
    }
    return undefined;
  };

  // Render nested field based on its type
  const renderNestedField = (nestedField: SettingsField) => {
    const fieldValue = getNestedFieldValue(nestedField.key) ?? nestedField.default ?? '';
    const actualValue = nestedField.event ? fieldValue[nestedField.event] : fieldValue;

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const handleFieldChange = (newValue: any) => {
      const finalValue = nestedField.event ? { ...fieldValue, [nestedField.event]: newValue } : newValue;
      handleNestedFieldChange(nestedField.key, finalValue);
    };

    const commonProps = {
      field: nestedField,
      value: actualValue,
      onChange: handleFieldChange,
    };

    switch (nestedField.type) {
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
        if (nestedField.group_options && Array.isArray(nestedField.group_options)) {
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

  if (!field.segments || !Array.isArray(field.segments)) {
    return null;
  }

  // Create tab items from segments
  const tabItems: TabItem<string>[] = field.segments.map((segment) => ({
    label: segment.label,
    value: segment.slug,
  }));

  // Find active segment
  const activeSegment = field.segments.find((segment) => segment.slug === activeTab);

  return (
    <div css={styles.container}>
      <div css={styles.header}>
        <h3 css={styles.title}>{field.label}</h3>
        {field.desc && (
          <div css={styles.description}>
            <div dangerouslySetInnerHTML={{ __html: field.desc }} />
          </div>
        )}
      </div>

      <div css={styles.tabsContainer}>
        <Tabs activeTab={activeTab} onChange={setActiveTab} tabList={tabItems} orientation="horizontal" />

        <div css={styles.tabContent}>
          {activeSegment && (
            <div css={styles.fieldsContainer}>
              {activeSegment.fields.map((nestedField) => (
                <div key={nestedField.key} css={fieldStyles.fieldRow}>
                  <div css={fieldStyles.labelColumn}>
                    <div css={fieldStyles.labelContainer}>
                      <label css={fieldStyles.label}>{nestedField.label}</label>
                      {nestedField.label_title && <div css={fieldStyles.labelTitle}>{nestedField.label_title}</div>}
                    </div>
                  </div>

                  <div css={fieldStyles.inputColumn}>
                    <div css={fieldStyles.inputContainer}>
                      {renderNestedField(nestedField)}

                      {nestedField.desc && (
                        <div css={fieldStyles.description}>
                          <div dangerouslySetInnerHTML={{ __html: nestedField.desc }} />
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default SegmentedField;
