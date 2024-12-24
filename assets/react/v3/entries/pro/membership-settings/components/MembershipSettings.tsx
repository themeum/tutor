import { useEffect } from 'react';
import { FormProvider } from 'react-hook-form';
import { css } from '@emotion/react';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import Show from '@Controls/Show';
import { spacing } from '@Config/styles';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { type MembershipSettings, useMembershipSettingsQuery } from '../services/memberships';
import EmptyState from '../molecules/EmptyState';

function MembershipSettings() {
  const form = useFormWithGlobalError<MembershipSettings>({
    defaultValues: {
      memberships: [],
    },
  });

  const { reset } = form;

  const membershipSettingsQuery = useMembershipSettingsQuery();

  const memberships = membershipSettingsQuery.data?.memberships?.length
    ? membershipSettingsQuery.data.memberships
    : form.getValues('memberships');

  const formData = form.watch();

  useEffect(() => {
    if (form.formState.isDirty) {
      document.getElementById('save_tutor_option')?.removeAttribute('disabled');
    }
  }, [form.formState.isDirty]);

  useEffect(() => {
    if (membershipSettingsQuery.data) {
      reset(membershipSettingsQuery.data);
    }
  }, [reset, membershipSettingsQuery.data]);

  if (membershipSettingsQuery.isLoading) {
    return <LoadingSection />;
  }

  return (
    <div css={styles.wrapper} data-isdirty={form.formState.isDirty ? 'true' : undefined}>
      <Show when={memberships.length} fallback={<EmptyState onActionClick={() => console.log('here!')} />}>
        <FormProvider {...form}>Membership list</FormProvider>
      </Show>

      <input type="hidden" name="tutor_option[subscription_memberships]" value={JSON.stringify(formData)} />
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
