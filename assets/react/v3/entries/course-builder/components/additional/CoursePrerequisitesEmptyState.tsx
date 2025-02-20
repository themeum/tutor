import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';

import prerequisites2x from '@SharedImages/pro-placeholders/prerequisites-2x.webp';
import prerequisites from '@SharedImages/pro-placeholders/prerequisites.webp';

const isTutorPro = !!tutorConfig.tutor_pro_url;

const CoursePrerequisitesEmptyState = () => {
  return (
    <div css={styles.emptyState}>
      <img
        css={styles.placeholderImage}
        src={prerequisites}
        srcSet={`${prerequisites} 1x, ${prerequisites2x} 2x`}
        alt={__('Pro Placeholder', 'tutor')}
      />

      <div css={styles.featureAndActionWrapper}>
        <div css={styles.featuresWithTitle}>
          <div>{__('Guide Students with Course Prerequisites', 'tutor')}</div>
          <Show when={!isTutorPro}>
            <div css={styles.features}>
              <div css={styles.feature}>
                <SVGIcon name="materialCheck" width={20} height={20} style={styles.checkIcon} />
                <span>
                  {__('Easily set prerequisites to structure your courses and guide student progress.', 'tutor')}
                </span>
              </div>
              <div css={styles.feature}>
                <SVGIcon name="materialCheck" width={20} height={20} style={styles.checkIcon} />
                <span>
                  {__('Offer customized learning journeys by setting multiple prerequisites for any course.', 'tutor')}
                </span>
              </div>
            </div>
          </Show>
        </div>
      </div>
    </div>
  );
};

export default CoursePrerequisitesEmptyState;

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
    ${typography.caption('medium')};
  `,
  features: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
  `,
  feature: css`
    ${typography.small()};
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
