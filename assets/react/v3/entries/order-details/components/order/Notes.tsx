import { Box, BoxTitle } from '@Atoms/Box';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { useOrderContext } from '@OrderContexts/order-context';
import { __ } from '@wordpress/i18n';

function Notes() {
  const {
    order: { note },
  } = useOrderContext();
  return (
    <Box bordered>
      <BoxTitle separator>{__('Notes', 'tutor')}</BoxTitle>
      <div css={styles.content}>{note}</div>
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
