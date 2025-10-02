import { type SettingsField } from '@Settings/contexts/SettingsContext';
import CheckBox from '@TutorShared/atoms/CheckBox';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { css } from '@emotion/react';
import React from 'react';
import { fieldStyles } from './fieldStyles';

interface CheckboxFieldProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
}

const styles = {
  container: (isVertical: boolean) => css`
    display: flex;
    flex-direction: ${isVertical ? 'column' : 'row'};
    gap: ${spacing[16]};
    flex-wrap: wrap;
  `,

  optionItem: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,

  description: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
    margin-top: ${spacing[4]};

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

const CheckboxField: React.FC<CheckboxFieldProps> = ({ field, value, onChange }) => {
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

  const isVertical = field.type === 'checkbox_vertical';

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
          <div css={styles.container(isVertical)}>
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
                    <CheckBox
                      checked={isSelected}
                      onChange={(checked) => handleChange(optionValue, checked)}
                      label={optionLabel}
                    />
                    {optionDesc && (
                      <div css={styles.description}>
                        <div dangerouslySetInnerHTML={{ __html: optionDesc }} />
                      </div>
                    )}
                  </div>
                );
              })}
          </div>
        </div>
      </div>
    </div>
  );
};

export default CheckboxField;
