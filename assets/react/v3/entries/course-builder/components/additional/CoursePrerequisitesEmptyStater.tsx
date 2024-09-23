import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import config, { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

import addonDisabled2x from '@Images/addon-disabled-2x.webp';
import addonDisabled from '@Images/addon-disabled.webp';
import prerequisites2x from '@Images/pro-placeholders/prerequisites-2x.webp';
import prerequisites from '@Images/pro-placeholders/prerequisites.webp';

const isTutorPro = !!tutorConfig.tutor_pro_url;

const CoursePrerequisitesEmptyStater = () => {
  return (
    <div css={styles.emptyState}>
      <img
        css={styles.placeholderImage}
        src={!isTutorPro ? prerequisites : addonDisabled}
        srcSet={
          !isTutorPro ? `${prerequisites} 1x, ${prerequisites2x} 2x` : `${addonDisabled} 1x, ${addonDisabled2x} 2x`
        }
        alt={__('Pro Placeholder', 'tutor')}
      />

      <div css={styles.featureAndActionWrapper}>
        <div css={styles.featuresWithTitle}>
          <div css={isTutorPro && styleUtils.text.align.center}>
            {!isTutorPro
              ? __('Level up course structure with Tutor LMS course prerequisites', 'tutor')
              : __('You can use this feature by activating Prerequisites addons', 'tutor')}
          </div>
          <Show when={!isTutorPro}>
            <div css={styles.features}>
              <div css={styles.feature}>
                <SVGIcon name="materialCheck" width={20} height={20} style={styles.checkIcon} />
                <span>
                  {__(
                    'Easily set course prerequisites to a course as stepping stones to create a structured learning path',
                    'tutor',
                  )}
                </span>
              </div>
              <div css={styles.feature}>
                <SVGIcon name="materialCheck" width={20} height={20} style={styles.checkIcon} />
                <span>
                  {__('Offer customized learning experiences by setting multiple prerequisites for a course', 'tutor')}
                </span>
              </div>
            </div>
          </Show>
        </div>
      </div>

      <Show when={isTutorPro}>
        <div css={styles.actionsButton}>
          <Button
            size="small"
            variant="secondary"
            icon={<SVGIcon name="linkExternal" width={24} height={24} />}
            onClick={() => {
              window.open(config.TUTOR_ADDONS_PAGE, '_blank', 'noopener');
            }}
          >
            {__('Enable Prerequisites Addon', 'tutor')}
          </Button>
        </div>
      </Show>
    </div>
  );
};

export default CoursePrerequisitesEmptyStater;

const styles = {
  emptyState: css`
    padding: ${spacing[12]} ${spacing[12]} ${spacing[24]} ${spacing[12]};
    ${styleUtils.display.flex('column')}
    gap: ${spacing[20]};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius.card};
    background-color: ${colorTokens.background.white};
  `,
  placeholderImage: css`
    max-width: 100%;
    width: 100%;
    height: 112px;
    object-fit: cover;
    object-position: center;
    border-radius: ${borderRadius[6]};
  `,
  featureAndActionWrapper: css`
    ${styleUtils.display.flex('column')}
    align-items: center;
    gap: ${spacing[12]};
    padding-inline: ${spacing[4]};
  `,
  featuresWithTitle: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
    ${typography.caption('regular')};
  `,
  features: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
  `,
  feature: css`
    ${typography.caption()};
    ${styleUtils.display.flex()}
    gap: ${spacing[12]};
    color: ${colorTokens.text.title};
    text-wrap: pretty;
  `,
  checkIcon: css`
    flex-shrink: 0;
    color: ${colorTokens.text.success};
  `,
  actionsButton: css`
    ${styleUtils.flexCenter()}
    margin-top: ${spacing[4]};
  `,
};
