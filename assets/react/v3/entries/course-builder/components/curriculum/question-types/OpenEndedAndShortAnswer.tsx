import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Alert from '@Atoms/Alert';
import { spacing } from '@Config/styles';

const OpenEndedAndShortAnswer = () => {
  return (
    <div css={styles.optionWrapper}>
      <Alert icon="bulb">{__('No option is necessary for this answer type', 'tutor')}</Alert>
    </div>
  );
};

export default OpenEndedAndShortAnswer;

const styles = {
  optionWrapper: css`
    padding-left: ${spacing[40]};
  `,
};
