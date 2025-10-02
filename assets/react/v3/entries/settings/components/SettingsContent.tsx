import { css } from '@emotion/react';
import SettingsBlock from '@Settings/components/SettingsBlock';
import { useSettings } from '@Settings/contexts/SettingsContext';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';

const styles = {
  container: css`
    padding: ${spacing[32]};
  `,

  notFound: css`
    text-align: center;
    padding: ${spacing[32]};
  `,

  header: css`
    margin-bottom: ${spacing[32]};
  `,

  title: css`
    ${typography.heading5('medium')};
    color: ${colorTokens.text.title};
    margin: 0;
  `,

  description: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,

  body: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,
};

const SettingsContent: React.FC = () => {
  const { state } = useSettings();

  // Find current section - could be a main section or a submenu item
  const findCurrentSection = () => {
    // First check if it's a main section
    if (state.sections[state.currentSection]) {
      return state.sections[state.currentSection];
    }

    // If not found, search in submenu items
    for (const section of Object.values(state.sections)) {
      if (section.submenu) {
        const submenuItem = section.submenu.find((submenu) => submenu.slug === state.currentSection);
        if (submenuItem) {
          return submenuItem;
        }
      }
    }

    return null;
  };

  const currentSection = findCurrentSection();

  if (!currentSection) {
    return (
      <div css={styles.notFound}>
        <p>Section not found</p>
      </div>
    );
  }

  return (
    <div css={styles.container}>
      <div css={styles.header}>
        <h5 css={styles.title}>{currentSection.label}</h5>
        {/* {currentSection.desc && <div css={styles.description}>{currentSection.desc}</div>} */}
      </div>

      <div css={styles.body}>
        {(() => {
          // Ensure blocks is always treated as an array
          const blocks = Array.isArray(currentSection.blocks)
            ? currentSection.blocks
            : Object.values(currentSection.blocks || {});

          return blocks.map((block, index) => <SettingsBlock key={`${block.slug || index}-${index}`} block={block} />);
        })()}
      </div>
    </div>
  );
};

export default SettingsContent;
