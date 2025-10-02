import { css } from '@emotion/react';
import SettingsField from '@Settings/components/SettingsField';
import { type SettingsBlock } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';

interface ColorPickerBlockProps {
  block: SettingsBlock;
}

const styles = {
  container: css`
    background-color: ${colorTokens.background.white};
    padding: ${spacing[24]};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[8]};
  `,

  header: css`
    margin-bottom: ${spacing[16]};
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
  `,

  title: css`
    ${typography.body()};
    color: ${colorTokens.text.title};
    margin: 0;
  `,

  body: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
};

const ColorPickerBlock: React.FC<ColorPickerBlockProps> = ({ block }) => {
  return (
    <div css={styles.container}>
      {block.label && <h2 css={styles.title}>{block.label}</h2>}

      <div css={styles.body}>
        {/* Regular fields */}
        {block.fields && block.fields.map((field, idx) => <SettingsField key={field.key || idx} field={field} />)}

        {/* Fields group */}
        {block.fields_group && block.fields_group.map((field) => <SettingsField key={field.key} field={field} />)}
      </div>
    </div>
  );
};

export default ColorPickerBlock;
