import { type SettingsField } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { css } from '@emotion/react';
import React from 'react';

interface TextareaFieldProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
}

const styles = {
  textarea: css`
    ${typography.body()};
    width: 100%;
    min-height: 100px;
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[6]};
    background-color: ${colorTokens.background.white};
    color: ${colorTokens.text.title};
    resize: vertical;
    transition: all 0.2s ease;

    &:focus {
      outline: none;
      border-color: ${colorTokens.stroke.brand};
      box-shadow: 0 0 0 2px ${colorTokens.primary[40]};
    }

    &:hover:not(:focus) {
      border-color: ${colorTokens.stroke.hover};
    }

    &::placeholder {
      color: ${colorTokens.text.hints};
    }

    &:disabled {
      background-color: ${colorTokens.background.disable};
      color: ${colorTokens.text.disable};
      cursor: not-allowed;
    }
  `,
};
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const TextareaField: React.FC<TextareaFieldProps> = ({ field, value, onChange }) => {
  const handleChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    onChange(e.target.value);
  };

  return (
    <textarea css={styles.textarea} value={value || ''} onChange={handleChange} placeholder="Enter text..." rows={4} />
  );
};

export default TextareaField;
