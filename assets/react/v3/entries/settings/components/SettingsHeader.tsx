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
    padding: ${spacing[12]} ${spacing[24]};
    position: sticky;
    top: 32px;
    z-index: ${zIndex.header};

    display: grid;
    grid-template-columns: 2fr 3fr 1fr;
  `,
  titleColumn: css``,
  title: css`
    ${typography.heading5('medium')};
    color: ${colorTokens.text.title};
    margin: 0;
  `,
  searchColumn: css`
    position: relative;
  `,
  buttonColumn: css`
    text-align: right;
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

  return (
    <div css={styles.header}>
      <div css={styles.titleColumn}>
        <h4 css={styles.title}>Settings 2 (React)</h4>
      </div>

      <div css={styles.searchColumn}>
        <TextInput
          variant="search"
          placeholder="Search ...⌃⌥ + S or Alt+S for shortcut"
          value={state.searchQuery}
          onChange={handleSearch}
          onFocus={() => setShowSearchResults(true)}
          onBlur={() => setTimeout(() => setShowSearchResults(false), 200)}
        />

        {showSearchResults && (
          <SearchResults results={searchResults} isLoading={isSearching} onClose={() => setShowSearchResults(false)} />
        )}
      </div>

      <div css={styles.buttonColumn}>
        <Button variant="primary" onClick={onSave} disabled={!isDirty || isSaving} loading={isSaving}>
          {isSaving ? 'Saving...' : 'Save Changes'}
        </Button>
      </div>
    </div>
  );
};

export default SettingsHeader;
