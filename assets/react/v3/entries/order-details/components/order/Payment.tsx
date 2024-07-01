import { Box, BoxTitle } from '@Atoms/Box';
import Button from '@Atoms/Button';
import { colorTokens, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { Badge } from '@OrderAtoms/Badge';
import { styleUtils } from '@Utils/style-utils';
import { __ } from '@wordpress/i18n';

function Payment() {
  return (
    <Box bordered>
      <BoxTitle>
        <div css={styles.paymentTitle}>
          <span>{__('Payment', 'tutor')}</span>
          <Badge variant="warning">Pending</Badge>
        </div>
      </BoxTitle>
      <div css={styles.content}>
        <Box bordered>
          <div css={styles.item({ action: 'regular' })}>
            <div>Subtotal</div>
            <div>3 Items</div>
            <div>$1300.00</div>
          </div>
          <div css={styles.item({ action: 'regular' })}>
            <button
              type="button"
              css={styles.discountButton}
              onClick={() => alert('@TODO: Will be implemented later.')}
            >
              Add discount
            </button>
            <div>-</div>
            <div>-$0.00</div>
          </div>
          <div css={styles.item({ action: 'regular' })}>
            <div>Estimated tax</div>
            <div>15%</div>
            <div>$23.00</div>
          </div>
          <div css={styles.item({ action: 'bold' })}>
            <div>Total Paid</div>
            <div />
            <div>$1323.00</div>
          </div>
          <div css={styles.separator} />
          <div css={styles.item({ action: 'destructive' })}>
            <div>Refunded</div>
            <div>Reason: Manual refund</div>
            <div>-$500.00</div>
          </div>
          <div css={styles.item({ action: 'destructive' })}>
            <div />
            <div>Reason: -</div>
            <div>-$500.00</div>
          </div>
          <div css={styles.item({ action: 'bold' })}>
            <div>Net payment</div>
            <div />
            <div>$323.00</div>
          </div>
        </Box>

        <div css={styles.markAsPaid}>
          <Button variant="primary" size="small" isOutlined onClick={() => alert('@TODO: will be implemented later.')}>
            {__('Mark as paid', 'tutor')}
          </Button>
        </div>
      </div>
    </Box>
  );
}

export default Payment;

const styles = {
  content: css`
		padding: ${spacing[12]} ${spacing[20]} ${spacing[16]} ${spacing[20]};
	`,
  paymentTitle: css`
		display: flex;
		gap: ${spacing[4]};
		align-items: center;
	`,
  markAsPaid: css`
		margin-top: ${spacing[12]};
		text-align: right;
	`,
  item: ({ action = 'regular' }: { action: 'regular' | 'bold' | 'destructive' }) => css`
		${typography.caption()};
		display: grid;
		grid-template-columns: 120px 1fr auto;
		align-items: center;
		min-height: 32px;
		color: ${colorTokens.text.primary};
		padding-inline: ${spacing[12]};

		${
      action === 'bold' &&
      css`
			font-weight: ${fontWeight.bold};
		`
    }

		${
      action === 'destructive' &&
      css`
			& > div:first-of-type {
				color: ${colorTokens.text.error};
			}
		`
    }

		& > div:nth-of-type(2) {
			color: ${colorTokens.text.subdued};
		}

		:first-of-type {
			padding-top: ${spacing[4]};
		}

		:last-of-type {
			padding-bottom: ${spacing[4]};
		}
	`,
  separator: css`
		height: 1px;
		width: 100%;
		background-color: ${colorTokens.stroke.divider};
		margin-block: ${spacing[12]};
	`,
  discountButton: css`
		${styleUtils.resetButton};
		${typography.small('medium')};
		color: ${colorTokens.brand.blue};
	`,
};
