import { type SerializedStyles, css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@TutorShared/atoms/Button';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';

import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';

interface SuccessModalProps {
  title: string;
  description?: string;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  image?: string;
  imageAlt?: string;
  image2x?: string;
  actions?: React.ReactNode;
  wrapperCss?: SerializedStyles;
  bodyCss?: SerializedStyles;
}

const SuccessModal = ({
  title,
  description,
  image,
  image2x,
  imageAlt,
  closeModal,
  actions,
  wrapperCss,
  bodyCss,
}: SuccessModalProps) => {
  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} entireHeader={<>&nbsp;</>} maxWidth={408}>
      <div css={[styles.wrapper, wrapperCss]}>
        <Show when={image}>
          <img
            src={image}
            srcSet={image2x ? `${image} 1x, ${image2x} 2x` : undefined}
            alt={imageAlt}
            css={styles.image}
          />
        </Show>
        <div css={[styles.body, bodyCss]}>
          <h5 css={typography.heading5('medium')}>{title}</h5>
          <p css={styles.message}>{description}</p>
        </div>
        <div css={styles.footer}>
          <Show
            when={actions}
            fallback={
              <Button
                onClick={() =>
                  closeModal({
                    action: 'CLOSE',
                  })
                }
                size="small"
              >
                {__('Ok', __TUTOR_TEXT_DOMAIN__)}
              </Button>
            }
          >
            {actions}
          </Show>
        </div>
      </div>
    </BasicModalWrapper>
  );
};

export default SuccessModal;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')};
    padding: ${spacing[24]};
    gap: ${spacing[24]};
  `,
  image: css`
    width: 100%;
    height: auto;
    object-position: center;
    object-fit: contain;
  `,
  body: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
  `,
  message: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
  `,
  footer: css`
    ${styleUtils.display.flex()};
    justify-content: flex-end;
    gap: ${spacing[16]};
    padding-top: ${spacing[8]};
  `,
};
