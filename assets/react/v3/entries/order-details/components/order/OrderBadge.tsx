import { TutorBadge, type Variant } from "@Atoms/TutorBadge";
import type { OrderStatus } from "@OrderServices/order";
import { __ } from "@wordpress/i18n";

const badgeMap: Record<OrderStatus, { label: string; type: Variant }> = {
  incomplete: { label: __('Incomplete', 'tutor'), type: 'critical' },
  completed: { label: __('Completed', 'tutor'), type: 'success' },
  cancelled: { label: __('Cancelled', 'tutor'), type: 'secondary' },
  trash: { label: __('Trash', 'tutor'), type: 'critical' },
};

export function OrderBadge({ status }: { status: OrderStatus }) {
  return <TutorBadge variant={badgeMap[status].type}>{badgeMap[status].label}</TutorBadge>;
}