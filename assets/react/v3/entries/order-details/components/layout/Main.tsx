import { css } from '@emotion/react';

import Container from '@TutorShared/components/Container';
import Activities from '@OrderComponents/order/Activities';
import Notes from '@OrderComponents/order/Notes';
import Payment from '@OrderComponents/order/Payment';
import PaymentInfo from '@OrderComponents/order/PaymentInfo';
import Student from '@OrderComponents/order/Student';
import Summary from '@OrderComponents/order/Summary';
import { OrderProvider } from '@OrderContexts/order-context';

import { Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { isDefined } from '@TutorShared/utils/types';
import { getQueryParam } from '@TutorShared/utils/url';
import Topbar, { TOPBAR_HEIGHT } from './Topbar';

function Main() {
  const orderId = getQueryParam('id', 'number');

  if (!isDefined(orderId)) {
    return null;
  }

  return (
    <div css={styles.wrapper}>
      <OrderProvider orderId={orderId}>
        <Topbar />
        <Container>
          <div css={styles.content}>
            <div css={styles.left}>
              <Summary />
              <Payment />
              <Activities />
            </div>
            <div css={styles.right}>
              <Student />
              <PaymentInfo />
              <Notes />
            </div>
          </div>
        </Container>
      </OrderProvider>
    </div>
  );
}

export default Main;

const styles = {
  wrapper: css`
    background-color: ${colorTokens.background.default};
    margin-left: ${spacing[20]};

    ${Breakpoint.mobile} {
      margin-left: ${spacing[12]};
    }
  `,
  content: css`
    min-height: calc(100vh - ${TOPBAR_HEIGHT}px);
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 356px;
    gap: ${spacing[24]};
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
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  right: css`
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,
};
