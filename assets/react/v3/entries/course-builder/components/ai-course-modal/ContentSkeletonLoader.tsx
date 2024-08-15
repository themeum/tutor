import Skeleton from '@Atoms/Skeleton';
import { spacing } from '@Config/styles';
import { css } from '@emotion/react';

const ContentSkeletonLoader = () => {
  return (
    <div css={styles.wrapper}>
      <Skeleton isMagicAi animation={true} width="80%" height="16px" />
      <Skeleton isMagicAi animation={true} width="100%" height="390px" />
      <Skeleton isMagicAi animation={true} width="20%" height="16px" />
      <Skeleton isMagicAi animation={true} width="10%" height="16px" />
      <Skeleton isMagicAi animation={true} width="100%" height="16px" />
      <Skeleton isMagicAi animation={true} width="100%" height="16px" />
      <Skeleton isMagicAi animation={true} width="40%" height="16px" />
      <Skeleton isMagicAi animation={true} width="30%" height="16px" />
      <Skeleton isMagicAi animation={true} width="90%" height="16px" />
      <Skeleton isMagicAi animation={true} width="100%" height="16px" />
      <Skeleton isMagicAi animation={true} width="50%" height="16px" />
      <Skeleton isMagicAi animation={true} width="20%" height="16px" />
      <Skeleton isMagicAi animation={true} width="30%" height="16px" />
      <Skeleton isMagicAi animation={true} width="50%" height="16px" />
      <Skeleton isMagicAi animation={true} width="20%" height="16px" />
      <Skeleton isMagicAi animation={true} width="30%" height="16px" />
      <Skeleton isMagicAi animation={true} width="50%" height="16px" />
      <Skeleton isMagicAi animation={true} width="20%" height="16px" />
      <Skeleton isMagicAi animation={true} width="30%" height="16px" />
      <Skeleton isMagicAi animation={true} width="100%" height="16px" />
      <Skeleton isMagicAi animation={true} width="100%" height="16px" />
    </div>
  );
};

export default ContentSkeletonLoader;

const styles = {
  wrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
	`,
};
