import { type Addon } from '@AddonList/services/addons';
import { css } from '@emotion/react';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import { tutorConfig } from '@TutorShared/config/config';
import { spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { __ } from '@wordpress/i18n';
import { useAddonContext } from '../contexts/addon-context';
import AddonCard from './AddonCard';
import EmptyState from './EmptyState';
import FreeBanner from './FreeBanner';

function AddonList() {
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const { addons, searchTerm, isLoading } = useAddonContext();

  const addonsList = addons.filter((addon) => {
    return addon.name.toLowerCase().includes(searchTerm.toLowerCase());
  });

  const { activeAddons, availableAddons } = addonsList.reduce(
    (addonGroups, addon) => {
      if (addon.is_enabled && !addon.required_settings) {
        addonGroups.activeAddons.push(addon);
      } else {
        addonGroups.availableAddons.push(addon);
      }
      return addonGroups;
    },
    { activeAddons: [] as Addon[], availableAddons: [] as Addon[] },
  );

  if (isLoading) {
    return <LoadingSection />;
  }

  if (searchTerm.length && addonsList.length === 0) {
    return <EmptyState />;
  }

  return (
    <div css={styles.wrapper}>
      <Show when={!isTutorPro}>
        <FreeBanner />
      </Show>

      <Show when={activeAddons.length}>
        <h5 css={styles.addonListTitle}>{__('Active Addons', 'tutor')}</h5>
        <div css={styles.addonListWrapper}>
          {activeAddons.map((addon, key) => {
            return <AddonCard key={key} addon={addon} />;
          })}
        </div>
      </Show>

      <Show when={availableAddons.length}>
        <h5 css={styles.addonListTitle}>{__('Available Addons', 'tutor')}</h5>
        <div css={styles.addonListWrapper}>
          {availableAddons.map((addon, key) => {
            return <AddonCard key={key} addon={addon} />;
          })}
        </div>
      </Show>
    </div>
  );
}

export default AddonList;

const styles = {
  wrapper: css`
    margin-top: ${spacing[40]};
  `,
  addonListWrapper: css`
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(275px, 1fr));
    gap: ${spacing[24]};
    margin-bottom: ${spacing[40]};
  `,
  addonListTitle: css`
    ${typography.heading5('medium')};
    margin-bottom: ${spacing[16]};
  `,
};
