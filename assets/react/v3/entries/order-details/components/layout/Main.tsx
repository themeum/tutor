import Container from '@Components/Container';
import { colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import Activities from '@OrderComponents/order/Activities';
import Notes from '@OrderComponents/order/Notes';
import Payment from '@OrderComponents/order/Payment';
import Student from '@OrderComponents/order/Student';
import Summary from '@OrderComponents/order/Summary';
import { OrderProvider } from '@OrderContexts/order-context';
import Topbar, { TOPBAR_HEIGHT } from './Topbar';

function Main() {
  return (
    <div css={styles.wrapper}>
      <OrderProvider orderId={1}>
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
	`,

  content: css`
		min-height: calc(100vh - ${TOPBAR_HEIGHT}px);
		width: 100%;
		display: flex;
		gap: ${spacing[24]};
		margin-top: ${spacing[32]};
	`,
  left: css`
		max-width: 736px;
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
