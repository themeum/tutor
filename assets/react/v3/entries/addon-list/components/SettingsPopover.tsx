import Button from '@TutorShared/atoms/Button';
import { borderRadius, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import config from '@TutorShared/config/config';
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
    padding: ${spacing[16]};

    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  content: css`
    ${typography.body('medium')};
    margin: 0px;
  `,
  buttonWrapper: css`
    display: flex;
    justify-content: end;
    gap: ${spacing[8]};
  `,
};
