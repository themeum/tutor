import { spacing } from '@Config/styles';
import { css } from '@emotion/react';

const EmptyPreviewDetail = () => {
  return (
    <div css={styles.wrapper}>
      <svg width="250" height="300" xmlns="http://www.w3.org/2000/svg">
        <line
          x1="10"
          y1="20"
          x2="80"
          y2="20"
          stroke="black"
          stroke-width="6px"
          stroke-linecap="round"
          stroke-opacity="0.05"
        />

        <circle cx="30" cy="50" r="3" fill="black" fill-opacity="0.05" />
        <line
          x1="50"
          y1="50"
          x2="200"
          y2="50"
          stroke="black"
          stroke-width="6px"
          stroke-linecap="round"
          stroke-opacity="0.05"
        />

        <circle cx="30" cy="80" r="3" fill="black" fill-opacity="0.05" />
        <line
          x1="50"
          y1="80"
          x2="180"
          y2="80"
          stroke="black"
          stroke-width="6px"
          stroke-linecap="round"
          stroke-opacity="0.05"
        />

        <circle cx="30" cy="110" r="3" fill="black" fill-opacity="0.05" />
        <line
          x1="50"
          y1="110"
          x2="120"
          y2="110"
          stroke="black"
          stroke-width="6px"
          stroke-linecap="round"
          stroke-opacity="0.05"
        />

        <line
          x1="10"
          y1="160"
          x2="80"
          y2="160"
          stroke="black"
          stroke-width="6px"
          stroke-linecap="round"
          stroke-opacity="0.05"
        />

        <circle cx="30" cy="190" r="3" fill="black" fill-opacity="0.05" />
        <line
          x1="50"
          y1="190"
          x2="140"
          y2="190"
          stroke="black"
          stroke-width="6px"
          stroke-linecap="round"
          stroke-opacity="0.05"
        />

        <circle cx="30" cy="220" r="3" fill="black" fill-opacity="0.05" />
        <line
          x1="50"
          y1="220"
          x2="180"
          y2="220"
          stroke="black"
          stroke-width="6px"
          stroke-linecap="round"
          stroke-opacity="0.05"
        />

        <circle cx="30" cy="250" r="3" fill="black" fill-opacity="0.05" />
        <line
          x1="50"
          y1="250"
          x2="120"
          y2="250"
          stroke="black"
          stroke-width="6px"
          stroke-linecap="round"
          stroke-opacity="0.05"
        />
      </svg>
    </div>
  );
};

export default EmptyPreviewDetail;

const styles = {
  wrapper: css`
    padding-left: ${spacing[24]};
  `,
};
