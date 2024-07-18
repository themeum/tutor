import { Box, BoxTitle } from '@Atoms/Box';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useOrderContext } from '@OrderContexts/order-context';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

function Notes() {
	const {
		order: { note },
	} = useOrderContext();
	return (
		<Box bordered css={styleUtils.boxReset}>
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
