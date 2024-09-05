import { Avatar } from '@Atoms/Avatar';
import { Box, BoxTitle } from '@Atoms/Box';
import { colorTokens, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { useOrderContext } from '@OrderContexts/order-context';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

function Student() {
  const { order } = useOrderContext();
  const { student } = order;
  return (
    <Box bordered css={styleUtils.boxReset}>
      <BoxTitle separator>{__('Student', 'tutor')}</BoxTitle>
      <div css={styles.content}>
        <Avatar image={student.image} name={student.name} />
        <div css={styles.innerContent}>
          <div css={styles.row}>
            {/* @TODO: need confirmation */}
            <span>{sprintf(__('Active order number: #%s', 'tutor'), order.id)}</span>
            <span>
              {sprintf(
                __('Enrolled course numbers: %s', 'tutor'),
                order.items.map((course) => `#${course.id}`).join(', '),
              )}
            </span>
          </div>
          <div css={styles.row}>
            <h4>{__('Contact information', 'tutor')}</h4>
            <span>{student.email}</span>
            <span>{student.phone}</span>
          </div>
          {isDefined(order.student.billing_address) && (
            <div css={styles.row}>
              <h4>{__('Billing Address', 'tutor')}</h4>
              <Show when={order.student.billing_address?.address}>
                <span>{student.billing_address.address}</span>
              </Show>
              <Show when={order.student.billing_address?.city}>
                <span>{student.billing_address.city}</span>
              </Show>
              <span>
                {[student.billing_address.state, student.billing_address.zip_code, student.billing_address.country]
                  .filter(isDefined)
                  .join(', ')}
              </span>
              <Show when={order.student.billing_address?.phone}>
                <span>{student.billing_address.phone}</span>
              </Show>
            </div>
          )}
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
