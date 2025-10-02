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
  wrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
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
  blockItem: css`
    &:not(:last-of-type) {
      border-bottom: 1px solid ${colorTokens.stroke.divider};
      padding-bottom: ${spacing[16]};
    }
  `,
};

const UniformBlock: React.FC<UniformBlockProps> = ({ block }) => {
  return (
    <div css={styles.wrapper}>
      {block.label && <h2 css={styles.title}>{block.label}</h2>}

      <div css={styles.body}>
        {/* Regular fields */}
        {block.fields &&
          block.fields.map((field, idx) => (
            <div css={styles.blockItem} key={field.key || idx}>
              <SettingsField field={field} />
            </div>
          ))}

        {/* Fields group */}
        {block.fields_group &&
          block.fields_group.map((field) => (
            <div css={styles.blockItem} key={field.key}>
              <SettingsField field={field} />
            </div>
          ))}
      </div>
    </div>
  );
};

export default UniformBlock;
