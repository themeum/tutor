import { css } from '@emotion/react';
import { type SettingsField } from '@Settings/contexts/SettingsContext';
import Switch from '@TutorShared/atoms/Switch';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';
import { fieldStyles } from './fieldStyles';

interface CheckGroupNestedProps {
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
    gap: ${spacing[16]};
  `,

  header: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,

  title: css`
    ${typography.body()};
    color: ${colorTokens.text.title};
    margin: 0;
    font-weight: 500;
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

  fieldsContainer: css`
    padding: ${spacing[20]};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[8]};
    background-color: ${colorTokens.color.black[2]};

    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: ${spacing[24]};
  `,

  fieldWrapper: css`
    padding: ${spacing[16]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[6]};
    background-color: ${colorTokens.background.white};

    &:hover {
      border-color: ${colorTokens.stroke.hover};
    }
  `,
};

const CheckGroupNested: React.FC<CheckGroupNestedProps> = ({ field, value, onChange }) => {
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

  // Render toggle_single field (only type supported in checkgroup)
  const renderNestedField = (nestedField: SettingsField) => {
    const fieldValue = getNestedFieldValue(nestedField.key);
    const value = fieldValue ?? nestedField.default ?? '';
    const isChecked = value === 'on' || value === true || value === '1';

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const handleChange = (newValue: any) => handleNestedFieldChange(nestedField.key, newValue);

    return <Switch label={nestedField.label} checked={isChecked} onChange={handleChange} labelPosition="right" />;
  };

  if (!field.group_options || !Array.isArray(field.group_options)) {
    return null;
  }

  return (
    <div css={styles.container}>
      <div>
        <label css={fieldStyles.label}>{field.label}</label>
        {field.label_title && <div css={fieldStyles.labelTitle}>{field.label_title}</div>}
        {field.desc && (
          <div css={fieldStyles.description}>
            <div dangerouslySetInnerHTML={{ __html: field.desc }} />
          </div>
        )}
      </div>

      <div css={styles.fieldsContainer}>
        {field.group_options.map((nestedField) => (
          <div key={nestedField.key} css={styles.fieldWrapper}>
            {renderNestedField(nestedField)}
            {/* <div css={nestedFieldStyles.fieldRow}>
              <div css={nestedFieldStyles.labelColumn}>
                <div css={nestedFieldStyles.labelContainer}>
                  <label css={nestedFieldStyles.label}>{nestedField.label}</label>
                  {nestedField.label_title && <div css={nestedFieldStyles.labelTitle}>{nestedField.label_title}</div>}
                </div>
              </div>

              <div css={nestedFieldStyles.inputColumn}>
                <div css={nestedFieldStyles.inputContainer}>
                  {renderNestedField(nestedField)}
                </div>
              </div>
            </div> */}
          </div>
        ))}
      </div>
    </div>
  );
};

export default CheckGroupNested;
