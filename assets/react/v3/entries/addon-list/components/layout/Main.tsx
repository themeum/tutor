import { css } from '@emotion/react';
import Container from '@Components/Container';
import { colorTokens, spacing } from '@Config/styles';
import Topbar, { TOPBAR_HEIGHT } from './Topbar';

function Main() {
  return (
    <div css={styles.wrapper}>
      <Topbar />
      <Container>
        <div css={styles.content}>Addon list</div>
      </Container>
    </div>
  );
}

export default Main;

const styles = {
  wrapper: css`
    background-color: ${colorTokens.background.default};
  `,
  content: css`
    min-height: calc(100vh - ${TOPBAR_HEIGHT}px);
    width: 100%;
    margin-top: ${spacing[32]};
  `,
};
