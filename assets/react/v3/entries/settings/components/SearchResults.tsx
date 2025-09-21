import { css } from '@emotion/react';
import { useSettings } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';

interface SearchResult {
  key: string;
  label: string;
  section_label: string;
  section_slug: string;
  block_label: string;
  desc?: string;
}

interface SearchResultsProps {
  results?: { fields: SearchResult[] };
  isLoading: boolean;
  onClose: () => void;
}

const styles = {
  container: css`
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: ${zIndex.dropdown};
    margin-top: ${spacing[4]};
  `,

  content: css`
    padding: ${spacing[16]};
  `,

  loadingContainer: css`
    display: flex;
    align-items: center;
    justify-content: center;
    gap: ${spacing[8]};
    ${typography.body()};
    color: ${colorTokens.text.subdued};
  `,

  noResults: css`
    text-align: center;
    ${typography.body()};
    color: ${colorTokens.text.subdued};
  `,

  resultsContainer: css`
    max-height: 300px;
    overflow-y: auto;
  `,

  resultItem: css`
    padding: ${spacing[12]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    cursor: pointer;
    transition: background-color 0.2s ease;

    &:hover {
      background-color: ${colorTokens.color.black[5]};
    }

    &:last-child {
      border-bottom: none;
    }
  `,

  resultTitle: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.title};
    margin: 0 0 ${spacing[4]} 0;
  `,

  resultPath: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0 0 ${spacing[4]} 0;
  `,

  resultDescription: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
    margin: 0;

    p {
      margin: 0;
    }
  `,

  loadingSpinner: css`
    width: 16px;
    height: 16px;
    border: 2px solid ${colorTokens.stroke.default};
    border-top: 2px solid ${colorTokens.primary.main};
    border-radius: 50%;
    animation: spin 1s linear infinite;

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }
  `,
};

const SearchResults: React.FC<SearchResultsProps> = ({ results, isLoading, onClose }) => {
  const { dispatch } = useSettings();

  const handleResultClick = (result: SearchResult) => {
    dispatch({ type: 'SET_CURRENT_SECTION', payload: result.section_slug });
    const url = new URL(window.location.href);
    url.searchParams.set('tab_page', result.section_slug);
    window.history.pushState({}, '', url.toString());
    onClose();
  };

  if (isLoading) {
    return (
      <div css={styles.container}>
        <div css={styles.content}>
          <div css={styles.loadingContainer}>
            <div css={styles.loadingSpinner} />
            <span>Searching...</span>
          </div>
        </div>
      </div>
    );
  }

  if (!results || !results.fields || results.fields.length === 0) {
    return (
      <div css={styles.container}>
        <div css={styles.content}>
          <div css={styles.noResults}>No results found</div>
        </div>
      </div>
    );
  }

  return (
    <div css={styles.container}>
      <div css={styles.resultsContainer}>
        {results.fields.slice(0, 10).map((result, index) => (
          <div key={`${result.key}-${index}`} css={styles.resultItem} onClick={() => handleResultClick(result)}>
            <div css={styles.resultTitle}>{result.label}</div>
            <div css={styles.resultPath}>
              {result.section_label}
              {result.block_label && ` > ${result.block_label}`}
            </div>
            {result.desc && (
              <div css={styles.resultDescription}>
                <div dangerouslySetInnerHTML={{ __html: result.desc.substring(0, 100) + '...' }} />
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
};

export default SearchResults;
