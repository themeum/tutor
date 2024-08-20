import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { Enrollment, useCreateEnrollmentMutation } from '@EnrollmentServices/enrollment';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { useFormContext } from 'react-hook-form';
const { __ } = wp.i18n;

export const TOPBAR_HEIGHT = 80;

function Topbar() {
  const form = useFormContext<Enrollment>();

  const createEnrollmentMutation = useCreateEnrollmentMutation();

  async function handleSubmit(data: Enrollment) {
    const isSubscriptionCourse = !!data.course?.plans?.length;

    const response = await createEnrollmentMutation.mutateAsync({
      student_ids: data.students.map((item) => item.ID),
      object_ids: data.course ? (isSubscriptionCourse ? [Number(data.subscription)] : [data.course.id]) : [],
      payment_status: data.payment_status,
      order_type: isSubscriptionCourse ? 'subscription' : 'single_order',
    });

    if (response.status_code === 200) {
      window.location.href = `${tutorConfig.home_url}/wp-admin/admin.php?page=enrollments`;
    }
  }

  function handleGoBack() {
    window.location.href = `${tutorConfig.home_url}/wp-admin/admin.php?page=enrollments`;
  }

  return (
    <div css={styles.wrapper}>
      <div css={styles.container}>
        <div css={styles.innerWrapper}>
          <div css={styles.left}>
            <button type="button" css={styles.backButton} onClick={handleGoBack}>
              <SVGIcon name="arrowLeft" width={26} height={26} />
            </button>
            <div>
              <div css={styles.headerContent}>
                <h4 css={typography.heading5('medium')}>{__('Manual Enrollment', 'tutor')}</h4>
              </div>
            </div>
          </div>
          <div css={styles.right}>
            <Button
              variant="primary"
              onClick={form.handleSubmit(handleSubmit)}
              loading={createEnrollmentMutation.isPending}
            >
              {__('Enroll Students', 'tutor')}
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Topbar;

const styles = {
  wrapper: css`
    height: ${TOPBAR_HEIGHT}px;
    background: ${colorTokens.background.white};
  `,
  container: css`
    max-width: 1030px;
    margin: 0 auto;
    height: 100%;
  `,
  innerWrapper: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
  `,
  headerContent: css`
    display: flex;
    align-items: center;
    gap: ${spacing[16]};
  `,
  left: css`
    display: flex;
    gap: ${spacing[16]};
  `,
  right: css`
    display: flex;
    gap: ${spacing[12]};
  `,
  backButton: css`
    ${styleUtils.resetButton};
    background-color: transparent;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid ${colorTokens.border.neutral};
    border-radius: ${borderRadius[4]};
    color: ${colorTokens.icon.default};
    transition: color 0.3s ease-in-out;

    :hover {
      color: ${colorTokens.icon.hover};
    }
  `,
};
