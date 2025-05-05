import { css } from '@emotion/react';

import { tutorConfig } from '@TutorShared/config/config';
import { Breakpoint, spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';

import LogoSvg from '@SharedImages/logo.svg';

const Logo = () => {
  const isTutorPro = !!tutorConfig.tutor_pro_url;

  return (
    <button type="button" css={[styleUtils.resetButton, styles.logo]}>
      <Show
        when={isTutorPro && tutorConfig.settings?.course_builder_logo_url}
        fallback={<LogoSvg width={108} height={24} />}
      >
        {(logo) => <img src={logo} alt="Tutor LMS" />}
      </Show>
    </button>
  );
};

const styles = {
  logo: css`
    padding-left: ${spacing[32]};
    cursor: default;

    &:focus,
    &:active,
    &:hover {
      background: none;
    }

    img {
      max-height: 24px;
      width: auto;
      object-fit: contain;
      object-position: center;
    }

    ${Breakpoint.smallTablet} {
      padding-left: ${spacing[24]};
    }

    ${Breakpoint.smallMobile} {
      grid-area: logo;
      padding-left: ${spacing[16]};
    }
  `,
};

export default Logo;
