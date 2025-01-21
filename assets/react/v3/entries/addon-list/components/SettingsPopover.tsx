import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import config from '@TutorShared/config/config';
import { borderRadius, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { type Addon } from '../services/addons';

interface SettingsPopoverProps {
  addon: Addon;
  handleClose: () => void;
}

function SettingsPopover({ addon, handleClose }: SettingsPopoverProps) {
  return (
    <div css={styles.wrapper}>
      <div css={styles.iconWrapper}>
        <SVGIcon name="settingsError" width={42} height={38} />
      </div>

      <p css={styles.content}>{addon.required_title}</p>

      <div css={styles.buttonWrapper}>
        <Button variant="text" size="small" onClick={handleClose}>
          {__('Cancel', 'tutor')}
        </Button>
        <Button
          variant="secondary"
          size="small"
          onClick={() => {
            handleClose();
            window.open(config.MONETIZATION_SETTINGS_URL, '_blank', 'noopener');
          }}
        >
          {__('Go to Settings', 'tutor')}
        </Button>
      </div>
    </div>
  );
}
export default SettingsPopover;

const styles = {
  wrapper: css`
    min-width: 300px;
    background-color: ${colorTokens.background.white};
    border-radius: ${borderRadius.card};
    box-shadow: ${shadow.popover};
    padding: ${spacing[24]} ${spacing[16]} ${spacing[16]};
  `,
  iconWrapper: css`
    text-align: center;
    margin-bottom: ${spacing[24]};
  `,
  content: css`
    ${typography.body('medium')};
    margin-bottom: ${spacing[20]};
  `,
  buttonWrapper: css`
    display: flex;
    justify-content: end;
    gap: ${spacing[8]};
  `,
};
