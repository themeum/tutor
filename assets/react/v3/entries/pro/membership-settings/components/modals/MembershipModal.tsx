import { lazy, Suspense, useEffect } from 'react';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { FormProvider } from 'react-hook-form';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import { type ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';

import { Breakpoint, spacing } from '@Config/styles';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import {
  convertFormDataToPayload,
  convertPlanToFormData,
  defaultValues,
  type MembershipPlan,
  useSaveMembershipPlanMutation,
} from '../../services/memberships';
import { LoadingSection } from '@/v3/shared/atoms/LoadingSpinner';
const MembershipFormFields = lazy(() => import('../MembershipFormFields'));

interface MembershipModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  plan?: MembershipPlan;
}

export default function MembershipModal({ title, subtitle, icon, plan, closeModal }: MembershipModalProps) {
  const form = useFormWithGlobalError({
    defaultValues: plan ? convertPlanToFormData(plan) : defaultValues,
  });

  const saveMembershipPlanMutation = useSaveMembershipPlanMutation();

  useEffect(() => {
    if (plan) {
      form.reset(convertPlanToFormData(plan));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [plan]);

  function handleSubmit() {
    form.handleSubmit(async (data) => {
      const payload = convertFormDataToPayload(data);
      const response = await saveMembershipPlanMutation.mutateAsync(payload);
      if (response.status_code === 200 || response.status_code === 201) {
        closeModal({ action: 'CONFIRM' });
      }
    })();
  }

  const isFormDirty = form.formState.isDirty;

  return (
    <FormProvider {...form}>
      <ModalWrapper
        maxWidth={1060}
        onClose={() => closeModal({ action: 'CLOSE' })}
        icon={isFormDirty ? <SVGIcon name="warning" width={24} height={24} /> : icon}
        title={isFormDirty ? __('Unsaved Changes', 'tutor') : title}
        subtitle={isFormDirty ? title?.toString() : subtitle}
        actions={
          <>
            <Button
              variant="text"
              size="small"
              onClick={() => {
                closeModal({ action: 'CLOSE' });
              }}
            >
              {__('Cancel', 'tutor')}
            </Button>
            <Button
              variant="primary"
              size="small"
              onClick={handleSubmit}
              loading={saveMembershipPlanMutation.isPending}
            >
              {__('Save', 'tutor')}
            </Button>
          </>
        }
      >
        <div css={styles.wrapper}>
          <Suspense fallback={<LoadingSection />}>
            <MembershipFormFields />
          </Suspense>
        </div>
      </ModalWrapper>
    </FormProvider>
  );
}

const styles = {
  wrapper: css`
    padding: ${spacing[40]} ${spacing[16]};

    ${Breakpoint.mobile} {
      padding: ${spacing[24]} ${spacing[16]};
    }
  `,
};
