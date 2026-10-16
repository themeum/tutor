import { __ } from '@wordpress/i18n';

import { TutorBadge, type Variant } from '@TutorShared/atoms/TutorBadge';

import type { OrderStatus } from '@OrderDetails/services/order';

const badgeMap: Record<OrderStatus, { label: string; type: Variant }> = {
  incomplete: { label: __('Incomplete', 'tutor'), type: 'critical' },
  completed: { label: __('Completed', 'tutor'), type: 'success' },
  cancelled: { label: __('Cancelled', 'tutor'), type: 'secondary' },
  trash: { label: __('Trash', 'tutor'), type: 'critical' },
  pending: { label: __('Pending', 'tutor'), type: 'secondary' },
};

export function OrderBadge({ status }: { status: OrderStatus }) {
  return (
    <TutorBadge variant={badgeMap[status]?.type || badgeMap.incomplete.type}>
      {badgeMap[status]?.label || badgeMap.incomplete.label}
    </TutorBadge>
  );
}
