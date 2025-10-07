import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import React from 'react';

import type { OrderSummaryItem } from '@OrderDetails/services/order';
import coursePlaceholder from '@SharedImages/course-placeholder.png';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { borderRadius, colorTokens, fontWeight, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { formatPrice } from '@TutorShared/utils/currency';
import { isString } from '@TutorShared/utils/types';

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

          <div css={styles.badgeWrapper}>
            {item.type === 'course_plan' && (
              <div>
                {__('Plan:', 'tutor')} {item.plan_info.plan_name}
              </div>
            )}

            {item.type === 'course-bundle' && (
              <div css={styles.bundleCount}>
                {item.total_courses} {__('Courses', 'tutor')}
              </div>
            )}

            {item.coupon_code && (
              <div css={styles.couponTag}>
                <SVGIcon name="tagOutline" width={12} height={12} /> {item.coupon_code}
              </div>
            )}
          </div>

          {item.item_meta_list && item.item_meta_list.length > 0 && (
            <ul css={styles.itemMeta}>
              {item.item_meta_list.map((item) => (
                <li key={item.id}>
                  <strong>{item.meta_key}</strong>: {isString(item.meta_value) ? item.meta_value : ''}
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>
      <div css={styles.right}>
        <Show when={item.sale_price || item.discount_price} fallback={<span>{formatPrice(item.regular_price)}</span>}>
          {(discountedPrice) => (
            <>
              <del>{formatPrice(item.regular_price)}</del>
              <span>{formatPrice(Number(discountedPrice))}</span>
            </>
          )}
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
    object-fit: cover;
    object-position: center;
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
    gap: ${spacing[8]};
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
  badgeWrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
  `,
  couponTag: css`
    ${typography.tiny()};
    background-color: ${colorTokens.surface.wordpress};
    border-radius: ${borderRadius[4]};
    display: flex;
    align-items: center;
    padding: ${spacing[2]} ${spacing[4]};
    gap: ${spacing[4]};
    width: fit-content;
  `,
  itemMeta: css`
    list-style: none;

    strong {
      font-weight: ${fontWeight.medium};
    }
  `,
};
