import { css } from '@emotion/react';
import SettingsField from '@Settings/components/SettingsField';
import { type SettingsBlock } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';

interface UniformBlockProps {
  block: SettingsBlock;
}

const styles = {
  container: css`
    /* margin-bottom: ${spacing[32]}; */
  `,

  header: css`
    margin-bottom: ${spacing[16]};
  `,

  title: css`
    ${typography.body()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,

  body: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};

    padding: ${spacing[24]};
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[6]};
  `,
};

const UniformBlock: React.FC<UniformBlockProps> = ({ block }) => {
  return (
    <div css={styles.container}>
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

export default UniformBlock;
