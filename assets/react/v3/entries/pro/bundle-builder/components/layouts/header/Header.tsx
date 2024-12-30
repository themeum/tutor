import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';

import { useModal } from '@Components/modals/Modal';
import HeaderActions from '@BundleBuilderComponents/layouts/header/HeaderActions';
import Logo from '@BundleBuilderComponents/layouts/header/Logo';
import ExitCourseBuilderModal from '@CourseBuilderComponents/modals/ExitCourseBuilderModal';

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
import { styleUtils } from '@Utils/style-utils';

import { type BundleFormData } from '@BundleBuilderServices/bundle';
import { CURRENT_VIEWPORT, WP_ADMIN_BAR_HEIGHT } from '@Config/constants';

const Header = () => {
  const form = useFormContext<BundleFormData>();
  const { showModal } = useModal();

  const isFormDirty = form.formState.isDirty;

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
            <h6 css={styles.title}>{__('Course Bundle', 'tutor')}</h6>
          </div>
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
    top: ${WP_ADMIN_BAR_HEIGHT};
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
