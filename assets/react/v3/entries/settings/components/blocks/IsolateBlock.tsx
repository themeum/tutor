import { css } from '@emotion/react';
import SettingsField from '@Settings/components/SettingsField';
import { type SettingsBlock } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';

interface IsolateBlockProps {
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
    gap: ${spacing[8]};
  `,

  blockItem: css`
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[6]};
    padding: ${spacing[24]};
  `,
};

const IsolateBlock: React.FC<IsolateBlockProps> = ({ block }) => {
  return (
    <div css={styles.container}>
      {block.label && (
        <div css={styles.header}>
          <h2 css={styles.title}>{block.label}</h2>
        </div>
      )}

      <div css={styles.body}>
        {/* Regular fields */}
        {block.fields &&
          block.fields.map((field, idx) => (
            <div key={field.key || idx} css={styles.blockItem}>
              <SettingsField field={field} />
            </div>
          ))}

        {/* Fields group */}
        {block.fields_group &&
          block.fields_group.map((field) => (
            <div key={field.key} css={styles.blockItem}>
              <SettingsField field={field} />
            </div>
          ))}
      </div>
    </div>
  );
};

export default IsolateBlock;
