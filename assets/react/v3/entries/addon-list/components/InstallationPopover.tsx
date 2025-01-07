import Button from '@TutorShared/atoms/Button';
import { borderRadius, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

interface Plugin {
  name: string;
  thumb?: string;
}

interface InstallationPopoverProps {
  addonName: string;
  handleClose: () => void;
  plugins: Plugin[];
}

function InstallationPopover({ addonName, plugins, handleClose }: InstallationPopoverProps) {
  return (
    <div css={styles.wrapper}>
      <p css={styles.content}>
        {sprintf(__("The following plugins will be installed upon activating the '%s'.", 'tutor'), addonName)}
      </p>

      <div css={styles.pluginsWrapper}>
        <For each={plugins}>
          {(item) => (
            <div css={styles.pluginItem}>
              <div css={styles.pluginThumb}>
                <img src={item.thumb} alt={item.name} />
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
        <Button variant="secondary" size="small">
          {__('Activate', 'tutor')}
        </Button>
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
