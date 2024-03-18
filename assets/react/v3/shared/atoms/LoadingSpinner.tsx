import { colorPalate } from '@Config/styles';
import { css, keyframes } from '@emotion/react';

const rotatorKeyframes = keyframes`
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
`;

const dashKeyframes = keyframes`
  0% {
    stroke-dashoffset: 180;
    transform: rotate(0deg);
  }
  50% {
    stroke-dashoffset: ${180 / 4};
    transform: rotate(135deg);
  }
  100% {
    stroke-dashoffset: 180;
    transform: rotate(360deg);
  }
`;

const styles = {
	fullscreen: css`
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    width: 100vw;
  `,
	loadingOverlay: css`
    position: absolute;
    top: 0;
    bottom: 0;
    right: 0;
    left: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  `,
	loadingSection: css`
    width: 100%;
    height: 100px;
    display: flex;
    justify-content: center;
    align-items: center;
  `,
	svg: css`
    animation: ${rotatorKeyframes} 1.4s linear infinite;
  `,
	spinnerPath: css`
    stroke-dasharray: 180;
    stroke-dashoffset: 0;
    transform-origin: center;
    animation: ${dashKeyframes} 1.4s linear infinite;
  `,
};

interface LoadingSpinnerProps {
	size?: number;
	color?: string;
}

const LoadingSpinner = ({ size = 30, color = colorPalate.icon.disabled }: LoadingSpinnerProps) => {
	return (
		// biome-ignore lint/a11y/noSvgWithoutTitle: <explanation>
		<svg width={size} height={size} css={styles.svg} viewBox="0 0 86 86" xmlns="http://www.w3.org/2000/svg">
			<circle
				css={styles.spinnerPath}
				fill="none"
				stroke={color}
				strokeWidth="6"
				strokeLinecap="round"
				cx="43"
				cy="43"
				r="30"
			/>
		</svg>
	);
};

export const LoadingOverlay = () => {
	return (
		<div css={styles.loadingOverlay}>
			<LoadingSpinner />
		</div>
	);
};

export const LoadingSection = () => {
	return (
		<div css={styles.loadingSection}>
			<LoadingSpinner />
		</div>
	);
};

export const FullscreenLoadingSpinner = () => {
	return (
		<div css={styles.fullscreen}>
			<LoadingSpinner />
		</div>
	);
};

export default LoadingSpinner;
