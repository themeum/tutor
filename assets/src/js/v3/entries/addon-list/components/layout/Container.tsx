import { spacing } from '@TutorShared/config/styles';
import { css } from '@emotion/react';
import type { ReactNode } from 'react';

const CONTAINER_WIDTH = 1196;

function Container({ children }: { children: ReactNode }) {
  return <div css={styles.wrapper}>{children}</div>;
}

export default Container;

const styles = {
  wrapper: css`
    width: 100%;
    max-width: ${CONTAINER_WIDTH}px;
    padding-inline: ${spacing[12]};
    margin: 0 auto;
  `,
};
