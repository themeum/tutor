import { css } from '@emotion/react';
import BlockSegments from '@Settings/components/BlockSegments';
import SettingsField from '@Settings/components/SettingsField';
import VisibilityControl from '@Settings/components/VisibilityControl';
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

  // Handle visibility control blocks
  if (block.block_type === 'visibility-control') {
    return (
      <div css={getBlockStyles()}>
        <VisibilityControl sections={block.sections || []} blockLabel={block.label} />
      </div>
    );
  }

  // Handle custom blocks (like certificate templates)
  if (block.block_type === 'custom') {
    return (
      <div css={getBlockStyles()}>
        {block.label && (
          <div css={styles.header}>
            <h2 css={styles.title}>{block.label}</h2>
          </div>
        )}
        <div css={styles.body}>
          <div
            css={css`
              padding: ${spacing[16]};
              background-color: ${colorTokens.color.warning[50]};
              border: 1px solid ${colorTokens.color.warning[200]};
              border-radius: ${borderRadius[6]};
              ${typography.caption()};
              color: ${colorTokens.text.subdued};
            `}
          >
            Custom block: {block.template_path ? `Template at ${block.template_path}` : 'No template path specified'}
          </div>
        </div>
      </div>
    );
  }

  // If block has segments, render them with tabs
  if (block.segments && Array.isArray(block.segments) && block.segments.length > 0) {
    return (
      <div css={getBlockStyles()}>
        <BlockSegments segments={block.segments} blockLabel={block.label} />
      </div>
    );
  }

  // Regular block rendering
  return (
    <div css={getBlockStyles()}>
      {block.label && (
        <div css={styles.header}>
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

export default SettingsBlock;
