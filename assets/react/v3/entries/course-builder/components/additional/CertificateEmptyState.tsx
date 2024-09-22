import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import config from '@Config/config';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';

import certificate2x from '@Images/pro-placeholders/certificates-2x.webp';
import certificate from '@Images/pro-placeholders/certificates.webp';

const CertificateEmptyState = () => {
  return (
    <div css={styles.emptyState}>
      <img
        css={styles.placeholderImage}
        src={certificate}
        srcSet={`${certificate} 1x, ${certificate2x} 2x`}
        alt={__('Pro Placeholder', 'tutor')}
      />

      <div css={styles.featureAndActionWrapper}>
        <h5 css={styles.title}>{__('Your students deserve certificates!', 'tutor')}</h5>
        <div css={styles.featuresWithTitle}>
          <div>
            {__(
              `Elevate your students' achievements with a custom certificate! Use our certificate builder to  Inspire them with a touch of credibility and recognition tailored just for them.`,
              'tutor',
            )}
          </div>
          <div css={styles.features}>
            <div css={styles.feature}>
              <SVGIcon name="materialCheck" width={20} height={20} style={styles.checkIcon} />
              <span>
                {__(
                  'Design personalized certificates that highlight their accomplishments and boost their confidence.',
                  'tutor',
                )}
              </span>
            </div>
            <div css={styles.feature}>
              <SVGIcon name="materialCheck" width={20} height={20} style={styles.checkIcon} />
              <span>
                {__('Inspire them with a touch of credibility and recognition tailored just for them.', 'tutor')}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div css={styles.actionsButton}>
        <Button
          icon={<SVGIcon name="crown" width={24} height={24} />}
          onClick={() => {
            window.open(config.TUTOR_PRICING_PAGE, '_blank', 'noopener');
          }}
        >
          {__('Get Tutor LMS Pro', 'tutor')}
        </Button>
      </div>
    </div>
  );
};

export default CertificateEmptyState;

const styles = {
  emptyState: css`
    padding-block: ${spacing[16]} ${spacing[12]};
    ${styleUtils.display.flex('column')}
    gap: ${spacing[20]};
  `,
  placeholderImage: ({
    notFound,
  }: {
    notFound?: boolean;
  }) => css`
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
    width: 500px;
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
