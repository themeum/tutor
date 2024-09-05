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

const rotate = keyframes`
	0% {
		transform: rotate(0deg);
	}
	100% {
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
  spinGradient: css`
		transition: transform;
		transform-origin: center;
    animation: ${rotate} 1s infinite linear;
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

export const GradientLoadingSpinner = ({ size = 24, color = colorPalate.icon.disabled }: LoadingSpinnerProps) => {
  return (
    // biome-ignore lint/a11y/noSvgWithoutTitle: <explanation>
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path
        d="M12 3C10.22 3 8.47991 3.52784 6.99987 4.51677C5.51983 5.50571 4.36628 6.91131 3.68509 8.55585C3.0039 10.2004 2.82567 12.01 3.17294 13.7558C3.5202 15.5016 4.37737 17.1053 5.63604 18.364C6.89472 19.6226 8.49836 20.4798 10.2442 20.8271C11.99 21.1743 13.7996 20.9961 15.4442 20.3149C17.0887 19.6337 18.4943 18.4802 19.4832 17.0001C20.4722 15.5201 21 13.78 21 12"
        stroke="url(#paint0_linear_2402_3559)"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
        css={styles.spinGradient}
      />
      <defs>
        <linearGradient
          id="paint0_linear_2402_3559"
          x1="4.50105"
          y1="12"
          x2="21.6571"
          y2="6.7847"
          gradientUnits="userSpaceOnUse"
        >
          <stop stopColor="#FF9645" />
          <stop offset="0.152804" stopColor="#FF6471" />
          <stop offset="0.467993" stopColor="#CF6EBD" />
          <stop offset="0.671362" stopColor="#A477D1" />
          <stop offset="1" stopColor="#3E64DE" />
        </linearGradient>
      </defs>
    </svg>
  );
};

export default LoadingSpinner;
