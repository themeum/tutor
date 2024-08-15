import { borderRadius, colorTokens } from '@Config/styles';
import { isNumber } from '@Utils/types';
import { css, keyframes } from '@emotion/react';

interface SkeletonProps {
  width?: number | string;
  height?: number | string;
  animation?: boolean;
  isMagicAi?: boolean;
}

const Skeleton = ({ width = '100%', height = 16, animation = false, isMagicAi = false }: SkeletonProps) => {
  return <span css={styles.skeleton(width, height, animation, isMagicAi)} />;
};

export default Skeleton;

const animations = {
  wave: keyframes`
    0% {
      transform: translateX(-100%);
    }
    50% {
      transform: translateX(0%);
    }
    100% {
      transform: translateX(100%);
    }
  `,
};

const styles = {
  skeleton: (width: number | string, height: number | string, animation: boolean, isMagicAi: boolean) => css`
    display: block;
    width: ${isNumber(width) ? `${width}px` : width};
    height: ${isNumber(height) ? `${height}px` : height};
    border-radius: ${borderRadius[6]};
    background-color: ${!isMagicAi ? 'rgba(0, 0, 0, 0.11)' : colorTokens.background.magicAi.skeleton} ;
    position: relative;
    -webkit-mask-image: -webkit-radial-gradient(center, white, black);
    overflow: hidden;

    ${
      animation &&
      css`
      :after {
        content: '';
				background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.05), transparent);
				position: absolute;
        transform: translateX(-100%);
        inset: 0;
        ${
          isMagicAi &&
          css`
						background: linear-gradient(89.17deg, #FEF4FF 0.8%, #F9D3FF 50.09%, #FEF4FF 96.31%);
						transform: rotate(-16deg);
				`
        }

				animation: 1.6s linear 0.5s infinite normal none running ${animations.wave};
      }
    `
    }
  `,
};
