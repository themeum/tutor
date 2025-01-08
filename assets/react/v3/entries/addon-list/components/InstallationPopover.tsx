import Button from '@TutorShared/atoms/Button';
import { borderRadius, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useInstallPlugin, type Addon } from '../services/addons';
import Show from '@TutorShared/controls/Show';
import woocommerceFavicon from '@SharedImages/woocommerce-favicon.webp';

interface Plugin {
  name: string;
  thumb?: string;
}

interface InstallationPopoverProps {
  addon: Addon;
  handleClose: () => void;
  handleSuccess: () => void;
}

function InstallationPopover({ addon, handleClose, handleSuccess }: InstallationPopoverProps) {
  const installPlugin = useInstallPlugin();

  async function handleActivatePlugin() {
    const pluginSlug = Object.keys(addon.depend_plugins)[0];
    if (pluginSlug) {
      const response = await installPlugin.mutateAsync({
        plugin_slug: pluginSlug,
      });

      if (response.status_code === 200) {
        handleSuccess();
      }
    }
  }

  return (
    <div css={styles.wrapper}>
      <p css={styles.content}>
        {addon.required_pro_plugin
          ? __('Install the following plugin(s) to enable this addon.', 'tutor')
          : sprintf(__("The following plugin will be installed upon activating the '%s'.", 'tutor'), addon.name)}
      </p>

      <div css={styles.pluginsWrapper}>
        <For
          each={
            (addon.plugins_required?.map((item) => ({ name: item, thumb: addon.thumb_url })) as Plugin[]) ??
            ([] as Plugin[])
          }
        >
          {(item) => (
            <div css={styles.pluginItem}>
              <div css={styles.pluginThumb}>
                <img src={item.name === 'WooCommerce' ? woocommerceFavicon : item.thumb} alt={item.name} />
              </div>
              <div css={styles.pluginName}>{item.name}</div>
            </div>
          )}
        </For>
      </div>

      <div css={styles.buttonWrapper}>
        <Button variant="text" size="small" onClick={handleClose}>
          {__('Cancel', 'tutor')}
        </Button>
        <Show when={!addon.required_pro_plugin}>
          <Button variant="secondary" size="small" onClick={handleActivatePlugin} loading={installPlugin.isPending}>
            {__('Activate', 'tutor')}
          </Button>
        </Show>
      </div>
    </div>
  );
}
export default InstallationPopover;

const styles = {
  wrapper: css`
    min-width: 300px;
    background-color: ${colorTokens.background.white};
    border-radius: ${borderRadius.card};
    box-shadow: ${shadow.popover};
    padding: ${spacing[16]};

    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  content: css`
    ${typography.body('medium')};
    margin: 0px;
  `,
  pluginsWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  pluginItem: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    padding: ${spacing[12]};
    background-color: ${colorTokens.surface.wordpress};
    border-radius: ${borderRadius[6]};
  `,
  pluginThumb: css`
    height: 32px;
    width: 32px;
    overflow: hidden;
    border-radius: ${borderRadius.circle};

    img {
      max-width: 100%;
    }
  `,
  pluginName: css`
    ${typography.caption('medium')};
  `,
  buttonWrapper: css`
    display: flex;
    justify-content: end;
    gap: ${spacing[8]};
  `,
};
