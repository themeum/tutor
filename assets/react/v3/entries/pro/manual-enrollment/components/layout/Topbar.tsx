import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { tutorConfig } from '@Config/config';
import { Breakpoint, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { type Enrollment, useCreateEnrollmentMutation } from '@EnrollmentServices/enrollment';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

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
      handleGoBack();
    }
  }

  function handleGoBack() {
    window.location.href = `${tutorConfig.site_url}/wp-admin/admin.php?page=enrollments`;
  }

  return (
    <div css={styles.wrapper}>
      <div css={styles.container}>
        <div css={styles.innerWrapper}>
          <div css={styles.left}>
            <button type="button" css={styleUtils.backButton} onClick={handleGoBack}>
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

    ${Breakpoint.smallMobile} {
      padding-inline: ${spacing[8]};
      height: auto;
    }
  `,
  container: css`
    max-width: 1054px;
    margin: 0 auto;
    height: 100%;
  `,
  innerWrapper: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    padding-inline: ${spacing[12]};

    ${Breakpoint.smallMobile} {
      padding-block: ${spacing[12]};
      flex-direction: column;
      gap: ${spacing[8]};
    }
  `,
  headerContent: css`
    display: flex;
    align-items: center;
    gap: ${spacing[16]};
  `,
  left: css`
    display: flex;
    gap: ${spacing[16]};

    ${Breakpoint.smallMobile} {
      width: 100%;
    }
  `,
  right: css`
    display: flex;
    gap: ${spacing[12]};
  `,
};
