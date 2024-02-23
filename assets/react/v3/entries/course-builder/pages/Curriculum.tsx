import { spacing } from '@Config/styles';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

const Curriculum = () => {
  return (
    <div css={styles.container}>
      <div css={styles.wrapper}>
        <CanvasHead title={__('Curriculum', 'tutor')} />
        <div css={styles.content}>
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Sapiente atque minima amet itaque! At ut eveniet
          voluptatem quae aut, ratione quo animi, consequatur nisi corporis sit. Recusandae dicta animi dolorem.
        </div>
      </div>
    </div>
  );
};

export default Curriculum;
const styles = {
  container: css`
    padding: ${spacing[32]} ${spacing[64]};
  `,
  wrapper: css`
    max-width: 1076px;
    width: 100%;
  `,
  content: css`
    padding: ${spacing[20]} ${spacing[16]};
  `,
};
