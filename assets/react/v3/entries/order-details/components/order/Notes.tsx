import { useOrderContext } from '@OrderDetails/contexts/order-context';
import { Box, BoxTitle } from '@TutorShared/atoms/Box';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

function Notes() {
  const {
    order: { note },
  } = useOrderContext();
  return (
    <Box bordered css={styleUtils.boxReset}>
      <BoxTitle separator>{__('Notes', 'tutor')}</BoxTitle>
      <div css={styles.content}>{note?.length ? note : __('No notes', 'tutor')}</div>
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
