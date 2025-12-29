import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';

import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type Certificate } from '@TutorShared/utils/types';

interface CertificateCardProps {
  selectedCertificate: string;
  orientation: 'landscape' | 'portrait';
  data: Certificate;
  onSelectCertificate: (key: string) => void;
  onPreviewCertificate: (data: Certificate) => void;
}

const CertificateCard = ({
  selectedCertificate = '',
  data,
  orientation,
  onSelectCertificate,
  onPreviewCertificate,
}: CertificateCardProps) => {
  return (
    <div
      css={styles.wrapper({
        isSelected: selectedCertificate === data.key,
        isLandScape: orientation === 'landscape',
      })}
    >
      <div
        data-overlay
        onClick={() => onSelectCertificate(data.key)}
        onKeyDown={(event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            onSelectCertificate(data.key);
          }
        }}
      />
      <Show
        when={data.preview_src}
        fallback={
          <div css={styles.emptyCard}>
            <SVGIcon name="outlineNone" width={49} height={49} />
            <span>{__('None', __TUTOR_TEXT_DOMAIN__)}</span>
          </div>
        }
      >
        {(image) => {
          return <img css={styles.certificateImage} src={image} alt={data.name} />;
        }}
      </Show>
      <Show when={data.preview_src || data.key !== selectedCertificate}>
        <div data-footer-actions css={styles.footerWrapper}>
          <Show when={data.preview_src}>
            <Button variant="secondary" isOutlined size="small" onClick={() => onPreviewCertificate(data)}>
              {__('Preview', __TUTOR_TEXT_DOMAIN__)}
            </Button>
          </Show>
          <Show when={data.key !== selectedCertificate}>
            <Button variant="primary" size="small" onClick={() => onSelectCertificate(data.key)}>
              {__('Select', __TUTOR_TEXT_DOMAIN__)}
            </Button>
          </Show>
        </div>
      </Show>

      <div
        css={styles.checkIcon({
          isSelected: selectedCertificate === data.key,
        })}
      >
        <SVGIcon name="checkFilledWhite" width={32} height={32} />
      </div>
    </div>
  );
};

export default CertificateCard;

const styles = {
  wrapper: ({ isSelected = false, isLandScape = false }: { isSelected: boolean; isLandScape: boolean }) => css`
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
      border-radius: ${borderRadius.card};
    }

    ${isSelected &&
    css`
      [data-overlay] {
        background: ${colorTokens.brand.blue};
        opacity: 0.1;
      }
    `}

    &:hover, &:focus-within {
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
    object-fit: contain;
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
  checkIcon: ({ isSelected = false }: { isSelected: boolean }) => css`
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
