import { css } from '@emotion/react';
import { useAddonContext } from '../contexts/addon-context';
import AddonCard from './AddonCard';
import { spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { __ } from '@wordpress/i18n';
import Show from '@TutorShared/controls/Show';
import FreeBanner from './FreeBanner';
import { tutorConfig } from '@TutorShared/config/config';
import EmptyState from './EmptyState';

function AddonList() {
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const { addons, searchTerm } = useAddonContext();
  const activeAddons = addons.filter((addon) => !!addon.is_enabled);
  const availableAddons = addons.filter((addon) => !addon.is_enabled);

  if (searchTerm.length && addons.length === 0) {
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
          {activeAddons.map((addon) => {
            return <AddonCard key={addon.base_name} addon={addon} />;
          })}
        </div>
      </Show>

      <Show when={availableAddons.length}>
        <h5 css={styles.addonListTitle}>{__('Available Addons', 'tutor')}</h5>
        <div css={styles.addonListWrapper}>
          {availableAddons.map((addon) => {
            return <AddonCard key={addon.base_name} addon={addon} />;
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
