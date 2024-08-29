import Skeleton from '@Atoms/Skeleton';
import { spacing } from '@Config/styles';
import { css } from '@emotion/react';

const ImageSkeleton = () => {
  return (
    <div css={styles.wrapper}>
      <Skeleton isMagicAi animation width="100%" height="390px" />
    </div>
  );
};

export default ImageSkeleton;
const styles = {
  wrapper: css`
		display: flex;
		align-items: center;
		gap: ${spacing[16]};
		width: 100%;
	`,
};
