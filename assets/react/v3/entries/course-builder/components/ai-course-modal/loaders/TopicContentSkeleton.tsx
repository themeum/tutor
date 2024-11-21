import Skeleton from '@Atoms/Skeleton';
import { spacing } from '@Config/styles';
import { css } from '@emotion/react';

const TopicContentSkeleton = () => {
  return (
    <div css={styles.spacer}>
      <div css={styles.group}>
        <Skeleton isMagicAi animation width="16px" height="16px" isRound />
        <Skeleton isMagicAi animation width="50%" height="16px" />
      </div>
      <div css={styles.group}>
        <Skeleton isMagicAi animation width="16px" height="16px" isRound />
        <Skeleton isMagicAi animation width="40%" height="16px" />
      </div>
      <div css={styles.group}>
        <Skeleton isMagicAi animation width="16px" height="16px" isRound />
        <Skeleton isMagicAi animation width="80%" height="16px" />
      </div>
    </div>
  );
};

export default TopicContentSkeleton;
const styles = {
  group: css`
		display: flex;
		gap: ${spacing[16]};
		align-items: center;
	`,
  spacer: css`
		margin-left: ${spacing[16]};
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
	`,
};
