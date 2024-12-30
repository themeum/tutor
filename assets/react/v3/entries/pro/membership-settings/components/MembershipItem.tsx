import Button from '@/v3/shared/atoms/Button';
import { type MembershipPlan } from '../services/memberships';
import MembershipModal from './modals/MembershipModal';
import { __ } from '@wordpress/i18n';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import { useModal } from '@/v3/shared/components/modals/Modal';

interface MembershipItemProps {
  data: MembershipPlan;
}

export default function MembershipItem({ data }: MembershipItemProps) {
  const { showModal } = useModal();
  return (
    <div>
      <h5>{data.plan_name}</h5>
      <div>${data.regular_price} per month</div>
      <Button
        onClick={() => {
          showModal({
            component: MembershipModal,
            props: {
              title: __('Update Membership', 'tutor'),
              icon: <SVGIcon name="dollar-recurring" width={24} height={24} />,
              plan: data,
            },
            depthIndex: 9999,
          });
        }}
      >
        Edit
      </Button>
    </div>
  );
}
