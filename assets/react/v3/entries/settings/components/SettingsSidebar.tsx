import { css } from '@emotion/react';
import { useSettings } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';
import { useNavigate } from 'react-router-dom';

const styles = {
  sidebar: css`
    padding: ${spacing[16]} 0 ${spacing[40]} 0;
    position: sticky;
    top: 97px;
  `,

  container: css`
    padding-right: ${spacing[20]};
  `,

  nav: css`
    list-style: none;
    margin: 0;
    padding: 0;
  `,

  navItem: css`
    margin-bottom: ${spacing[4]};
  `,

  navLink: css`
    display: flex;
    align-items: center;
    width: 100%;
    padding: ${spacing[12]} ${spacing[16]};
    background: none;
    border: none;
    border-radius: ${borderRadius[6]};
    ${typography.body()};
    color: ${colorTokens.text.subdued};
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
      background-color: ${colorTokens.color.black[5]};
      color: ${colorTokens.text.title};
    }

    &.active {
      background-color: ${colorTokens.primary[40]};
      color: ${colorTokens.primary.main};
      font-weight: 500;
    }
  `,

  navIcon: css`
    margin-right: ${spacing[8]};
    font-size: 16px;
  `,

  navText: css`
    flex: 1;
    text-align: left;
  `,

  submenu: css`
    list-style: none;
    margin: ${spacing[8]} 0 0 ${spacing[24]};
    padding: 0;
  `,

  submenuItem: css`
    margin-bottom: ${spacing[2]};
  `,

  submenuLink: css`
    display: block;
    width: 100%;
    padding: ${spacing[8]} ${spacing[12]};
    background: none;
    border: none;
    border-radius: ${borderRadius[4]};
    ${typography.caption()};
    color: ${colorTokens.text.hints};
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;

    &:hover {
      background-color: ${colorTokens.color.black[3]};
      color: ${colorTokens.text.subdued};
    }
  `,

  submenuLinkActive: css`
    background-color: ${colorTokens.primary[30]};
    color: ${colorTokens.primary.main};
    font-weight: 500;
  `,
};

const SettingsSidebar: React.FC = () => {
  const { state, dispatch } = useSettings();
  const navigate = useNavigate();

  const handleSectionClick = (sectionKey: string) => {
    dispatch({ type: 'SET_CURRENT_SECTION', payload: sectionKey });
    navigate(`/${sectionKey}`);
  };

  return (
    <div css={styles.sidebar}>
      <div css={styles.container}>
        <ul css={styles.nav}>
          {Object.entries(state.sections).map(([key, section]) => {
            // Check if current section is this section or one of its submenu items
            const isMainSectionActive = state.currentSection === key;
            const isSubmenuParentActive = section.submenu?.some(
              (submenuItem) => submenuItem.slug === state.currentSection,
            );
            const isSectionActive = isMainSectionActive || isSubmenuParentActive;

            return (
              <li key={key} css={styles.navItem}>
                <button
                  type="button"
                  css={[
                    styles.navLink,
                    isSectionActive &&
                      css`
                        background-color: ${colorTokens.primary[40]};
                        color: ${colorTokens.primary.main};
                        font-weight: 500;
                      `,
                  ]}
                  onClick={() => handleSectionClick(key)}
                >
                  <span css={styles.navIcon} className={`tutor-icon ${section.icon}`}></span>
                  <span css={styles.navText}>{section.label}</span>
                </button>

                {/* Render submenu if exists */}
                {section.submenu && (
                  <ul css={styles.submenu}>
                    {section.submenu.map((submenuItem) => {
                      const isSubmenuActive = state.currentSection === submenuItem.slug;

                      return (
                        <li key={submenuItem.slug} css={styles.submenuItem}>
                          <button
                            type="button"
                            css={[styles.submenuLink, isSubmenuActive && styles.submenuLinkActive]}
                            onClick={() => handleSectionClick(submenuItem.slug)}
                          >
                            {submenuItem.label}
                          </button>
                        </li>
                      );
                    })}
                  </ul>
                )}
              </li>
            );
          })}
        </ul>
      </div>
    </div>
  );
};

export default SettingsSidebar;
