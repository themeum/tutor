import { css } from '@emotion/react';
import { type SettingsBlock } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';

interface CustomBlockProps {
  block: SettingsBlock;
}

const styles = {
  container: css`
    padding: ${spacing[24]};
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[8]};
  `,

  header: css`
    margin-bottom: ${spacing[16]};
  `,

  title: css`
    ${typography.heading6('medium')};
    color: ${colorTokens.text.title};
    margin: 0;
  `,

  placeholder: css`
    padding: ${spacing[16]};
    background-color: ${colorTokens.color.warning[50]};
    border: 1px solid ${colorTokens.color.warning[100]};
    border-radius: ${borderRadius[6]};
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    text-align: center;
  `,

  templatePath: css`
    ${typography.caption('medium')};
    color: ${colorTokens.text.hints};
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    background-color: ${colorTokens.color.black[5]};
    padding: ${spacing[8]} ${spacing[12]};
    border-radius: ${borderRadius[4]};
    margin-top: ${spacing[8]};
    word-break: break-all;
  `,
};

const CustomBlock: React.FC<CustomBlockProps> = ({ block }) => {
  return (
    <div css={styles.container}>
      {block.label && (
        <div css={styles.header}>
          <h2 css={styles.title}>{block.label}</h2>
        </div>
      )}

      <div css={styles.placeholder}>
        <div>Custom Block Template</div>
        <div>This block uses a custom PHP template for rendering.</div>

        {block.template_path && <div css={styles.templatePath}>Template: {block.template_path}</div>}

        {block.placement && <div css={styles.templatePath}>Placement: {block.placement}</div>}
      </div>
    </div>
  );
};

export default CustomBlock;
