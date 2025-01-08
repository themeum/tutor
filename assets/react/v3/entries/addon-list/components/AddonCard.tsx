import { css } from '@emotion/react';
import { useEnableDisableAddon, type Addon } from '../services/addons';
import { borderRadius, colorTokens, fontSize, fontWeight, lineHeight, spacing } from '@TutorShared/config/styles';
import Switch from '@TutorShared/atoms/Switch';
import { tutorConfig } from '@TutorShared/config/config';
import Show from '@TutorShared/controls/Show';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Tooltip from '@TutorShared/atoms/Tooltip';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';
import { useAddonContext } from '../contexts/addon-context';
import { useToast } from '@TutorShared/atoms/Toast';
import InstallationPopover from './InstallationPopover';
import Popover from '@TutorShared/molecules/Popover';
import { AnimationType } from '@TutorShared/hooks/useAnimation';

function AddonCard({ addon }: { addon: Addon }) {
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const { showToast } = useToast();
  const { addons, updatedAddons, setUpdatedAddons } = useAddonContext();

  const popoverRef = useRef(null);
  const [isOpen, setIsOpen] = useState(false);
  const [isPluginInstalled, setIsPluginInstalled] = useState(false);

  const [isChecked, setIsChecked] = useState(!!addon.is_enabled);
  const [isTooltipVisible, setIsTooltipVisible] = useState(false);

  const enableDisableAddon = useEnableDisableAddon();

  const handleAddonChange = async (checked: boolean) => {
    setIsChecked(checked);

    const addonObject = {} as Record<string, number>;

    addons.forEach((item) => {
      const alreadyUpdatedItem = updatedAddons.find((updatedItem) => updatedItem.basename === item.basename);
      if (item.basename === addon.basename) {
        addonObject[item.basename as string] = checked ? 1 : 0;
      } else if (alreadyUpdatedItem) {
        addonObject[item.basename as string] = alreadyUpdatedItem.is_enabled ? 1 : 0;
      } else {
        addonObject[item.basename as string] = item.is_enabled ? 1 : 0;
      }
    });

    const response = await enableDisableAddon.mutateAsync({
      addonFieldNames: JSON.stringify(addonObject),
    });

    if (response.success) {
      setUpdatedAddons([
        ...updatedAddons.filter((item) => item.base_name === addon.base_name),
        { ...addon, is_enabled: checked ? 1 : 0 },
      ]);
    } else {
      setIsChecked(!checked);
      showToast({ type: 'danger', message: response.data?.message ?? __('Something went wrong!', 'tutor') });
    }
  };

  const hasToolTip = !isTutorPro || addon.required_settings;

  return (
    <div
      ref={popoverRef}
      css={styles.wrapper}
      onMouseEnter={() => hasToolTip && setIsTooltipVisible(true)}
      onMouseLeave={() => hasToolTip && setIsTooltipVisible(false)}
    >
      <div css={styles.addonTop}>
        <div css={styles.thumb}>
          <img src={addon.thumb_url || addon.url} alt={addon.name} />
        </div>
        <div css={styles.addonAction}>
          <Show
            when={isTutorPro}
            fallback={
              <Tooltip content={__('Available in Pro', 'tutor')} visible={isTooltipVisible}>
                <SVGIcon name="lockStroke" width={24} height={24} />
              </Tooltip>
            }
          >
            <Show
              when={addon.required_settings}
              fallback={
                <Switch
                  size="small"
                  checked={isChecked}
                  onChange={(checked) => {
                    if (addon.plugins_required?.length && !isPluginInstalled) {
                      setIsOpen(true);
                    } else {
                      handleAddonChange(checked);
                    }
                  }}
                  disabled={enableDisableAddon.isPending}
                />
              }
            >
              <Tooltip content={addon.required_message} visible={isTooltipVisible}>
                <div css={styles.requiredBadge}>{__('Settings Required', 'tutor')}</div>
              </Tooltip>
            </Show>
          </Show>
        </div>
      </div>
      <div css={styles.addonTitle}>
        {addon.name}
        <Show when={addon.is_new}>
          <div css={styles.newBadge}>{__('New', 'tutor')}</div>
        </Show>
      </div>
      <div css={styles.addonDescription}>{addon.description}</div>
      <Popover
        triggerRef={popoverRef}
        isOpen={isOpen}
        closePopover={() => setIsOpen(false)}
        animationType={AnimationType.slideUp}
        closeOnEscape={false}
        arrow="middle"
        hideArrow
      >
        <InstallationPopover
          addon={addon}
          handleClose={() => setIsOpen(false)}
          handleSuccess={() => {
            setIsOpen(false);
            handleAddonChange(true);
            setIsPluginInstalled(true);
          }}
        />
      </Popover>
    </div>
  );
}

export default AddonCard;

const styles = {
  wrapper: css`
    background-color: ${colorTokens.background.white};
    padding: ${spacing[16]};
    border-radius: ${borderRadius[6]};
  `,
  addonTop: css`
    display: flex;
    align-items: start;
    justify-content: space-between;
  `,
  thumb: css`
    width: 32px;
    height: 32px;
    background-color: ${colorTokens.background.hover};
    border-radius: ${borderRadius[4]};
    overflow: hidden;

    img {
      max-width: 100%;
      border-radius: ${borderRadius.circle};
    }
  `,
  addonAction: css`
    svg {
      color: ${colorTokens.icon.default};
    }
  `,
  addonTitle: css`
    font-size: ${fontSize[16]};
    line-height: ${lineHeight[26]};
    font-weight: ${fontWeight.semiBold};
    color: ${colorTokens.text.primary};
    margin-top: ${spacing[16]};
    margin-bottom: ${spacing[4]};

    display: flex;
    align-items: center;
    gap: ${spacing[8]};
  `,
  newBadge: css`
    min-width: fit-content;
    background-color: ${colorTokens.brand.blue};
    color: ${colorTokens.text.white};
    border-radius: ${borderRadius[4]};
    font-size: ${fontSize[11]};
    line-height: ${lineHeight[15]};
    font-weight: ${fontWeight.semiBold};
    padding: ${spacing[2]} ${spacing[8]} 1px;
    text-transform: uppercase;
  `,
  requiredBadge: css`
    min-width: fit-content;
    background-color: ${colorTokens.icon.warning};
    color: ${colorTokens.text.primary};
    border-radius: ${borderRadius[4]};
    font-size: ${fontSize[11]};
    line-height: ${lineHeight[16]};
    font-weight: ${fontWeight.semiBold};
    padding: 1px ${spacing[8]};
  `,
  addonDescription: css`
    font-size: ${fontSize[14]};
    line-height: ${lineHeight[22]};
    color: ${colorTokens.text.subdued};
  `,
};
