import { css } from '@emotion/react';
import VisibilityControl from '@Settings/components/VisibilityControl';
import { type SettingsBlock } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';

interface VisibilityControlBlockProps {
  block: SettingsBlock;
}

const styles = {
  container: css`
    /* margin-bottom: ${spacing[32]}; */
  `,

  header: css`
    margin-bottom: ${spacing[16]};
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
  `,

  visibilityIcon: css`
    width: 20px;
    height: 20px;
    border-radius: ${borderRadius[4]};
    background-color: ${colorTokens.color.warning[100]};
    display: flex;
    align-items: center;
    justify-content: center;
    color: ${colorTokens.color.warning[60]};
    font-size: 12px;
    font-weight: 600;

    &::after {
      content: '👁';
    }
  `,

  title: css`
    ${typography.body()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,
};

const VisibilityControlBlock: React.FC<VisibilityControlBlockProps> = ({ block }) => {
  return (
    <div css={styles.container}>
      {block.label && (
        <div css={styles.header}>
          <h2 css={styles.title}>{block.label}</h2>
        </div>
      )}

      <VisibilityControl sections={block.sections || []} />
    </div>
  );
};

export default VisibilityControlBlock;
