import Skeleton from '@Atoms/Skeleton';
import { spacing } from '@Config/styles';
import { css } from '@emotion/react';

const SkeletonLoader = () => {
  return (
    <div css={styles.container}>
      <div css={styles.wrapper}>
        <Skeleton animation={true} isMagicAi width="20%" height="12px" />
        <Skeleton animation={true} isMagicAi width="100%" height="12px" />
        <Skeleton animation={true} isMagicAi width="100%" height="12px" />
        <Skeleton animation={true} isMagicAi width="40%" height="12px" />
      </div>
      <div css={styles.wrapper}>
        <Skeleton animation={true} isMagicAi width="80%" height="12px" />
        <Skeleton animation={true} isMagicAi width="100%" height="12px" />
        <Skeleton animation={true} isMagicAi width="80%" height="12px" />
      </div>
    </div>
  );
};

export default SkeletonLoader;

const styles = {
  wrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};
	`,
  container: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[32]};
	`,
};
