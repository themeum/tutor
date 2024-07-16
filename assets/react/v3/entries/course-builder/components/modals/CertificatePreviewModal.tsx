import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import type { Certificate } from '@CourseBuilderServices/course';
import { styleUtils } from '@Utils/style-utils';
import Show from '@Controls/Show';

export interface CertificatePreviewModalProps {
  certificates: Certificate[];
  selectedCertificate: string;
  currentCertificate: Certificate;
  onSelectCertificate: (certificate: Certificate) => void;
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

const CertificatePreviewModal = ({
  certificates,
  selectedCertificate: propsSelectedCertificate,
  currentCertificate: propsCurrentCertificate,
  onSelectCertificate,
  closeModal,
}: CertificatePreviewModalProps) => {
  const [selectedCertificate, setSelectedCertificate] = useState(propsSelectedCertificate);
  const [currentCertificate, setCurrentCertificate] = useState(propsCurrentCertificate);

  const closeButtonRef = useRef<HTMLButtonElement>(null);

  const currentCertificateIndex = certificates.findIndex((certificate) => certificate.key === currentCertificate.key);

  const previousIndex = Math.max(-1, currentCertificateIndex - 1);

  const nextIndex = Math.min(certificates.length, currentCertificateIndex + 1);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'ArrowLeft') {
        handleNavigate('previous');
      } else if (event.key === 'ArrowRight') {
        handleNavigate('next');
      } else if (event.key === 'Enter') {
        handleSelectCertificate(currentCertificate);
      } else if (event.key === 'Escape') {
        closeModal({ action: 'CLOSE' });
      }
    };

    window.addEventListener('keydown', handleKeyDown);

    return () => {
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [currentCertificateIndex, certificates]);

  useEffect(() => {
    if (closeButtonRef.current) {
      closeButtonRef.current.focus();
    }
  }, []);

  const handleSelectCertificate = (certificate: Certificate) => {
    if (certificate.key === selectedCertificate) {
      return;
    }
    onSelectCertificate(certificate);
    setSelectedCertificate(certificate.key);
  };

  const handleNavigate = (direction: 'previous' | 'next') => {
    if (direction === 'previous' && currentCertificateIndex > 0) {
      setCurrentCertificate(certificates[previousIndex]);
    } else if (direction === 'next' && currentCertificateIndex < certificates.length - 1) {
      setCurrentCertificate(certificates[nextIndex]);
    }
  };

  return (
    <div css={styles.container}>
      {currentCertificate && (
        <div css={styles.content}>
          <div css={styles.certificateAndActions}>
            <img css={styles.certificate} src={currentCertificate.preview_src} alt={currentCertificate.name} />

            <div css={styles.actionsWrapper}>
              <Tooltip content={__('Close', 'tutor')}>
                <button
                  ref={closeButtonRef}
                  type="button"
                  css={[styles.actionButton, styles.closeButton]}
                  onClick={() => {
                    closeModal({ action: 'CLOSE' });
                  }}
                >
                  <SVGIcon name="cross" width={40} height={40} />
                </button>
              </Tooltip>
              <Show when={currentCertificate.edit_url}>
                {(editUrl) => (
                  <Tooltip content={__('Edit in Certificate Builder', 'tutor')}>
                    <button
                      type="button"
                      css={[styles.actionButton, styles.editButton]}
                      onClick={() => {
                        window.open(editUrl, '_blank');
                      }}
                    >
                      <SVGIcon name="edit" width={40} height={40} />
                    </button>
                  </Tooltip>
                )}
              </Show>
            </div>
          </div>
        </div>
      )}

      <div css={styles.navigatorWrapper}>
        <div css={styles.navigator}>
          <button
            type="button"
            css={[styles.actionButton, styles.navigatorButton]}
            onClick={() => handleNavigate('previous')}
            disabled={previousIndex < 0}
          >
            <SVGIcon name="chevronLeft" width={40} height={40} />
          </button>
          <Button
            variant="primary"
            onClick={() => {
              handleSelectCertificate(currentCertificate);
              closeModal({ action: 'CONFIRM' });
            }}
            disabled={selectedCertificate === currentCertificate.key}
          >
            {selectedCertificate === currentCertificate.key ? __('Selected', 'tutor') : __('Select', 'tutor')}
          </Button>
          <button
            type="button"
            css={[styles.actionButton, styles.navigatorButton]}
            onClick={() => handleNavigate('next')}
            disabled={nextIndex > certificates.length - 1}
          >
            <SVGIcon name="chevronRight" width={40} height={40} />
          </button>
        </div>
      </div>
    </div>
  );
};

export default CertificatePreviewModal;

const styles = {
  container: css`
    width: 100%;
    height: 100%;
    ${styleUtils.display.flex('column')};
    justify-content: center;
    align-items: center;
    gap: ${spacing[16]};
  `,
  content: css`
    ${styleUtils.display.flex('column')};
    justify-content: center;
    align-items: center;
    object-fit: contain;
  `,
  certificateAndActions: css`
    position: relative;
    ${styleUtils.display.flex()};
    justify-content: center;
    align-items: center;
    gap: ${spacing[20]};
  `,
  certificate: css`
    max-height: 80dvh;
    height: 100%;
    object-fit: contain;
  `,
  actionsWrapper: css`
    position: absolute;
    top: 0;
    right: -${spacing[56]};
    bottom: 0;
    ${styleUtils.display.flex('column')};
    justify-content: space-between;
  `,
  actionButton: css`
    place-self: center start;
    ${styleUtils.resetButton};
    display: inline-flex;
    align-items: center;
    justify-content: center;

    svg {
      color: ${colorTokens.action.secondary.default};
      transition: color 0.3s ease-in-out;
    }
  `,
  closeButton: css`
    place-self: center start;
  `,
  editButton: css`
    place-self: center end;
  `,
  navigatorWrapper: css`
  `,
  navigator: css`
    ${styleUtils.display.flex()};
    gap: ${spacing[16]};
    justify-content: center;
    background: ${colorTokens.background.white};
    padding: ${spacing[12]};
    border-radius: ${borderRadius[8]};
  `,
  navigatorButton: css`
    svg {
      color: ${colorTokens.icon.default};
    }
    
    :disabled {
      cursor: not-allowed;
      svg {
        color: ${colorTokens.icon.hints};
      }
    }
  `,
};
