import { borderRadius, colorTokens } from '@TutorShared/config/styles';
import { isNumber } from '@TutorShared/utils/types';
import { css, keyframes } from '@emotion/react';
import { forwardRef } from 'react';

interface SkeletonProps extends React.HTMLAttributes<HTMLSpanElement> {
  width?: number | string;
  height?: number | string;
  animation?: boolean;
  isMagicAi?: boolean;
  isRound?: boolean;
  animationDuration?: number;
}

const Skeleton = forwardRef<HTMLSpanElement, SkeletonProps>(
  (
    {
      width = '100%',
      height = 16,
      animation = false,
      isMagicAi = false,
      isRound = false,
      animationDuration = 1.6,
      className,
    },
    ref,
  ) => {
    return (
      <span
        ref={ref}
        css={styles.skeleton(width, height, animation, isMagicAi, isRound, animationDuration)}
        className={className}
      />
    );
  },
);

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
  skeleton: (
    width: number | string,
    height: number | string,
    animation: boolean,
    isMagicAi: boolean,
    isRound: boolean,
    animationDuration: number,
  ) => css`
    display: block;
    width: ${isNumber(width) ? `${width}px` : width};
    height: ${isNumber(height) ? `${height}px` : height};
    border-radius: ${borderRadius[6]};
    background-color: ${!isMagicAi ? 'rgba(0, 0, 0, 0.11)' : colorTokens.background.magicAi.skeleton};
    position: relative;
    -webkit-mask-image: -webkit-radial-gradient(center, white, black);
    overflow: hidden;

    ${isRound &&
    css`
      border-radius: ${borderRadius.circle};
    `}

    ${animation &&
    css`
      :after {
        content: '';
        background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.05), transparent);
        position: absolute;
        transform: translateX(-100%);
        inset: 0;
        ${isMagicAi &&
        css`
          background: linear-gradient(89.17deg, #fef4ff 0.2%, #f9d3ff 50.09%, #fef4ff 96.31%);
        `}

        animation: ${animationDuration}s linear 0.5s infinite normal none running ${animations.wave};
      }
    `}
  `,
};
