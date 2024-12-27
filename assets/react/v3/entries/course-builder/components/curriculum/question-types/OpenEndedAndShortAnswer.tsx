import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Alert from '@Atoms/Alert';
import { Breakpoint, spacing } from '@Config/styles';

const OpenEndedAndShortAnswer = () => {
  return (
    <div css={styles.optionWrapper}>
      <Alert icon="bulb">{__('No options are necessary for this question type', 'tutor')}</Alert>
    </div>
  );
};

export default OpenEndedAndShortAnswer;

const styles = {
  optionWrapper: css`
    padding-left: ${spacing[40]};

    ${Breakpoint.smallMobile} {
      padding-left: ${spacing[8]};
    }
  `,
};
