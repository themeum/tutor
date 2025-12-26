import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';

import config from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';

interface ProIdentifierModalProps
  extends Omit<ModalProps, 'entireHeader' | 'headerChildren' | 'icon' | 'subtitle' | 'actions'> {
  image?: string;
  image2x?: string;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  featuresTitle?: string;
  features?: string[];
  message?: string;
  footer?: React.ReactNode;
}

const defaults = {
  title: (
    <>
      {__('Upgrade to Tutor LMS Pro today and experience the power of ', __TUTOR_TEXT_DOMAIN__)}
      <span css={styleUtils.aiGradientText}>{__('AI Studio', __TUTOR_TEXT_DOMAIN__)}</span>
    </>
  ),
  message: __('Upgrade your plan to access the AI feature', __TUTOR_TEXT_DOMAIN__),
  featuresTitle: __('Don’t miss out on this game-changing feature!', __TUTOR_TEXT_DOMAIN__),
  features: [
    __('Generate a complete course outline in seconds!', __TUTOR_TEXT_DOMAIN__),
    __(
      'Let the AI Studio create Quizzes on your behalf and give your brain a well-deserved break.',
      __TUTOR_TEXT_DOMAIN__,
    ),
    __('Generate images, customize backgrounds, and even remove unwanted objects with ease.', __TUTOR_TEXT_DOMAIN__),
    __('Say goodbye to typos and grammar errors with AI-powered copy editing.', __TUTOR_TEXT_DOMAIN__),
  ],
  footer: (
    <Button
      onClick={() => window.open(config.TUTOR_PRICING_PAGE, '_blank', 'noopener')}
      icon={<SVGIcon name="crown" width={24} height={24} />}
    >
      {__('Get Tutor LMS Pro', __TUTOR_TEXT_DOMAIN__)}
    </Button>
  ),
};

const ProIdentifierModal = ({
  title = defaults.title,
  message = defaults.message,
  featuresTitle = defaults.featuresTitle,
  features = defaults.features,
  closeModal,
  image,
  image2x,
  footer = defaults.footer,
}: ProIdentifierModalProps) => {
  return (
    <BasicModalWrapper onClose={closeModal} entireHeader={<span css={styles.message}>{message}</span>} maxWidth={496}>
      <div css={styles.wrapper}>
        <Show when={title}>
          <h4 css={styles.title}>{title}</h4>
        </Show>

        <Show when={image}>
          <img
            css={styles.image}
            src={image}
            alt={typeof title === 'string' ? title : __('Illustration', __TUTOR_TEXT_DOMAIN__)}
            srcSet={image2x ? `${image} ${image2x} 2x` : undefined}
          />
        </Show>

        <Show when={featuresTitle}>
          <h6 css={styles.featuresTiTle}>{featuresTitle}</h6>
        </Show>
        <Show when={features.length}>
          <div css={styles.features}>
            <For each={features}>
              {(feature, index) => (
                <div key={index} css={styles.feature}>
                  <SVGIcon name="materialCheck" width={20} height={20} style={styles.checkIcon} />
                  <span>{feature}</span>
                </div>
              )}
            </For>
          </div>
        </Show>

        <Show when={footer}>{footer}</Show>
      </div>
    </BasicModalWrapper>
  );
};

export default ProIdentifierModal;

const styles = {
  wrapper: css`
    padding: 0 ${spacing[24]} ${spacing[32]} ${spacing[24]};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
  `,
  message: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
    padding-left: ${spacing[8]};
    padding-top: ${spacing[24]};
    padding-bottom: ${spacing[4]};
  `,
  title: css`
    ${typography.heading6('medium')};
    color: ${colorTokens.text.primary};
    text-wrap: pretty;
  `,
  image: css`
    height: 270px;
    width: 100%;
    object-fit: cover;
    object-position: center;
    border-radius: ${borderRadius[8]};
  `,
  featuresTiTle: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.primary};
    text-wrap: pretty;
  `,
  features: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
    padding-right: ${spacing[48]};
  `,
  feature: css`
    ${styleUtils.display.flex()};
    gap: ${spacing[12]};
    ${typography.small()};
    color: ${colorTokens.text.title};

    span {
      text-wrap: pretty;
    }
  `,
  checkIcon: css`
    flex-shrink: 0;
    color: ${colorTokens.text.success};
  `,
};
