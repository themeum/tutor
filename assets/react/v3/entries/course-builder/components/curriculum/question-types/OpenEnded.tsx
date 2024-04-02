import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Alert from '@Atoms/Alert';

const OpenEnded = () => {
  return (
    <div css={styles.optionWrapper}>
      <Alert icon="bulb">{__('No option is necessary for this answer type', 'tutor')}</Alert>
    </div>
  );
};

export default OpenEnded;

const styles = {
  optionWrapper: css`
    padding-left: 42px; // This is not is our design system.
  `,
};
