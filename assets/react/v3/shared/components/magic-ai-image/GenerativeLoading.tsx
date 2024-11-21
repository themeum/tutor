import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { css, keyframes } from '@emotion/react';
import React from 'react';

const GenerativeLoading = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className }, ref) => {
    return (
      <div ref={ref} className={className} css={styles.wrapper}>
        <div css={styles.item} />
        <div css={styles.item} />
        <div css={styles.item} />
        <div css={styles.item} />
      </div>
    );
  },
);

export default GenerativeLoading;

const animations = {
  walker: keyframes`
		0% {
			left: -100%;
		}
    100% {
      left: 100%;
    }
	`,
  loading: keyframes`
		0% {
      opacity: 0.3;
    }
		25% {
			opacity: 0.5;
		}
    50% {
      opacity: 0.7;
    }
		75% {
			opacity: 0.5;
		}
    100% {
      opacity: 0.3;
    }
	`,
};

const styles = {
  wrapper: css`
		display: grid;
		grid-template-columns: repeat(2, minmax(300px, 1fr));
    grid-template-rows: repeat(2, minmax(300px, 1fr));
		gap: ${spacing[12]};
	`,
  item: css`
		border-radius: ${borderRadius[12]};
		background: ${colorTokens.ai.gradient_1};
		position: relative;
		width: 100%;
		height: 100%;
		background-size: 612px 612px;
		opacity: 0.3;
		transition: opacity 0.5s ease;
		animation: ${animations.loading} 2s linear infinite;

		&:nth-of-type(1) {
			background-position: top left;
		}

		&:nth-of-type(2) {
			background-position: top right;
			animation-delay: 0.5s;
		}

		&:nth-of-type(3) {
			background-position: bottom left;
			animation-delay: 1.5s;
		}

		&:nth-of-type(4) {
			background-position: bottom right;
			animation-delay: 1s;
		}
	`,
};
