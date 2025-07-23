import type { Coupon } from '@CouponDetails/services/coupon';
import Button from '@TutorShared/atoms/Button';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';
import { spacing } from '@TutorShared/config/styles';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import type { UseFormReturn } from 'react-hook-form';
import CategoryListTable from './CategoryListTable';
import CourseListTable from './CourseListTable';
import MembershipPlanListTable from './MembershipPlanListTable';

interface CouponSelectItemModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<Coupon, any, undefined>;
  type: 'bundles' | 'courses' | 'categories' | 'membershipPlans';
}

function CouponSelectItemModal({ title, closeModal, actions, form, type }: CouponSelectItemModalProps) {
  const _form = useFormWithGlobalError<Coupon>({
    defaultValues: form.getValues(),
  });

  const itemsTable = {
    courses: <CourseListTable form={_form} type="courses" />,
    bundles: <CourseListTable form={_form} type="bundles" />,
    categories: <CategoryListTable form={_form} />,
    membershipPlans: <MembershipPlanListTable form={_form} />,
  };

  function handleApply() {
    form.setValue(type, _form.getValues(type));
    closeModal({ action: 'CONFIRM' });
  }

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions} maxWidth={720}>
      {itemsTable[type]}
      <div css={styles.footer}>
        <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
          {__('Cancel', 'tutor')}
        </Button>
        <Button type="submit" size="small" variant="primary" onClick={handleApply}>
          {__('Add', 'tutor')}
        </Button>
      </div>
    </BasicModalWrapper>
  );
}

export default CouponSelectItemModal;

const styles = {
  footer: css`
    box-shadow: 0px 1px 0px 0px #e4e5e7 inset;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: end;
    gap: ${spacing[16]};
    padding-inline: ${spacing[16]};
  `,
};
