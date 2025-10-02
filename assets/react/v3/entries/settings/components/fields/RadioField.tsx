import { type SettingsField } from '@Settings/contexts/SettingsContext';
import Radio from '@TutorShared/atoms/Radio';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { css } from '@emotion/react';
import React from 'react';
import { fieldStyles } from './fieldStyles';

interface RadioFieldProps {
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
    gap: ${spacing[24]};
  `,
  container: (isVertical: boolean, isHorizontalFull: boolean) => css`
    display: flex;
    flex-direction: ${isVertical ? 'column' : 'row'};
    gap: ${spacing[16]};
    ${!isVertical &&
    css`
      flex-wrap: ${isHorizontalFull ? 'wrap' : 'nowrap'};
    `}
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

const RadioField: React.FC<RadioFieldProps> = ({ field, value, onChange }) => {
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    onChange(e.target.value);
  };

  const isVertical = field.type === 'radio_vertical';
  const isHorizontalFull = field.type === 'radio_horizontal_full';

  return (
    <div css={styles.wrapper}>
      <div css={fieldStyles.labelContainer}>
        <label css={fieldStyles.label}>{field.label}</label>
        {field.label_title && <div css={fieldStyles.labelTitle}>{field.label_title}</div>}
        {field.desc && (
          <div css={fieldStyles.description}>
            <div dangerouslySetInnerHTML={{ __html: field.desc }} />
          </div>
        )}
      </div>

      <div css={styles.container(isVertical, isHorizontalFull)}>
        {field.options &&
          Object.entries(field.options).map(([optionValue, optionData]) => {
            const isSelected = value === optionValue;
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
                <Radio
                  name={field.key}
                  value={optionValue}
                  checked={isSelected}
                  onChange={handleChange}
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
  );
};

export default RadioField;
