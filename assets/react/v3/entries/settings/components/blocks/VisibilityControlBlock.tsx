import { css } from '@emotion/react';
import VisibilityControl from '@Settings/components/VisibilityControl';
import { type SettingsBlock } from '@Settings/contexts/SettingsContext';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';

interface VisibilityControlBlockProps {
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
};

const VisibilityControlBlock: React.FC<VisibilityControlBlockProps> = ({ block }) => {
  return (
    <div css={styles.wrapper}>
      {block.label && <h2 css={styles.title}>{block.label}</h2>}

      <VisibilityControl sections={block.sections || []} />
    </div>
  );
};

export default VisibilityControlBlock;
