import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import Button from '@Atoms/Button';
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

import config, { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, containerMaxWidth, headerHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { useCourseNavigator } from '@CourseBuilderContexts/CourseNavigatorContext';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { styleUtils } from '@Utils/style-utils';

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

  return (
    <div css={styles.wrapper}>
      <Logo />

      <div css={styles.container}>
        <div css={styles.titleAndTackerWrapper}>
          <div css={styles.titleAndTacker}>
            <h6 css={styles.title}>{__('Course Builder', 'tutor')}</h6>
            <span css={styles.divider} />
            <Tracker />
          </div>

          <Show when={currentIndex === 0 && (isOpenAiEnabled || !isTutorPro)}>
            <span css={styles.divider} />

            <div css={styleUtils.flexCenter()}>
              <MagicButton
                variant="plain"
                css={styles.magicButton}
                onClick={() => {
                  if (!isTutorPro) {
                    showModal({
                      component: ProIdentifierModal,
                      props: {
                        title: (
                          <>
                            {__('Upgrade to Tutor LMS Pro today and experience the power of ', 'tutor')}
                            <span css={styles.aiGradientText}>{__('AI Studio', 'tutor')} </span>
                          </>
                        ),
                        featuresTitle: __('Don’t miss out on this game-changing feature!', 'tutor'),
                        image: generateCourse,
                        image2x: generateCourse2x,
                        features: [
                          __('Generate a complete course outline in seconds!', 'tutor'),
                          __(
                            ' Let the AI Studio create Quizzes on your behalf and give your brain a well-deserved break.',
                            'tutor',
                          ),
                          __(
                            'Generate images, customize backgrounds, and even remove unwanted objects with ease.',
                            'tutor',
                          ),
                          __('Say goodbye to typos and grammar errors with AI-powered copy editing.', 'tutor'),
                        ],
                        footer: (
                          <Button
                            onClick={() => window.open(config.TUTOR_PRICING_PAGE, '_blank', 'noopener')}
                            icon={<SVGIcon name="crown" width={24} height={24} />}
                          >
                            {__('Get Tutor LMS Pro', 'tutor')}
                          </Button>
                        ),
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
                }}
              >
                <SVGIcon name="magicAiColorize" width={32} height={32} />
                {__('Generate with AI', 'tutor')}
              </MagicButton>
            </div>
          </Show>
        </div>

        <HeaderActions />
      </div>

      <div css={styles.closeButtonWrapper}>
        <Tooltip delay={200} content={__('Exit', 'tutor')} placement="left">
          <button
            type="button"
            css={styles.closeButton}
            onClick={() => {
              if (isFormDirty) {
                showModal({
                  component: ExitCourseBuilderModal,
                });
              } else {
                const isFormWpAdmin = window.location.href.includes('wp-admin');

                window.location.href = isFormWpAdmin
                  ? tutorConfig.backend_course_list_url
                  : tutorConfig.frontend_course_list_url;
              }
            }}
          >
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
  `,
  container: css`
    max-width: ${containerMaxWidth}px;
    width: 100%;
    height: ${headerHeight}px;
    ${styleUtils.display.flex()};
    justify-content: space-between;
    align-items: center;
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
  `,
  closeButtonWrapper: css`
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-right: ${spacing[16]};
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
  aiGradientText: css`
    background: ${colorTokens.text.ai.gradient};
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  `,
  magicButton: css`
    display: inline-flex;
    align-items: center;
    gap: ${spacing[4]};
    padding-inline: 0px;
    margin-left: ${spacing[4]};
  `,
};
