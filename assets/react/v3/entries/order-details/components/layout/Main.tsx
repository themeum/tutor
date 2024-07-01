import Container from '@Components/Container';
import { colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import Activities from '@OrderComponents/order/Activities';
import Notes from '@OrderComponents/order/Notes';
import Payment from '@OrderComponents/order/Payment';
import Student from '@OrderComponents/order/Student';
import Summary from '@OrderComponents/order/Summary';
import type { OrderSummaryItem } from '@OrderServices/order';
import Topbar, { TOPBAR_HEIGHT } from './Topbar';

const items: OrderSummaryItem[] = [
  {
    id: 1,
    title: 'Tutor LMS For Beginners Part II: Progress Your Webflow Skills',
    image: '',
    type: 'course',
    regular_price: '$140.00',
    discounted_price: '$120.00',
  },
  {
    id: 2,
    title: 'Frontend Courses',
    type: 'bundle',
    regular_price: '$140.00',
    discounted_price: '$120.00',
    total_courses: 4,
    discount: {
      name: 'Special Discount',
      value: '$20.00',
    },
  },
  {
    id: 3,
    title: 'Backend Guru',
    image: '',
    type: 'bundle',
    regular_price: '$150.00',
    discounted_price: '$140.00',
    total_courses: 4,
    discount: {
      name: 'Promotional discount',
      value: '$10.00',
    },
  },
];

function Main() {
  return (
    <div css={styles.wrapper}>
      <Topbar />
      <Container>
        <div css={styles.content}>
          <div css={styles.left}>
            <Summary items={items} />
            <Payment />
            <Activities />
          </div>
          <div css={styles.right}>
            <Student />
            <Notes />
          </div>
        </div>
      </Container>
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
