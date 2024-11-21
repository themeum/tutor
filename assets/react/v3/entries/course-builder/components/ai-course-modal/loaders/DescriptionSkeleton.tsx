import Skeleton from '@Atoms/Skeleton';
import { spacing } from '@Config/styles';
import { css } from '@emotion/react';

const DescriptionSkeleton = () => {
  return (
    <div css={styles.wrapper}>
      <Skeleton isMagicAi animation width="20%" height="16px" />
      <Skeleton isMagicAi animation width="100%" height="2px" />
      <Skeleton isMagicAi animation width="100%" height="16px" />
      <Skeleton isMagicAi animation width="80%" height="16px" />
      <Skeleton isMagicAi animation width="70%" height="16px" />

      <Skeleton isMagicAi animation width="30%" height="16px" />
      <Skeleton isMagicAi animation width="100%" height="16px" />
      <Skeleton isMagicAi animation width="100%" height="16px" />
      <Skeleton isMagicAi animation width="90%" height="16px" />
      <Skeleton isMagicAi animation width="95%" height="16px" />

      <Skeleton isMagicAi animation width="35%" height="16px" />
      <Skeleton isMagicAi animation width="100%" height="16px" />
      <Skeleton isMagicAi animation width="100%" height="16px" />
      <Skeleton isMagicAi animation width="65%" height="16px" />
      <Skeleton isMagicAi animation width="80%" height="16px" />
    </div>
  );
};

export default DescriptionSkeleton;
const styles = {
  wrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
		width: 100%;
		margin-top: ${spacing[16]};
	`,
};
