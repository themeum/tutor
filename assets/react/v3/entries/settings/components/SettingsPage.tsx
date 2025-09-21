import { css } from '@emotion/react';
import SettingsContent from '@Settings/components/SettingsContent';
import SettingsHeader from '@Settings/components/SettingsHeader';
import SettingsSidebar from '@Settings/components/SettingsSidebar';
import { useSettings } from '@Settings/contexts/SettingsContext';
import { useSettingsData, useSettingsMutation } from '@Settings/hooks/useSettings';
import LoadingSpinner from '@TutorShared/atoms/LoadingSpinner';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import React, { useEffect } from 'react';

const styles = {
  adminWrap: css`
    min-height: 100vh;
    background-color: ${colorTokens.background.default};
  `,

  loadingContainer: css`
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
  `,

  adminContainer: css`
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 ${spacing[16]};
  `,

  row: css`
    display: flex;
    flex-wrap: wrap;
    margin: 0 -${spacing[8]};

    @media (min-width: 992px) {
      margin: 0;
    }
  `,

  sidebarColumn: css`
    width: 100%;
    padding: 0 ${spacing[8]};
    border-right: 1px solid ${colorTokens.stroke.divider};

    @media (min-width: 576px) {
      width: 16.666667%;
    }

    @media (min-width: 992px) {
      width: 24%;
      padding: 0;
    }
  `,

  contentColumn: css`
    width: 100%;
    padding: 0 ${spacing[8]};

    @media (min-width: 576px) {
      width: 83.333333%;
    }

    @media (min-width: 992px) {
      width: 75%;
      padding: 0;
    }
  `,
};

const SettingsPage: React.FC = () => {
  const url = new URL(window.location.href);
  const section = url.searchParams.get('tab_page');

  const { state, dispatch } = useSettings();
  const { isLoading } = useSettingsData();
  const { saveSettings, isSaving } = useSettingsMutation();

  useEffect(() => {
    if (section && section !== state.currentSection) {
      dispatch({ type: 'SET_CURRENT_SECTION', payload: section });
    }
  }, [section, state.currentSection, dispatch]);

  const handleSave = () => {
    saveSettings(state.values);
  };

  if (isLoading) {
    return (
      <div css={styles.adminWrap}>
        <div css={styles.loadingContainer}>
          <LoadingSpinner />
        </div>
      </div>
    );
  }

  return (
    <div css={styles.adminWrap}>
      <SettingsHeader onSave={handleSave} isSaving={isSaving} isDirty={state.isDirty} />

      <div css={styles.adminContainer}>
        <div css={styles.row}>
          <div css={styles.sidebarColumn}>
            <SettingsSidebar />
          </div>

          <div css={styles.contentColumn}>
            <SettingsContent />
          </div>
        </div>
      </div>
    </div>
  );
};

export default SettingsPage;
