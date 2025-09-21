import { css } from '@emotion/react';
import SettingsField from '@Settings/components/SettingsField';
import { type SettingsBlock as SettingsBlockType } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';

interface SettingsBlockProps {
  block: SettingsBlockType;
}

const styles = {
  block: css`
    margin-bottom: ${spacing[32]};
  `,

  uniformBlock: css`
    padding: ${spacing[24]};
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[8]};
  `,

  isolateBlock: css`
    padding: ${spacing[20]};
    background-color: ${colorTokens.color.black[2]};
    border-radius: ${borderRadius[6]};
    border-left: 4px solid ${colorTokens.primary.main};
  `,

  header: css`
    margin-bottom: ${spacing[16]};
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

const SettingsBlock: React.FC<SettingsBlockProps> = ({ block }) => {
  const getBlockStyles = () => {
    const baseStyles = [styles.block];

    if (block.block_type === 'uniform') {
      baseStyles.push(styles.uniformBlock);
    } else if (block.block_type === 'isolate') {
      baseStyles.push(styles.isolateBlock);
    }

    return baseStyles;
  };

  return (
    <div css={getBlockStyles()}>
      {block.label && (
        <div css={styles.header}>
          <h2 css={styles.title}>{block.label}</h2>
        </div>
      )}

      <div css={styles.body}>
        {/* Regular fields */}
        {block.fields && block.fields.map((field) => <SettingsField key={field.key} field={field} />)}

        {/* Fields group */}
        {block.fields_group && block.fields_group.map((field) => <SettingsField key={field.key} field={field} />)}
      </div>
    </div>
  );
};

export default SettingsBlock;
