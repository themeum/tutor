import Container from '@TutorShared/components/Container';
import { Breakpoint, spacing } from '@TutorShared/config/styles';

import CouponDiscount from '@CouponDetails/components/coupon/CouponDiscount';
import CouponInfo from '@CouponDetails/components/coupon/CouponInfo';
import CouponUsageLimitation from '@CouponDetails/components/coupon/CouponLimitation';
import CouponPreview from '@CouponDetails/components/coupon/CouponPreview';
import CouponValidity from '@CouponDetails/components/coupon/CouponValidity';
import PurchaseRequirements from '@CouponDetails/components/coupon/PurchaseRequirements';
import { css } from '@emotion/react';
import { TOPBAR_HEIGHT } from './Topbar';

export default function MainContent() {
  return (
    <Container>
      <div css={styles.content}>
        <div css={styles.left}>
          <CouponInfo />
          <CouponDiscount />
          <CouponUsageLimitation />
          <PurchaseRequirements />
          <CouponValidity />
        </div>
        <div>
          <CouponPreview />
        </div>
      </div>
    </Container>
  );
}

const styles = {
  content: css`
    min-height: calc(100vh - ${TOPBAR_HEIGHT}px);
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 342px;
    gap: ${spacing[36]};
    margin-top: ${spacing[32]};
    padding-inline: ${spacing[8]};

    ${Breakpoint.smallTablet} {
      grid-template-columns: 1fr 280px;
    }

    ${Breakpoint.mobile} {
      grid-template-columns: 1fr;
    }
  `,
  left: css`
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
};
