import { borderRadius, colorPalate } from '@Config/styles';
import productPlaceholder from '@Public/images/product-placeholder.png';
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
    background: ${colorPalate.surface.neutral.default};
    border-radius: ${borderRadius[6]};
    overflow: hidden;
    border: 1px solid ${colorPalate.border.neutral};
    position: relative;
    display: flex;
    align-items: center;
  `,
	image: css`
    width: 100%;
  `,
};

export default ImageCard;
