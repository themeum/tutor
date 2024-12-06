import { css } from '@emotion/react';
import { useAddonContext } from '../contexts/addon-context';
import AddonCard from './AddonCard';
import { spacing } from '@/v3/shared/config/styles';
import { typography } from '@/v3/shared/config/typography';
import { __ } from '@wordpress/i18n';
import Show from '@/v3/shared/controls/Show';

function AddonList() {
  const { addons } = useAddonContext();
  const activeAddons = addons.filter((addon) => !!addon.is_enabled);
  const availableAddons = addons.filter((addon) => !addon.is_enabled);

  return (
    <div css={styles.wrapper}>
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
