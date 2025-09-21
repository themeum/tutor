import { type SettingsField } from '@Settings/contexts/SettingsContext';
import CheckBox from '@TutorShared/atoms/CheckBox';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { css } from '@emotion/react';
import React from 'react';

interface CheckboxNotificationProps {
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

  notificationGroup: css`
    padding: ${spacing[16]};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[8]};
    background-color: ${colorTokens.color.black[2]};
  `,

  groupTitle: css`
    ${typography.body()};
    color: ${colorTokens.text.title};
    margin: 0 0 ${spacing[12]} 0;
    font-weight: 500;
  `,

  optionsContainer: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,

  optionItem: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    padding: ${spacing[8]};
    border-radius: ${borderRadius[4]};
    transition: background-color 0.2s ease;

    &:hover {
      background-color: ${colorTokens.color.black[3]};
    }
  `,

  optionLabel: css`
    ${typography.caption()};
    color: ${colorTokens.text.title};
    margin: 0;
    flex: 1;
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
    <div css={styles.container}>
      <div css={styles.notificationGroup}>
        <h4 css={styles.groupTitle}>{field.label}</h4>

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
                <div css={styles.optionItem}>
                  <CheckBox checked={isSelected} onChange={(checked) => handleOptionChange(optionKey, checked)} />
                  <span css={styles.optionLabel}>{optionLabel}</span>
                </div>

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
    </div>
  );
};

export default CheckboxNotification;
