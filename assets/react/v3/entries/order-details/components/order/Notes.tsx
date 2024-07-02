import { Box, BoxTitle } from '@Atoms/Box';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

function Notes() {
  return (
    <Box bordered>
      <BoxTitle separator>{__('Notes', 'tutor')}</BoxTitle>
      <div css={styles.content}>
        I'm a student. Along with my university fee I'm unable to pay full payment. please give me a discount for my
        enrollment.
      </div>
    </Box>
  );
}

export default Notes;

const styles = {
  content: css`
		${typography.caption()};
		color: ${colorTokens.text.subdued};
		padding: ${spacing[16]} ${spacing[20]};
	`,
};
