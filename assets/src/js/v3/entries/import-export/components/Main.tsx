import { css } from '@emotion/react';

import { spacing } from '@TutorShared/config/styles';
import { styleUtils } from '@TutorShared/utils/style-utils';

import Export from '@ImportExport/components/Export';
import History from '@ImportExport/components/History';
import Import from '@ImportExport/components/Import';

const Main = () => {
  return (
    <div css={styles.wrapper}>
      <Import />

      <Export />

      <History />
    </div>
  );
};

export default Main;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[20]};
    padding-bottom: ${spacing[20]};
  `,
};
