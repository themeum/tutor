import { type SettingsField } from '@Settings/contexts/SettingsContext';
import CheckBox from '@TutorShared/atoms/CheckBox';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { css } from '@emotion/react';
import React from 'react';
import { fieldStyles } from './fieldStyles';

interface CheckboxNotificationProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
}

const styles = {
  container: css`
    &:not(:last-of-type) {
      border-bottom: 1px solid ${colorTokens.stroke.divider};
      padding-bottom: ${spacing[16]};
    }
  `,

  optionsContainer: css`
    display: flex;
    flex-shrink: 0;
    gap: ${spacing[12]};
  `,

  optionDescription: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: ${spacing[4]} 0 0 ${spacing[24]};
  `,
};

const CheckboxNotification: React.FC<CheckboxNotificationProps> = ({ field, value, onChange }) => {
  const selectedValues = Array.isArray(value) ? value : [];

  const handleOptionChange = (optionKey: string, isChecked: boolean) => {
    let newValues;

    if (isChecked) {
      newValues = [...selectedValues, optionKey];
    } else {
      newValues = selectedValues.filter((v) => v !== optionKey);
    }

    onChange(newValues);
  };

  if (!field.options) {
    return null;
  }

  return (
    <div css={[fieldStyles.fieldRow, styles.container]}>
      <div css={fieldStyles.labelContainer}>
        <label css={fieldStyles.label}>{field.label}</label>
        {field.label_title && <div css={fieldStyles.labelTitle}>{field.label_title}</div>}
        {field.desc && (
          <div css={fieldStyles.description}>
            <div dangerouslySetInnerHTML={{ __html: field.desc }} />
          </div>
        )}
      </div>

      <div css={styles.optionsContainer}>
        {Object.entries(field.options).map(([optionKey, optionData]) => {
          const isSelected = selectedValues.includes(optionKey);
          const optionLabel =
            typeof optionData === 'object' && optionData !== null
              ? // eslint-disable-next-line @typescript-eslint/no-explicit-any
                (optionData as any).label || optionKey
              : String(optionData);
          const optionDesc =
            typeof optionData === 'object' && optionData !== null
              ? // eslint-disable-next-line @typescript-eslint/no-explicit-any
                (optionData as any).desc
              : null;

          return (
            <div key={optionKey}>
              <CheckBox
                label={optionLabel}
                checked={isSelected}
                onChange={(checked) => handleOptionChange(optionKey, checked)}
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
    </div>
  );
};

export default CheckboxNotification;
