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
    margin-bottom: ${spacing[32]};
    padding: ${spacing[24]};
    background: linear-gradient(135deg, ${colorTokens.color.black[2]} 0%, ${colorTokens.background.white} 100%);
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[8]};
    position: relative;
    overflow: hidden;

    &::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(
        90deg,
        ${colorTokens.primary.main} 0%,
        ${colorTokens.color.success[100]} 25%,
        ${colorTokens.color.warning[100]} 50%,
        ${colorTokens.color.danger[100]} 75%,
        ${colorTokens.primary.main} 100%
      );
    }
  `,

  header: css`
    margin-bottom: ${spacing[16]};
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
  `,

  colorIcon: css`
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(
      45deg,
      ${colorTokens.primary.main} 0%,
      ${colorTokens.color.success[100]} 50%,
      ${colorTokens.color.warning[100]} 100%
    );
    border: 2px solid ${colorTokens.background.white};
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  `,

  title: css`
    ${typography.heading6('medium')};
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
      {block.label && (
        <div css={styles.header}>
          <div css={styles.colorIcon} />
          <h2 css={styles.title}>{block.label}</h2>
        </div>
      )}

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
