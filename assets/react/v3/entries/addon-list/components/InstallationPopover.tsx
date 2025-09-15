import woocommerceFavicon from '@SharedImages/woocommerce-favicon.webp';
import Button from '@TutorShared/atoms/Button';
import { borderRadius, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import { useInstallPlugin, type Addon } from '../services/addons';

interface Plugin {
  name: string;
  thumb?: string;
}

interface InstallationPopoverProps {
  addon: Addon;
  handleClose: () => void;
  handleSuccess: () => void;
}

// Note: It's presumed that only one plugin will be installed but multiple can be activated.
function InstallationPopover({ addon, handleClose, handleSuccess }: InstallationPopoverProps) {
  const [installingIdx, setInstallingIdx] = useState<number | null>(null);
  const [percentage, setPercentage] = useState<number>(10);

  const installPlugin = useInstallPlugin();

  let interval: NodeJS.Timeout;

  const handleActivatePlugin = async () => {
    let isSuccessAll = true;
    for (const [idx, item] of Object.keys(addon.depend_plugins ?? []).entries()) {
      if (!addon.is_dependents_installed && idx === 0) {
        setInstallingIdx(idx);
      }

      const response = await installPlugin.mutateAsync({
        plugin_slug: item,
      });

      if (response.status_code !== 200) {
        isSuccessAll = false;
        break;
      }

      if (response.status_code === 200 && idx === 0 && !addon.is_dependents_installed) {
        clearInterval(interval);
        interval = setInterval(() => {
          setPercentage((prevSeconds) => {
            if (prevSeconds < 100) {
              return prevSeconds + 1;
            } else {
              clearInterval(interval);
              return prevSeconds;
            }
          });
        }, 10);
      }
    }

    if (addon.is_dependents_installed && isSuccessAll) {
      handleSuccess();
    }
  };

  useEffect(() => {
    if (percentage === 100) {
      handleSuccess();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [percentage]);

  useEffect(() => {
    if (installingIdx === 0) {
      // eslint-disable-next-line react-hooks/exhaustive-deps
      interval = setInterval(() => {
        setPercentage((prevSeconds) => {
          if (prevSeconds < 77) {
            return prevSeconds + 1;
          } else {
            clearInterval(interval);
            return prevSeconds;
          }
        });
      }, 200);
    }

    return () => clearInterval(interval);
  }, [installingIdx]);

  const getContent = () => {
    if (addon.required_pro_plugin && !addon.is_dependents_installed) {
      return __('Install the following plugin(s) to enable this addon.', 'tutor');
    } else if (addon.is_dependents_installed) {
      // translators: %s is the addon name
      return sprintf(__("The following plugin(s) will be activated upon activating the '%s'.", 'tutor'), addon.name);
    } else {
      // translators: %s is the addon name
      return sprintf(__("The following plugin(s) will be installed upon activating the '%s'.", 'tutor'), addon.name);
    }
  };

  return (
    <div css={styles.wrapper}>
      <p css={styles.content}>{getContent()}</p>

      <div css={styles.pluginsWrapper}>
        <For
          each={
            (addon.plugins_required?.map((item) => ({ name: item, thumb: addon.thumb_url })) as Plugin[]) ??
            ([] as Plugin[])
          }
        >
          {(item, idx) => (
            <div>
              <Show when={installingIdx === idx}>
                <div css={styles.progressWrapper}>
                  <div css={styles.progressContent}>
                    <span css={styles.progressStep}>
                      {!addon.is_dependents_installed && percentage < 78
                        ? __('Installing...', 'tutor')
                        : __('Activating...', 'tutor')}
                    </span>
                    <span css={styles.progressPercentage}>{percentage}%</span>
                  </div>
                  <div css={styles.progressBar(percentage)}>
                    <span></span>
                  </div>
                </div>
              </Show>
              <div css={styles.pluginItem(installingIdx === idx)}>
                <div css={styles.pluginThumb}>
                  <img src={item.name === 'WooCommerce' ? woocommerceFavicon : item.thumb} alt={item.name} />
                </div>
                <div css={styles.pluginName}>{item.name}</div>
              </div>
            </div>
          )}
        </For>
      </div>

      <div css={styles.buttonWrapper}>
        <Button variant="text" size="small" onClick={handleClose}>
          {__('Cancel', 'tutor')}
        </Button>
        <Show when={!addon.required_pro_plugin || addon.is_dependents_installed}>
          <Button
            variant="secondary"
            size="small"
            onClick={handleActivatePlugin}
            loading={installPlugin.isPending || (percentage > 10 && percentage < 100)}
          >
            {addon.is_dependents_installed ? __('Activate', 'tutor') : __('Install & Activate', 'tutor')}
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
  pluginItem: (loading: boolean) => css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    padding: ${spacing[12]};
    background-color: ${colorTokens.surface.wordpress};
    border-radius: ${borderRadius[6]};

    ${loading &&
    css`
      border-top-left-radius: 0px;
      border-top-right-radius: 0px;
    `}
  `,
  pluginThumb: css`
    height: 32px;
    width: 32px;
    overflow: hidden;

    img {
      max-width: 100%;
    }
  `,
  pluginName: css`
    ${typography.caption('medium')};
  `,
  progressWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
  `,
  progressContent: css`
    display: flex;
    justify-content: space-between;
  `,
  progressStep: css`
    ${typography.small('regular')};
  `,
  progressPercentage: css`
    ${typography.tiny('bold')};
    border-radius: ${borderRadius[12]};
    padding: ${spacing[2]} ${spacing[4]};
    background-color: #ecfdf3;
    color: #087112;
  `,
  progressBar: (percentage: number) => css`
    height: 6px;
    background-color: #dddfe6;
    border-top-left-radius: ${borderRadius[50]};
    border-top-right-radius: ${borderRadius[50]};
    overflow: hidden;

    span {
      display: block;
      height: 6px;
      background-color: ${colorTokens.brand.blue};
      width: ${percentage}%;
      transition: width 0.25s ease;
    }
  `,
  buttonWrapper: css`
    display: flex;
    justify-content: end;
    gap: ${spacing[8]};
  `,
};
