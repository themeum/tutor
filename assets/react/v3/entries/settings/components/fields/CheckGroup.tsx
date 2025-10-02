import { type SettingsField } from '@Settings/contexts/SettingsContext';
import Button from '@TutorShared/atoms/Button';
import Switch from '@TutorShared/atoms/Switch';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { css } from '@emotion/react';
import React from 'react';
import { fieldStyles } from './fieldStyles';

interface CheckGroupProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
}

const styles = {
  wrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,

  selectAllContainer: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
  `,

  optionsContainer: (isVertical: boolean) => css`
    display: flex;
    flex-direction: ${isVertical ? 'column' : 'row'};
    gap: ${isVertical ? spacing[12] : spacing[16]};
    ${!isVertical &&
    css`
      flex-wrap: wrap;
    `}
  `,

  optionItem: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
  `,

  optionDescription: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin-left: ${spacing[32]};

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

  summary: css`
    padding: ${spacing[12]};
    background-color: ${colorTokens.color.black[5]};
    border-radius: ${borderRadius[6]};
    border: 1px solid ${colorTokens.stroke.divider};
  `,

  summaryText: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,
};

const CheckGroup: React.FC<CheckGroupProps> = ({ field, value, onChange }) => {
  const selectedValues = Array.isArray(value) ? value : [];

  const handleChange = (optionValue: string, isChecked: boolean) => {
    let newValues;

    if (isChecked) {
      newValues = [...selectedValues, optionValue];
    } else {
      newValues = selectedValues.filter((v) => v !== optionValue);
    }

    onChange(newValues);
  };

  const handleSelectAll = () => {
    if (!field.options) return;

    const allValues = Object.keys(field.options);
    const allSelected = allValues.every((value) => selectedValues.includes(value));

    if (allSelected) {
      // Deselect all
      onChange([]);
    } else {
      // Select all
      onChange(allValues);
    }
  };

  const isVertical = field.type === 'checkgroup_vertical' || field.type === 'checkgroup';
  const allValues = field.options ? Object.keys(field.options) : [];
  const allSelected = allValues.length > 0 && allValues.every((value) => selectedValues.includes(value));
  const someSelected = selectedValues.length > 0 && !allSelected;

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
          <div css={styles.wrapper}>
            {/* Select All / Deselect All Button */}
            {field.options && Object.keys(field.options).length > 1 && (
              <div css={styles.selectAllContainer}>
                <Button variant="text" size="small" onClick={handleSelectAll}>
                  {allSelected ? 'Deselect All' : 'Select All'}
                  {someSelected && ' (Some Selected)'}
                </Button>
              </div>
            )}

            {/* Switch Group */}
            <div css={styles.optionsContainer(isVertical)}>
              {field.options &&
                Object.entries(field.options).map(([optionValue, optionData]) => {
                  const isSelected = selectedValues.includes(optionValue);
                  const optionLabel =
                    typeof optionData === 'object' && optionData !== null
                      ? // eslint-disable-next-line @typescript-eslint/no-explicit-any
                        (optionData as any).label
                      : String(optionData);
                  const optionDesc =
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    typeof optionData === 'object' && optionData !== null ? (optionData as any).desc : null;

                  return (
                    <div key={optionValue} css={styles.optionItem}>
                      <Switch
                        checked={isSelected}
                        onChange={(checked) => handleChange(optionValue, checked)}
                        label={optionLabel}
                        labelPosition="right"
                        size="regular"
                      />

                      {optionDesc && (
                        <div css={styles.optionDescription}>
                          <div dangerouslySetInnerHTML={{ __html: optionDesc }} />
                        </div>
                      )}
                    </div>
                  );
                })}
            </div>

            {/* Summary */}
            {selectedValues.length > 0 && (
              <div css={styles.summary}>
                <p css={styles.summaryText}>
                  Selected: {selectedValues.length} of {allValues.length} options
                </p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default CheckGroup;
