import AddonList from '@AddonList/components/AddonList';
import { AddonProvider } from '@AddonList/contexts/addon-context';

import Container from './Container';
import Topbar from './Topbar';

function Main() {
  return (
    <div>
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
