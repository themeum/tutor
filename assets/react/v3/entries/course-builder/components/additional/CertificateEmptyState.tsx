import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import config, { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';

import Show from '@Controls/Show';
import addonDisabled2x from '@Images/addon-disabled-2x.webp';
import addonDisabled from '@Images/addon-disabled.webp';
import certificate2x from '@Images/pro-placeholders/certificates-2x.webp';
import certificate from '@Images/pro-placeholders/certificates.webp';

const isTutorPro = !!tutorConfig.tutor_pro_url;

const CertificateEmptyState = () => {
  return (
    <div css={styles.emptyState}>
      <img
        css={styles.placeholderImage}
        src={!isTutorPro ? certificate : addonDisabled}
        srcSet={!isTutorPro ? `${certificate} 1x, ${certificate2x} 2x` : `${addonDisabled} 1x, ${addonDisabled2x} 2x`}
        alt={!isTutorPro ? __('Pro Placeholder', 'tutor') : __('Addon Disabled', 'tutor')}
      />

      <div css={styles.featureAndActionWrapper}>
        <Show when={!isTutorPro}>
          <h5 css={styles.title}>{__('Your students deserve certificates!', 'tutor')}</h5>
        </Show>
        <div css={styles.featuresWithTitle}>
          <Show
            when={!isTutorPro}
            fallback={
              <h6 css={typography.heading6('medium')}>
                {__('You can use this feature by enabling Certificate Addon', 'tutor')}
              </h6>
            }
          >
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
          </Show>
        </div>
      </div>

      <div css={styles.actionsButton}>
        <Button
          variant={!isTutorPro ? 'primary' : 'secondary'}
          icon={<SVGIcon name={!isTutorPro ? 'crown' : 'linkExternal'} width={24} height={24} />}
          onClick={() => {
            window.open(!isTutorPro ? config.TUTOR_PRICING_PAGE : config.TUTOR_ADDONS_PAGE, '_blank', 'noopener');
          }}
        >
          {!isTutorPro ? __('Get Tutor LMS Pro', 'tutor') : __('Enable Certificate Addon', 'tutor')}
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
