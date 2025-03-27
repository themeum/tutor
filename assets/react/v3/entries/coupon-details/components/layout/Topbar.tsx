import {
  type Coupon,
  convertFormDataToPayload,
  useCreateCouponMutation,
  useUpdateCouponMutation,
} from '@CouponServices/coupon';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { TutorBadge } from '@TutorShared/atoms/TutorBadge';
import Container from '@TutorShared/components/Container';
import { tutorConfig } from '@TutorShared/config/config';
import { Breakpoint, colorTokens, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { makeFirstCharacterUpperCase } from '@TutorShared/utils/util';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

export const TOPBAR_HEIGHT = 96;

const statusVariant = {
  active: 'success',
  inactive: 'secondary',
  trash: 'critical',
} as const;

function Topbar() {
  const params = new URLSearchParams(window.location.search);
  const courseId = params.get('coupon_id');

  const form = useFormContext<Coupon>();
  const coupon = form.getValues();
  const createCouponMutation = useCreateCouponMutation();
  const updateCouponMutation = useUpdateCouponMutation();

  async function handleSubmit(data: Coupon) {
    const payload = convertFormDataToPayload(data);
    if (data.id) {
      updateCouponMutation.mutate(payload);
    } else {
      createCouponMutation.mutate(payload);
    }
  }

  function handleGoBack() {
    window.location.href = `${tutorConfig.site_url}/wp-admin/admin.php?page=tutor_coupons`;
  }

  return (
    <div css={styles.wrapper}>
      <Container>
        <div css={styles.innerWrapper}>
          <div css={styles.left}>
            <button type="button" css={styleUtils.backButton} onClick={handleGoBack}>
              <SVGIcon name="arrowLeft" width={26} height={26} />
            </button>
            <div>
              <div css={styles.headerContent}>
                <h4 css={styles.headerTitle}>
                  {courseId ? __('Update Coupon', 'tutor') : __('Create Coupon', 'tutor')}
                </h4>
                <TutorBadge variant={statusVariant[coupon.coupon_status]}>
                  {makeFirstCharacterUpperCase(coupon.coupon_status)}
                </TutorBadge>
              </div>
              <Show
                when={coupon.updated_at_gmt && coupon.coupon_update_by.length}
                fallback={
                  coupon.created_at_gmt && (
                    <p css={styles.updateMessage}>
                      {sprintf(
                        __('Created by %s at %s', 'tutor'),
                        coupon.coupon_created_by,
                        coupon.created_at_readable,
                      )}
                    </p>
                  )
                }
              >
                {() => (
                  <p css={styles.updateMessage}>
                    {sprintf(__('Updated by %s at %s', 'tutor'), coupon.coupon_update_by, coupon.updated_at_readable)}
                  </p>
                )}
              </Show>
            </div>
          </div>
          <div css={styles.right}>
            <Button variant="tertiary" onClick={handleGoBack}>
              {__('Cancel', 'tutor')}
            </Button>
            <Button
              data-cy="save-coupon"
              variant="primary"
              loading={createCouponMutation.isPending || updateCouponMutation.isPending}
              onClick={form.handleSubmit(handleSubmit)}
            >
              {__('Save', 'tutor')}
            </Button>
          </div>
        </div>
      </Container>
    </div>
  );
}

export default Topbar;

const styles = {
  wrapper: css`
    height: ${TOPBAR_HEIGHT}px;
    background: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
    position: sticky;
    top: 32px;
    z-index: ${zIndex.positive};

    ${Breakpoint.mobile} {
      position: unset;
      padding-inline: ${spacing[8]};
    }

    ${Breakpoint.smallMobile} {
      height: auto;
    }
  `,
  innerWrapper: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    padding-inline: ${spacing[8]};

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
  headerTitle: css`
    ${typography.heading5('medium')};

    ${Breakpoint.smallMobile} {
      ${typography.heading6('medium')};
    }
  `,
  left: css`
    display: flex;
    gap: ${spacing[16]};
    width: 100%;
  `,
  right: css`
    display: flex;
    gap: ${spacing[12]};
  `,
  updateMessage: css`
    ${typography.body()};
    color: ${colorTokens.text.subdued};
  `,
};
