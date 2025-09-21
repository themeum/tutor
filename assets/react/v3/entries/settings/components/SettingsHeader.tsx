import { css } from '@emotion/react';
import SearchResults from '@Settings/components/SearchResults';
import { useSettings } from '@Settings/contexts/SettingsContext';
import { useSettingsSearch } from '@Settings/hooks/useSettings';
import Button from '@TutorShared/atoms/Button';
import TextInput from '@TutorShared/atoms/TextInput';
import { colorTokens, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React, { useState } from 'react';

interface SettingsHeaderProps {
  onSave: () => void;
  isSaving: boolean;
  isDirty: boolean;
}

const styles = {
  header: css`
    background-color: ${colorTokens.background.white};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    padding: ${spacing[16]} ${spacing[24]};
    position: sticky;
    top: 0;
    z-index: ${zIndex.header};
  `,

  row: css`
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: ${spacing[16]};
    max-width: 1200px;
    margin: 0 auto;
  `,

  titleColumn: css`
    flex: 0 0 auto;
    margin-bottom: ${spacing[16]};

    @media (min-width: 768px) {
      flex: 0 0 25%;
      margin-bottom: 0;
    }

    @media (min-width: 992px) {
      flex: 0 0 30%;
    }
  `,

  title: css`
    ${typography.heading4('medium')};
    color: ${colorTokens.text.title};
    margin: 0;
  `,

  searchColumn: css`
    flex: 1;
    margin-bottom: ${spacing[24]};
    position: relative;

    @media (min-width: 768px) {
      flex: 0 0 41.666667%;
      margin-bottom: 0;
    }

    @media (min-width: 1200px) {
      flex: 0 0 50%;
    }
  `,

  searchWrapper: css`
    position: relative;
  `,

  buttonColumn: css`
    flex: 0 0 auto;
    display: flex;
    justify-content: flex-end;

    @media (min-width: 768px) {
      flex: 0 0 25%;
    }

    @media (min-width: 992px) {
      flex: 0 0 16.666667%;
    }
  `,
};

const SettingsHeader: React.FC<SettingsHeaderProps> = ({ onSave, isSaving, isDirty }) => {
  const { state, dispatch } = useSettings();
  const { searchSettings, searchResults, isSearching } = useSettingsSearch();
  const [showSearchResults, setShowSearchResults] = useState(false);

  const handleSearch = (value: string) => {
    dispatch({ type: 'SET_SEARCH_QUERY', payload: value });

    if (value.length > 2) {
      searchSettings(value);
      setShowSearchResults(true);
    } else {
      setShowSearchResults(false);
    }
  };

  const handleKeyDown = (keyName: string, event: React.KeyboardEvent<HTMLInputElement>) => {
    // Handle Ctrl+Alt+S or Alt+S shortcut
    if ((event.ctrlKey && event.altKey && event.key === 's') || (event.altKey && event.key === 's')) {
      event.preventDefault();
      event.target.focus();
    }
  };

  return (
    <div css={styles.header}>
      <div css={styles.row}>
        <div css={styles.titleColumn}>
          <h1 css={styles.title}>Settings 2 (React)</h1>
        </div>

        <div css={styles.searchColumn}>
          <div css={styles.searchWrapper}>
            <TextInput
              variant="search"
              placeholder="Search ...⌃⌥ + S or Alt+S for shortcut"
              value={state.searchQuery}
              onChange={handleSearch}
              onKeyDown={handleKeyDown}
              onFocus={() => setShowSearchResults(true)}
              onBlur={() => setTimeout(() => setShowSearchResults(false), 200)}
              autoFocus
            />

            {showSearchResults && (
              <SearchResults
                results={searchResults}
                isLoading={isSearching}
                onClose={() => setShowSearchResults(false)}
              />
            )}
          </div>
        </div>

        <div css={styles.buttonColumn}>
          <Button variant="primary" onClick={onSave} disabled={!isDirty || isSaving} loading={isSaving}>
            {isSaving ? 'Saving...' : 'Save Changes'}
          </Button>
        </div>
      </div>
    </div>
  );
};

export default SettingsHeader;
