import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

interface CertificateCardProps {
  isSelected: boolean;
  setSelectedCertificate: (id: string) => void;
  orientation: 'landscape' | 'portrait';
  // biome-ignore lint/suspicious/noExplicitAny: <explanation>
  data: any;
}

const CertificateCard = ({ isSelected = true, setSelectedCertificate, data, orientation }: CertificateCardProps) => {
  return (
    <div
      css={styles.wrapper({
        isSelected,
        isLandScape: orientation === 'landscape',
      })}
    >
      <div data-overlay />
      <Show
        when={data.image}
        fallback={
          <div css={styles.emptyCard}>
            <SVGIcon name="outlineNone" width={49} height={49} />
            <span>{__('None', 'tutor')}</span>
          </div>
        }
      >
        {(image) => {
          return <img css={styles.certificateImage} src={image} alt={data.title} />;
        }}
      </Show>
      <Show when={data.image || !isSelected}>
        <div data-footer-actions css={styles.footerWrapper}>
          <Show when={data.image}>
            <Button
              variant="secondary"
              isOutlined
              size="small"
              onClick={() => {
                window.open(data.image, '_blank');
              }}
            >
              {__('Preview', 'tutor')}
            </Button>
          </Show>
          <Show when={!isSelected}>
            <Button variant="primary" size="small" onClick={() => setSelectedCertificate(data.id)}>
              {__('Select', 'tutor')}
            </Button>
          </Show>
        </div>
      </Show>

      <div
        css={styles.checkIcon({
          isSelected,
        })}
      >
        <SVGIcon name="checkFilled" width={32} height={32} />
      </div>
    </div>
  );
};

export default CertificateCard;

const styles = {
  wrapper: ({
    isSelected = false,
    isLandScape = false,
  }: {
    isSelected: boolean;
    isLandScape: boolean;
  }) => css`
    ${styleUtils.centeredFlex};
    background-color: ${colorTokens.surface.courseBuilder};
    max-height: ${isLandScape ? '154px' : '217px'};
    min-height: ${isLandScape ? '154px' : '217px'};
    height: 100%;
    position: relative;
    outline: ${isSelected ? '2px' : '1px'} solid ${isSelected ? colorTokens.stroke.brand : colorTokens.stroke.default};
    border-radius: ${borderRadius.card};
    transition: all 0.15s ease-in-out;

    [data-overlay] {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      border-radius: ${borderRadius.card};;
    }

    ${
      isSelected &&
      css`
        [data-overlay] {
          background: ${colorTokens.brand.blue};
          opacity: 0.1;
        }
      `
    }

    &:hover {
      border-color: ${colorTokens.stroke.brand};

      [data-footer-actions] {
        opacity: 1;
      }

      [data-overlay] {
        background: ${colorTokens.brand.blue};
        opacity: 0.1;
      }
  }
  `,
  emptyCard: css`
    ${styleUtils.flexCenter()};
    flex-direction: column;
    height: 100%;
    width: 100%;
    gap: ${spacing[8]};
    ${typography.caption('medium')};

    svg {
      color: ${colorTokens.color.black[20]};
    }
  `,
  certificateImage: css`
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: ${borderRadius.card};
  `,
  footerWrapper: css`
    opacity: 0;
    position: absolute;
    left: 0px;
    right: 0px;
    bottom: 0px;
    ${styleUtils.flexCenter()};
    align-items: center;
    gap: ${spacing[4]};
    padding-block: ${spacing[8]};
    background: ${colorTokens.bg.white};
    border-bottom-left-radius: ${borderRadius.card};
    border-bottom-right-radius: ${borderRadius.card};
  `,
  checkIcon: ({
    isSelected = false,
  }: {
    isSelected: boolean;
  }) => css`
    opacity: ${isSelected ? 1 : 0};
    position: absolute;
    top: -14px;
    right: -14px;
    border-bottom-left-radius: ${borderRadius.card};

    svg {
      color: ${colorTokens.icon.brand};
    }
  `,
};
