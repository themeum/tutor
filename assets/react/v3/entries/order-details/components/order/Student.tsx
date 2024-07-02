import { Avatar } from '@Atoms/Avatar';
import { Box, BoxTitle } from '@Atoms/Box';
import { colorTokens, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

function Student() {
  return (
    <Box bordered>
      <BoxTitle separator>{__('Student', 'tutor')}</BoxTitle>
      <div css={styles.content}>
        <Avatar image={'https://placehold.co/100'} name="Hedy Lamarr" />
        <div css={styles.innerContent}>
          <div css={styles.row}>
            <span>Active order number: #19384</span>
            <span>Enrolled courses number: #29389</span>
          </div>
          <div css={styles.row}>
            <h4>Contact information</h4>
            <span>nikolatesla@abc.com</span>
            <span>039483994883</span>
          </div>
          <div css={styles.row}>
            <h4>Billing Address</h4>
            <span>Santiago Nuetro</span>
            <span>Caro, Caroa 2</span>
            <span>Tusca, California 2345, United States</span>
            <span>3948579398</span>
          </div>
        </div>
      </div>
    </Box>
  );
}

export default Student;

const styles = {
  content: css`
		padding: ${spacing[16]} ${spacing[20]};
	`,
  innerContent: css`
		margin-top: ${spacing[8]};
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};
	`,
  row: css`
		display: flex;
		flex-direction: column;
		${typography.caption()};
		color: ${colorTokens.text.subdued};

		h4 {
			font-weight: ${fontWeight.medium};
			color: ${colorTokens.text.primary};
			margin-bottom: ${spacing[4]};
		}
	`,
};
