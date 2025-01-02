import { useEffect } from 'react';
import { FormProvider } from 'react-hook-form';
import { css } from '@emotion/react';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import Show from '@Controls/Show';
import { spacing } from '@Config/styles';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { type MembershipSettings, useMembershipSettingsQuery } from '../services/memberships';
import EmptyState from '../molecules/EmptyState';
import { useModal } from '@/v3/shared/components/modals/Modal';
import MembershipModal from './modals/MembershipModal';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import { __ } from '@wordpress/i18n';
import MembershipList from './MembershipList';

function MembershipSettings() {
  const { showModal } = useModal();
  const form = useFormWithGlobalError<MembershipSettings>({
    defaultValues: {
      plans: [],
    },
  });

  const { reset } = form;

  const membershipSettingsQuery = useMembershipSettingsQuery();

  const memberships = membershipSettingsQuery.data?.length ? membershipSettingsQuery.data : form.getValues('plans');

  const formData = form.watch();

  useEffect(() => {
    if (form.formState.isDirty) {
      document.getElementById('save_tutor_option')?.removeAttribute('disabled');
    }
  }, [form.formState.isDirty]);

  useEffect(() => {
    if (membershipSettingsQuery.data) {
      const updatedPlans = membershipSettingsQuery.data?.map((item) => ({
        ...item,
        is_enabled: !!Number(item.is_enabled),
      }));
      reset({ plans: updatedPlans });
    }
  }, [reset, membershipSettingsQuery.data]);

  if (membershipSettingsQuery.isLoading) {
    return <LoadingSection />;
  }

  function handleNewMembershipClick() {
    showModal({
      component: MembershipModal,
      props: {
        title: __('Create Membership', 'tutor'),
        icon: <SVGIcon name="dollar-recurring" width={24} height={24} />,
      },
      depthIndex: 9999,
    });
  }

  return (
    <div css={styles.wrapper} data-isdirty={form.formState.isDirty ? 'true' : undefined}>
      <Show when={memberships.length} fallback={<EmptyState onActionClick={handleNewMembershipClick} />}>
        <FormProvider {...form}>
          <MembershipList onNewMembershipClick={handleNewMembershipClick} />
        </FormProvider>
      </Show>

      <input
        type="hidden"
        name="tutor_option[membership_settings]"
        value={JSON.stringify({
          ...formData,
          plans: formData.plans.map(({ id, plan_name, is_enabled }) => ({ id, plan_name, is_enabled })),
        })}
      />
    </div>
  );
}

export default MembershipSettings;

const styles = {
  wrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
};
