import type { PaymentStatus } from '@OrderDetails/services/order';
import { TutorBadge, type Variant } from '@TutorShared/atoms/TutorBadge';
import { __ } from '@wordpress/i18n';

const badgeMap: Record<PaymentStatus, { label: string; type: Variant }> = {
  paid: { label: __('Paid', 'tutor'), type: 'success' },
  failed: { label: __('Failed', 'tutor'), type: 'critical' },
  'partially-refunded': { label: __('Partially refunded', 'tutor'), type: 'secondary' },
  refunded: { label: __('Refunded', 'tutor'), type: 'critical' },
  unpaid: { label: __('Unpaid', 'tutor'), type: 'warning' },
  pending: { label: __('Pending', 'tutor'), type: 'warning' },
};

export function PaymentBadge({ status }: { status: PaymentStatus }) {
  return <TutorBadge variant={badgeMap[status]?.type ?? 'secondary'}>{badgeMap[status]?.label ?? status}</TutorBadge>;
}
