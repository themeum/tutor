import { css } from '@emotion/react';
import { type Addon } from '../services/addons';
import { borderRadius, colorTokens, fontSize, fontWeight, lineHeight, spacing } from '@/v3/shared/config/styles';
import Switch from '@/v3/shared/atoms/Switch';
import { tutorConfig } from '@/v3/shared/config/config';
import Show from '@/v3/shared/controls/Show';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import Tooltip from '@/v3/shared/atoms/Tooltip';
import { __ } from '@wordpress/i18n';

function AddonCard({ addon }: { addon: Addon }) {
  const isTutorPro = !!tutorConfig.tutor_pro_url;

  return (
    <div css={styles.wrapper(!!addon.is_enabled)}>
      <div css={styles.addonTop}>
        <div css={styles.thumb}>
          <img src={addon.thumb_url || addon.url} alt={addon.name} />
        </div>
        <div data-addon-action css={styles.addonAction}>
          <Show
            when={isTutorPro}
            fallback={
              <Tooltip content={__('Available in Pro', 'tutor')}>
                <SVGIcon name="lockStroke" width={24} height={24} />
              </Tooltip>
            }
          >
            <Show when={!addon.plugins_required?.length && !addon.required_settings}>
              <Switch size="small" checked={!!addon.is_enabled} />
            </Show>
          </Show>
        </div>
      </div>
      <div css={styles.addonTitle}>
        <span css={styles.addonTitleText}>{addon.name}</span>
        <Show when={addon.plugins_required?.length}>
          <Tooltip content={addon.plugins_required?.join(', ')}>
            <div css={styles.requiredBadge}>{__('Plugin Required', 'tutor')}</div>
          </Tooltip>
        </Show>
        <Show when={addon.required_settings}>
          <Tooltip content={addon.required_message}>
            <div css={styles.requiredBadge}>{__('Settings Required', 'tutor')}</div>
          </Tooltip>
        </Show>
      </div>
      <div css={styles.addonDescription}>{addon.description}</div>
    </div>
  );
}

export default AddonCard;

const styles = {
  wrapper: (isEnabled: boolean) => css`
    background-color: ${colorTokens.background.white};
    padding: ${spacing[16]} ${spacing[12]};
    border-radius: ${borderRadius[6]};

    ${!isEnabled &&
    css`
      [data-addon-action] {
        display: none;
      }
    `}

    &:hover {
      [data-addon-action] {
        display: block;
      }
    }
  `,
  addonTop: css`
    display: flex;
    align-items: start;
    justify-content: space-between;
  `,
  thumb: css`
    width: 32px;
    height: 32px;
    border-radius: ${borderRadius.circle};
    overflow: hidden;

    img {
      max-width: 100%;
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

    > div {
      flex-shrink: 0;
    }
  `,
  addonTitleText: css`
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
