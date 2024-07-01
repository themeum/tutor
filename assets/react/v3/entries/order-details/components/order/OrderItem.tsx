import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { css } from '@emotion/react';
import coursePlaceholder from '@OrderPublic/images/course-placeholder.png';
import type { OrderSummaryItem } from '@OrderServices/order';
import { isDefined } from '@Utils/types';
import React from 'react';

interface OrderItemProps extends React.HTMLAttributes<HTMLDivElement> {
  item: OrderSummaryItem;
}

export const OrderItem = React.forwardRef<HTMLDivElement, OrderItemProps>(({ className, item }, ref) => {
  return (
    <div className={className} ref={ref} css={styles.wrapper}>
      <div css={styles.left}>
        <img src={item.image || coursePlaceholder} css={styles.image} alt="course item" />
        <div>
          <p css={styles.title}>{item.title}</p>

          <Show when={item.discount}>
            {(discount) => (
              <div css={styles.discount}>
                <SVGIcon name="tagOutline" width={16} height={16} />
                <p>{discount.name}</p>
                <p>(-{discount.value})</p>
              </div>
            )}
          </Show>

          {/* {item.type === 'bundle' && (
					<div css={styles.bundleCount}>{item.total_courses} {__('Courses', 'tutor')}</div>
				)} */}
        </div>
      </div>
      <div css={styles.right}>
        <Show when={isDefined(item.discounted_price)} fallback={<span>{item.regular_price}</span>}>
          <del>{item.regular_price}</del>
          <span>{item.discounted_price}</span>
        </Show>
      </div>
    </div>
  );
});

const styles = {
  wrapper: css`
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: ${spacing[12]} ${spacing[20]};

		:not(:last-of-type) {
			border-bottom: 1px solid ${colorTokens.stroke.divider};
		}
	`,
  image: css`
		width: 48px;
		height: 48px;
		border-radius: ${borderRadius[4]};
	`,
  discount: css`
		display: flex;
		align-items: center;
		gap: ${spacing[4]};
		${typography.small()};
		color: ${colorTokens.text.subdued};
	`,
  bundleCount: css`
		${typography.small()};
		color: ${colorTokens.text.hints};
	`,
  left: css`
		display: flex;
		gap: ${spacing[16]};
	`,
  right: css`
		display: flex;
		gap: ${spacing[32]};
		${typography.caption()};
		color: ${colorTokens.text.primary};

		del {
			color: ${colorTokens.text.subdued};
		}
	`,
  title: css`
		${typography.caption()};
		color: ${colorTokens.brand.blue};
	`,
};
