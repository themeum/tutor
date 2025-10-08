import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { FormProvider } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import type { ModalProps } from '@TutorShared/components/modals/Modal';
import ModalWrapper from '@TutorShared/components/modals/ModalWrapper';
import SubscriptionItem from '@TutorShared/components/subscription/SubscriptionItem';

import { CURRENT_VIEWPORT } from '@TutorShared/config/constants';
import { Breakpoint, spacing } from '@TutorShared/config/styles';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import {
  type SubscriptionFormData,
  convertFormDataToSubscription,
  defaultSubscriptionFormData,
  useSaveCourseSubscriptionMutation,
} from '@TutorShared/services/subscription';

interface SubscriptionModalProps extends ModalProps {
  courseId: number;
  isBundle?: boolean;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  subscription: SubscriptionFormDataWithSaved;
}

export type SubscriptionFormDataWithSaved = SubscriptionFormData & { isSaved: boolean };

export default function SubscriptionModal({
  courseId,
  isBundle = false,
  icon,
  closeModal,
  subscription,
}: SubscriptionModalProps) {
  const form = useFormWithGlobalError<SubscriptionFormDataWithSaved>({
    defaultValues: subscription || defaultSubscriptionFormData,
    mode: 'onChange',
  });

  const saveSubscriptionMutation = useSaveCourseSubscriptionMutation(courseId);

  const isFormDirty = form.formState.isDirty;
  const isSaved = subscription.isSaved;

  const handleSaveSubscription = async (values: SubscriptionFormDataWithSaved) => {
    const payload = convertFormDataToSubscription({
      ...values,
      id: values.isSaved ? values.id : '0',
      assign_id: String(courseId),
      plan_type: isBundle ? 'bundle' : 'course',
    });
    const response = await saveSubscriptionMutation.mutateAsync(payload);

    if (response.status_code === 200 || response.status_code === 201) {
      closeModal({ action: 'CONFIRM' });
    }
  };

  return (
    <FormProvider {...form}>
      <ModalWrapper
        onClose={() => closeModal({ action: 'CLOSE' })}
        icon={isFormDirty ? <SVGIcon name="warning" width={24} height={24} /> : icon}
        title={
          isFormDirty
            ? CURRENT_VIEWPORT.isAboveMobile
              ? __('Unsaved Changes', __TUTOR_TEXT_DOMAIN__)
              : ''
            : __('Subscription Plan', __TUTOR_TEXT_DOMAIN__)
        }
        subtitle={
          subscription.isSaved ? __('Update plan', __TUTOR_TEXT_DOMAIN__) : __('Create plan', __TUTOR_TEXT_DOMAIN__)
        }
        maxWidth={1218}
        actions={
          isFormDirty && (
            <>
              <Button
                variant="text"
                size="small"
                onClick={() => (isSaved ? form.reset() : closeModal({ action: 'CLOSE' }))}
              >
                {isSaved ? __('Discard Changes', __TUTOR_TEXT_DOMAIN__) : __('Cancel', __TUTOR_TEXT_DOMAIN__)}
              </Button>
              <Button
                data-cy="save-subscription"
                loading={saveSubscriptionMutation.isPending}
                variant="primary"
                size="small"
                onClick={form.handleSubmit(handleSaveSubscription)}
              >
                {isSaved ? __('Update', __TUTOR_TEXT_DOMAIN__) : __('Save', __TUTOR_TEXT_DOMAIN__)}
              </Button>
            </>
          )
        }
      >
        <div css={styles.wrapper}>
          <div css={styles.container}>
            <div css={styles.content}>
              <SubscriptionItem key={subscription.id} />
            </div>
          </div>
        </div>
      </ModalWrapper>
    </FormProvider>
  );
}

const styles = {
  wrapper: css`
    width: 100%;
    height: 100%;
  `,
  container: css`
    max-width: 640px;
    width: 100%;
    padding-block: ${spacing[40]};
    margin-inline: auto;
    display: flex;
    flex-direction: column;
    gap: ${spacing[32]};

    ${Breakpoint.smallMobile} {
      padding-block: ${spacing[24]};
      padding-inline: ${spacing[8]};
    }
  `,
  content: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
};
