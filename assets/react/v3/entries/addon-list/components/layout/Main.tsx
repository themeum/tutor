import { css } from '@emotion/react';
import Topbar from './Topbar';
import { AddonProvider } from '@AddonList/contexts/addon-context';
import AddonList from '@AddonList/components/AddonList';
import Container from './Container';

function Main() {
  return (
    <div css={styles.wrapper}>
      <AddonProvider>
        <Topbar />
        <Container>
          <AddonList />
        </Container>
      </AddonProvider>
    </div>
  );
}

export default Main;

const styles = {
  wrapper: css``,
};
