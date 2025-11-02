import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import config, { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';

import certificate2x from '@SharedImages/pro-placeholders/certificates-2x.webp';
import certificate from '@SharedImages/pro-placeholders/certificates.webp';

const isTutorPro = !!tutorConfig.tutor_pro_url;

const CertificateEmptyState = () => {
  if (isTutorPro) {
    return null;
  }

  return (
    <div css={styles.emptyState}>
      <img
        css={styles.placeholderImage}
        src={certificate}
        srcSet={`${certificate} 1x, ${certificate2x} 2x`}
        alt={__('Pro Placeholder', __TUTOR_TEXT_DOMAIN__)}
      />

      <div css={styles.featureAndActionWrapper}>
        <h5 css={styles.title}>{__('Award Students with Custom Certificates', __TUTOR_TEXT_DOMAIN__)}</h5>
        <div css={styles.featuresWithTitle}>
          <div>
            {__(
              'Celebrate success with personalized certificates. Recognize student achievements with unique designs that inspire and motivate students.',
              __TUTOR_TEXT_DOMAIN__,
            )}
          </div>

          <div css={styles.features}>
            <div css={styles.feature}>
              <SVGIcon name="materialCheck" width={20} height={20} style={styles.checkIcon} />
              <span>
                {__(
                  'Design personalized certificates that highlight their accomplishments and boost their confidence.',
                  __TUTOR_TEXT_DOMAIN__,
                )}
              </span>
            </div>
            <div css={styles.feature}>
              <SVGIcon name="materialCheck" width={20} height={20} style={styles.checkIcon} />
              <span>
                {__(
                  'Inspire them with a touch of credibility and recognition tailored just for them.',
                  __TUTOR_TEXT_DOMAIN__,
                )}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div css={styles.actionsButton}>
        <Button
          as="a"
          variant={'primary'}
          icon={<SVGIcon name={'crown'} width={24} height={24} />}
          href={config.TUTOR_PRICING_PAGE}
          target="_blank"
          rel="noreferrer"
        >
          {__('Get Tutor LMS Pro', __TUTOR_TEXT_DOMAIN__)}
        </Button>
      </div>
    </div>
  );
};

export default CertificateEmptyState;

const styles = {
  emptyState: css`
    padding-bottom: ${spacing[12]};
    ${styleUtils.display.flex('column')}
    gap: ${spacing[20]};
  `,
  placeholderImage: ({ notFound }: { notFound?: boolean }) => css`
    max-width: 100%;
    width: 100%;
    height: ${notFound ? '189px' : '312px;'};
    object-fit: cover;
    object-position: center;
    border-radius: ${borderRadius[6]};
  `,
  featureAndActionWrapper: css`
    ${styleUtils.display.flex('column')}
    align-items: center;
    gap: ${spacing[12]};
  `,
  title: css`
    ${typography.heading5('medium')}
    color: ${colorTokens.text.primary};
  `,
  featuresWithTitle: css`
    ${styleUtils.display.flex('column')}
    max-width: 500px;
    width: 100%;
    gap: ${spacing[8]};
    ${typography.body('regular')};
  `,
  features: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
  `,
  feature: css`
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
