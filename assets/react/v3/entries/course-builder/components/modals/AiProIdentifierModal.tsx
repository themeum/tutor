import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import SVGIcon from '@Atoms/SVGIcon';

import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

interface AiProIdentifierModalProps
  extends Omit<ModalProps, 'entireHeader' | 'headerChildren' | 'icon' | 'subtitle' | 'actions'> {
  image?: string;
  image2x?: string;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  featuresTitle?: string;
  features?: string[];
  message?: string;
  footer?: React.ReactNode;
}

const AiProIdentifierModal = ({
  title,
  message = __('This feature isn’t available on your current plan', 'tutor'),
  image,
  featuresTitle,
  features = [],
  closeModal,
  image2x,
  footer,
}: AiProIdentifierModalProps) => {
  return (
    <BasicModalWrapper onClose={closeModal} entireHeader={<span css={styles.message}>{message}</span>}>
      <div css={styles.wrapper}>
        <Show when={title}>
          <h4 css={styles.title}>{title}</h4>
        </Show>

        <Show when={image}>
          <img
            css={styles.image}
            src={image}
            alt={typeof title === 'string' ? title : __('Illustration')}
            srcSet={image2x ? `${image2x} 2x` : undefined}
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

export default AiProIdentifierModal;

const styles = {
  wrapper: css`
    width: 560px;
    padding: 0 ${spacing[24]} ${spacing[32]} ${spacing[24]};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[20]};
  `,
  message: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
    padding-left: ${spacing[8]};
  `,
  title: css`
    ${typography.heading4('medium')};
    color: ${colorTokens.text.primary};
    text-wrap: pretty;
  `,
  image: css`
    height: 232px;
    width: 100%;
    object-fit: cover;
    object-position: center;
    border-radius: ${borderRadius[8]};
  `,
  featuresTiTle: css`
    ${typography.heading6('medium')};
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
    ${typography.caption()};
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
