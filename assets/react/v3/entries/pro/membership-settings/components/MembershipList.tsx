import Button from '@/v3/shared/atoms/Button';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import For from '@/v3/shared/controls/For';
import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';
import MembershipItem from './MembershipItem';
import { type MembershipSettings } from '../services/memberships';

interface MembershipListProps {
  onNewMembershipClick: () => void;
}

export default function MembershipList({ onNewMembershipClick }: MembershipListProps) {
  const form = useFormContext<MembershipSettings>();
  const membershipPlans = form.watch('plans');

  console.log(form.getValues());

  return (
    <div>
      <div>
        <For each={membershipPlans}>{(item) => <MembershipItem data={item} />}</For>
      </div>

      <Button
        variant="primary"
        isOutlined
        size="large"
        onClick={onNewMembershipClick}
        icon={<SVGIcon name="plus" width={24} height={24} />}
      >
        {__('New Membership Level', 'tutor')}
      </Button>
    </div>
  );
}
