import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import { useModal } from '@Components/modals/Modal';
import CertificatePreviewModal from '@CourseBuilderComponents/modals/CertificatePreviewModal';
import { useCourseDetailsQuery, type Certificate } from '@CourseBuilderServices/course';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';

import Show from '@Controls/Show';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { styleUtils } from '@Utils/style-utils';

interface CertificateCardProps {
  isSelected: boolean;
  setSelectedCertificate: (id: string) => void;
  orientation: 'landscape' | 'portrait';
  data: Certificate;
}

const CertificateCard = ({ isSelected = true, setSelectedCertificate, data, orientation }: CertificateCardProps) => {
  const courseId = getCourseId();
  const { showModal } = useModal();
  const courseDetailsQuery = useCourseDetailsQuery(courseId);
  const certificatesData =
    courseDetailsQuery.data?.course_certificates_templates.filter(
      (certificate) => certificate.orientation === orientation
    ) ?? [];

  return (
    <div
      css={styles.wrapper({
        isSelected,
        isLandScape: orientation === 'landscape',
      })}
    >
      <div data-overlay />
      <Show
        when={data.preview_src}
        fallback={
          <div css={styles.emptyCard}>
            <SVGIcon name="outlineNone" width={49} height={49} />
            <span>{__('None', 'tutor')}</span>
          </div>
        }
      >
        {(image) => {
          return <img css={styles.certificateImage} src={image} alt={data.name} />;
        }}
      </Show>
      <Show when={data.preview_src || !isSelected}>
        <div data-footer-actions css={styles.footerWrapper}>
          <Show when={data.preview_src}>
            <Button
              variant="secondary"
              isOutlined
              size="small"
              onClick={() => {
                showModal({
                  component: CertificatePreviewModal,
                  props: {
                    certificates: certificatesData,
                    currentCertificate: data,
                    selectedCertificate: data.key,
                    onSelectCertificate: (certificate: Certificate): void => {
                      setSelectedCertificate(certificate.key);
                    },
                  },
                });
              }}
            >
              {__('Preview', 'tutor')}
            </Button>
          </Show>
          <Show when={!isSelected}>
            <Button variant="primary" size="small" onClick={() => setSelectedCertificate(data.key)}>
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
