import { css } from '@emotion/react';
import SettingsField from '@Settings/components/SettingsField';
import { type SettingsBlock } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';

interface NotificationBlockProps {
  block: SettingsBlock;
}

const styles = {
  container: css`
    margin-bottom: ${spacing[8]};
  `,

  header: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: ${spacing[16]};
  `,

  title: css`
    ${typography.body()};
    color: ${colorTokens.text.subdued};
  `,

  body: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
    padding: ${spacing[24]};
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[6]};
  `,
};

const NotificationBlock: React.FC<NotificationBlockProps> = ({ block }) => {
  return (
    <div css={styles.container}>
      {block.label && (
        <div css={styles.header}>
          <div css={styles.title}>{block.label}</div>
          <div css={styles.title}>{block.status_label}</div>
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

export default NotificationBlock;
