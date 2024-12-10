import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import MagicButton from '@Atoms/MagicButton';
import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';

import { useModal } from '@Components/modals/Modal';
import Tracker from '@CourseBuilderComponents/layouts/Tracker';
import HeaderActions from '@CourseBuilderComponents/layouts/header/HeaderActions';
import Logo from '@CourseBuilderComponents/layouts/header/Logo';
import AICourseBuilderModal from '@CourseBuilderComponents/modals/AICourseBuilderModal';
import ExitCourseBuilderModal from '@CourseBuilderComponents/modals/ExitCourseBuilderModal';
import ProIdentifierModal from '@CourseBuilderComponents/modals/ProIdentifierModal';
import SetupOpenAiModal from '@CourseBuilderComponents/modals/SetupOpenAiModal';

import { tutorConfig } from '@Config/config';
import {
  borderRadius,
  Breakpoint,
  colorTokens,
  containerMaxWidth,
  headerHeight,
  shadow,
  spacing,
  zIndex,
} from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { useCourseNavigator } from '@CourseBuilderContexts/CourseNavigatorContext';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { styleUtils } from '@Utils/style-utils';

import { CURRENT_WINDOW } from '@Config/constants';
import generateCourse2x from '@Images/pro-placeholders/generate-course-2x.webp';
import generateCourse from '@Images/pro-placeholders/generate-course.webp';

const Header = () => {
  const form = useFormContext<CourseFormData>();
  const { currentIndex } = useCourseNavigator();
  const { showModal } = useModal();
  const isFormDirty = form.formState.isDirty;

  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const isOpenAiEnabled = tutorConfig.settings?.chatgpt_enable === 'on';
  const hasOpenAiAPIKey = tutorConfig.settings?.chatgpt_key_exist;

  const handleAiButtonClick = () => {
    if (!isTutorPro) {
      showModal({
        component: ProIdentifierModal,
        props: {
          image: generateCourse,
          image2x: generateCourse2x,
        },
      });
    } else if (!hasOpenAiAPIKey) {
      showModal({
        component: SetupOpenAiModal,
        props: {
          image: generateCourse,
          image2x: generateCourse2x,
        },
      });
    } else {
      showModal({
        component: AICourseBuilderModal,
        isMagicAi: true,
        props: {
          title: __('Create with AI', 'tutor'),
          icon: <SVGIcon name="magicAiColorize" width={24} height={24} />,
        },
      });
    }
  };

  const handleExitButtonClick = () => {
    if (isFormDirty) {
      showModal({
        component: ExitCourseBuilderModal,
      });
    } else {
      const isFormWpAdmin = window.location.href.includes('wp-admin');

      window.location.href = isFormWpAdmin ? tutorConfig.backend_course_list_url : tutorConfig.frontend_course_list_url;
    }
  };

  return (
    <div css={styles.wrapper}>
      <Logo />

      <div css={styles.container}>
        <div css={styles.titleAndTackerWrapper}>
          <div css={styles.titleAndTacker}>
            <h6 css={styles.title}>{__('Course Builder', 'tutor')}</h6>
            <span css={styles.divider} data-title-divider />
            <Tracker />
          </div>

          <Show when={currentIndex === 0 && (isOpenAiEnabled || !isTutorPro)}>
            <span css={styles.divider} />

            <div css={styleUtils.flexCenter()}>
              <MagicButton variant="plain" css={styles.magicButton} onClick={handleAiButtonClick}>
                <SVGIcon name="magicAiColorize" width={24} height={24} />
                <Show when={CURRENT_WINDOW.isDesktop}>{__('Generate with AI', 'tutor')}</Show>
              </MagicButton>
            </div>
          </Show>
        </div>

        <Show when={CURRENT_WINDOW.isDesktop}>
          <HeaderActions />
        </Show>
      </div>

      <div css={styles.closeButtonWrapper}>
        <Show when={!CURRENT_WINDOW.isDesktop}>
          <HeaderActions />
        </Show>
        <Tooltip delay={200} content={__('Exit', 'tutor')} placement="left">
          <button type="button" css={styles.closeButton} onClick={handleExitButtonClick}>
            <SVGIcon name="cross" width={32} height={32} />
          </button>
        </Tooltip>
      </div>
    </div>
  );
};

export default Header;

const styles = {
  wrapper: css`
    height: ${headerHeight}px;
    width: 100%;
    background-color: ${colorTokens.surface.navbar};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    display: grid;
    grid-template-columns: 1fr ${containerMaxWidth}px 1fr;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: ${zIndex.header};

    ${Breakpoint.tablet} {
      grid-template-columns: auto 1fr auto;
    }

    ${Breakpoint.smallMobile} {
      height: auto;
      padding-block: ${spacing[8]};
      grid-template-areas:
        'logo closeButton'
        'container container';
    }
  `,
  container: css`
    max-width: ${containerMaxWidth}px;
    width: 100%;
    height: ${headerHeight}px;
    ${styleUtils.display.flex()};
    justify-content: space-between;
    align-items: center;

    ${Breakpoint.tablet} {
      [data-title-divider] {
        margin-left: ${spacing[12]};
      }
    }

    ${Breakpoint.smallMobile} {
      grid-area: container;
      order: 2;
      justify-content: center;

      [data-title-divider] {
        display: none;
      }
    }
  `,
  titleAndTackerWrapper: css`
    ${styleUtils.display.flex()};
    align-items: center;
  `,
  titleAndTacker: css`
    ${styleUtils.display.flex()};
    gap: ${spacing[12]};
    align-items: center;
    margin-right: ${spacing[16]};
  `,
  divider: css`
    width: 2px;
    height: 16px;
    background-color: ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[20]};
  `,
  title: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.subdued};

    ${Breakpoint.tablet} {
      display: none;

      [data-title-divider] {
        display: none;
      }
    }
  `,
  closeButtonWrapper: css`
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-right: ${spacing[16]};

    ${Breakpoint.smallMobile} {
      grid-area: closeButton;
      order: 1;
    }
  `,
  closeButton: css`
    ${styleUtils.resetButton};
    ${styleUtils.flexCenter()};
    cursor: pointer;
    color: ${colorTokens.icon.default};
    margin-left: ${spacing[4]};
    border-radius: ${borderRadius[4]};
    transition: all 0.2s ease-in-out;

    &:hover {
      background-color: ${colorTokens.background.status.errorFail};
      color: ${colorTokens.icon.error};
    }

    &:focus {
      box-shadow: ${shadow.focus};
    }
  `,
  previewButton: css`
    color: ${colorTokens.text.title};
    svg {
      color: ${colorTokens.icon.default};
    }
  `,
  magicButton: css`
    display: inline-flex;
    align-items: center;
    gap: ${spacing[4]};
    padding-inline: ${spacing[4]};
    margin-left: ${spacing[4]};
  `,
};
