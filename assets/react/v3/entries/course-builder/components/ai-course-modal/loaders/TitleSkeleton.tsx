import Skeleton from '@Atoms/Skeleton';
import { spacing } from '@Config/styles';
import { css } from '@emotion/react';

const TitleSkeleton = () => {
  return (
    <div css={styles.wrapper}>
      <Skeleton isMagicAi animation width="24px" height="24px" isRound />
      <Skeleton isMagicAi animation width="100%" height="24px" />
    </div>
  );
};

export default TitleSkeleton;
const styles = {
  wrapper: css`
		display: flex;
		align-items: center;
		gap: ${spacing[16]};
		width: 100%;
	`,
};
