import { borderRadius, colorTokens } from '@Config/styles';
import productPlaceholder from '@Images/course-placeholder.png';
import { css } from '@emotion/react';

interface ImageProps {
	name: string;
	path: string | null;
}

const ImageCard = ({ name, path }: ImageProps) => {
	return (
		<div css={styles.imageCard}>
			<img src={path || productPlaceholder} alt={name} css={styles.image} />
		</div>
	);
};

export const styles = {
	imageCard: css`
		background: ${colorTokens.background.default};
		border-radius: ${borderRadius[6]};
		overflow: hidden;
		border: 1px solid ${colorTokens.stroke.divider};
		position: relative;
		display: flex;
		align-items: center;
	`,
	image: css`
		width: 100%;
	`,
};

export default ImageCard;
