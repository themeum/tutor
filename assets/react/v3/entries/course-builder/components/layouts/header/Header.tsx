import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import MagicButton from '@TutorShared/atoms/MagicButton';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Tooltip from '@TutorShared/atoms/Tooltip';

import HeaderActions from '@CourseBuilderComponents/layouts/header/HeaderActions';
import Tracker from '@CourseBuilderComponents/layouts/Tracker';
import AICourseBuilderModal from '@CourseBuilderComponents/modals/AICourseBuilderModal';
import Logo from '@TutorShared/components/Logo';
import ConfirmationModal from '@TutorShared/components/modals/ConfirmationModal';
import { useModal } from '@TutorShared/components/modals/Modal';
import ProIdentifierModal from '@TutorShared/components/modals/ProIdentifierModal';
import SetupOpenAiModal from '@TutorShared/components/modals/SetupOpenAiModal';

import { useCourseNavigator } from '@CourseBuilderContexts/CourseNavigatorContext';
import type { CourseDetailsResponse, CourseFormData } from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { tutorConfig } from '@TutorShared/config/config';
import { CURRENT_VIEWPORT, TutorRoles, WP_ADMIN_BAR_HEIGHT } from '@TutorShared/config/constants';
import {
  borderRadius,
  Breakpoint,
  colorTokens,
  containerMaxWidth,
  headerHeight,
  spacing,
  zIndex,
} from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';

import generateCourse2x from '@SharedImages/pro-placeholders/generate-course-2x.webp';
import generateCourse from '@SharedImages/pro-placeholders/generate-course.webp';

const courseId = getCourseId();

const Header = () => {
  const form = useFormContext<CourseFormData>();
  const queryClient = useQueryClient();
  const { currentIndex } = useCourseNavigator();
  const { showModal } = useModal();

  const totalEnrolledStudents = (queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse)
    ?.total_enrolled_student;
  const isFormDirty = form.formState.dirtyFields && Object.keys(form.formState.dirtyFields).length > 0;
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const isOpenAiEnabled = tutorConfig.settings?.chatgpt_enable === 'on';
  const hasOpenAiAPIKey = tutorConfig.settings?.chatgpt_key_exist;
  const isAdmin = tutorConfig.current_user.roles?.includes(TutorRoles.ADMINISTRATOR);
  const hasWpAdminAccess = tutorConfig.settings?.hide_admin_bar_for_users === 'off';

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
        component: ConfirmationModal,
        props: {
          title: __('Do you want to exit without saving?', 'tutor'),
          description: __('You’re about to leave the course creation process without saving your changes.', 'tutor'),
          confirmButtonText: __('Yes, exit without saving', 'tutor'),
          confirmButtonVariant: 'danger',
          cancelButtonText: __('Continue editing', 'tutor'),
          maxWidth: 445,
        },
      }).then((result) => {
        if (result.action === 'CONFIRM') {
          const isFormWpAdmin = window.location.href.includes('wp-admin');

          window.location.href = isFormWpAdmin
            ? tutorConfig.backend_course_list_url
            : tutorConfig.frontend_course_list_url;
        }
      });
    } else {
      const isFormWpAdmin = window.location.href.includes('wp-admin');

      window.location.href = isFormWpAdmin ? tutorConfig.backend_course_list_url : tutorConfig.frontend_course_list_url;
    }
  };

  return (
    <div css={styles.wrapper(isAdmin || hasWpAdminAccess)}>
      <Logo />

      <div css={styles.container}>
        <div css={styles.titleAndTackerWrapper}>
          <div css={styles.titleAndTacker}>
            <h6 css={styles.title}>{__('Course Builder', 'tutor')}</h6>
            <span css={styles.divider} data-title-divider />
            <Tracker />
          </div>

          <Show when={currentIndex === 0 && totalEnrolledStudents === 0 && (isOpenAiEnabled || !isTutorPro)}>
            <span css={styles.divider} />

            <div css={styleUtils.flexCenter()}>
              <MagicButton variant="plain" css={styles.magicButton} onClick={handleAiButtonClick}>
                <SVGIcon name="magicAiColorize" width={24} height={24} />
                <Show when={CURRENT_VIEWPORT.isAboveTablet}>{__('Generate with AI', 'tutor')}</Show>
              </MagicButton>
            </div>
          </Show>
        </div>

        <Show when={CURRENT_VIEWPORT.isAboveDesktop}>
          <HeaderActions />
        </Show>
      </div>

      <div css={styles.closeButtonWrapper}>
        <Show when={!CURRENT_VIEWPORT.isAboveDesktop}>
          <HeaderActions />
        </Show>
        <Tooltip delay={200} content={__('Exit', 'tutor')} placement="left">
          <Button
            isIconOnly
            size="small"
            variant="danger"
            buttonCss={styles.closeButton}
            aria-label={__('Exit', 'tutor')}
            onClick={handleExitButtonClick}
            icon={<SVGIcon name="cross" width={32} height={32} />}
          />
        </Tooltip>
      </div>
    </div>
  );
};

export default Header;

const styles = {
  wrapper: (hasAdminBar: boolean) => css`
    height: ${headerHeight}px;
    width: 100%;
    background-color: ${colorTokens.surface.navbar};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    display: grid;
    grid-template-columns: 1fr ${containerMaxWidth}px 1fr;
    align-items: center;
    position: sticky;
    top: ${hasAdminBar ? WP_ADMIN_BAR_HEIGHT : '0px'};
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
      row-gap: ${spacing[8]};
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
      height: auto;
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
      margin-right: ${spacing[8]};
    }
  `,
  closeButton: css`
    background-color: transparent;
    svg {
      color: ${colorTokens.icon.default};
    }

    &:hover,
    &:focus {
      background-color: ${colorTokens.background.status.errorFail};
      svg {
        color: ${colorTokens.icon.error};
      }
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
