import Skeleton from '@Atoms/Skeleton';
import { spacing } from '@Config/styles';
import { css } from '@emotion/react';

const ContentSkeleton = () => {
  return (
    <div css={styles.wrapper}>
      <Skeleton isMagicAi animation width="20%" height="16px" />
      <Skeleton isMagicAi animation width="100%" height="2px" />

      <div css={styles.group}>
        <Skeleton isMagicAi animation width="16px" height="16px" isRound />
        <Skeleton isMagicAi animation width="80%" height="16px" />
        <Skeleton isMagicAi animation width="20%" height="16px" />
      </div>
      <div css={styles.group}>
        <Skeleton isMagicAi animation width="16px" height="16px" isRound />
        <Skeleton isMagicAi animation width="85%" height="16px" />
        <Skeleton isMagicAi animation width="20%" height="16px" />
      </div>
      <div css={styles.group}>
        <Skeleton isMagicAi animation width="16px" height="16px" isRound />
        <Skeleton isMagicAi animation width="90%" height="16px" />
        <Skeleton isMagicAi animation width="20%" height="16px" />
      </div>
      <div css={styles.group}>
        <Skeleton isMagicAi animation width="16px" height="16px" isRound />
        <Skeleton isMagicAi animation width="60%" height="16px" />
        <Skeleton isMagicAi animation width="20%" height="16px" />
      </div>
      <div css={styles.group}>
        <Skeleton isMagicAi animation width="16px" height="16px" isRound />
        <Skeleton isMagicAi animation width="70%" height="16px" />
        <Skeleton isMagicAi animation width="20%" height="16px" />
      </div>
    </div>
  );
};

export default ContentSkeleton;
const styles = {
  wrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
		width: 100%;
		margin-top: ${spacing[16]};
	`,
  group: css`
		display: flex;
		gap: ${spacing[16]};
		align-items: center;
	`,
};
