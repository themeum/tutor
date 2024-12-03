import { css } from '@emotion/react';
import Container from '@Components/Container';
import { colorTokens, zIndex } from '@Config/styles';

export const TOPBAR_HEIGHT = 96;

function Topbar() {
  return (
    <div css={styles.wrapper}>
      <Container>
        <div css={styles.innerWrapper}>
          <div css={styles.left}>Left</div>
          <div css={styles.right}>right</div>
        </div>
      </Container>
    </div>
  );
}

export default Topbar;

const styles = {
  wrapper: css`
    height: ${TOPBAR_HEIGHT}px;
    background: ${colorTokens.background.white};
    position: sticky;
    top: 32px;
    z-index: ${zIndex.positive};
  `,
  innerWrapper: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
  `,
  left: css``,
  right: css``,
};
