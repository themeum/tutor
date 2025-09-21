import { type SettingsField } from '@Settings/contexts/SettingsContext';
import Switch from '@TutorShared/atoms/Switch';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { css } from '@emotion/react';
import React from 'react';

interface ToggleSingleProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
}

const styles = {
  container: css`
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
  `,

  labelContainer: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
  `,

  mainLabel: css`
    ${typography.body()};
    color: ${colorTokens.text.title};
    margin: 0;
  `,

  toggleLabel: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,
};

const ToggleSingle: React.FC<ToggleSingleProps> = ({ field, value, onChange }) => {
  const isChecked = value === 'on' || value === true || value === '1';

  const handleChange = (checked: boolean) => {
    onChange(checked ? 'on' : 'off');
  };

  return (
    <div css={styles.container}>
      <div css={styles.labelContainer}>
        <p css={styles.mainLabel}>{field.label}</p>
        {field.label_title && <p css={styles.toggleLabel}>{field.label_title}</p>}
      </div>

      <Switch checked={isChecked} onChange={handleChange} size="regular" labelPosition="left" />
    </div>
  );
};

export default ToggleSingle;
