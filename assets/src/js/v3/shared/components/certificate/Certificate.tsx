import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Tooltip from '@TutorShared/atoms/Tooltip';
import Tabs from '@TutorShared/molecules/Tabs';

import CertificatePreviewModal from '@TutorShared/components/modals/CertificatePreviewModal';
import { useModal } from '@TutorShared/components/modals/Modal';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons, CURRENT_VIEWPORT } from '@TutorShared/config/constants';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type Certificate as CertificateType } from '@TutorShared/utils/types';
import { isAddonEnabled } from '@TutorShared/utils/util';

import notFound2x from '@SharedImages/not-found-2x.webp';
import notFound from '@SharedImages/not-found.webp';

import CertificateCard from './CertificateCard';
import CertificateEmptyState from './CertificateEmptyState';

type CertificateTabValue = 'templates' | 'custom_certificates';

interface CertificateTabItem {
  label: string;
  value: CertificateTabValue;
}

interface CertificateProps {
  isSidebarVisible?: boolean;
  certificateTemplates: CertificateType[];
  currentCertificateKey: string;
  onSelect: (certificateKey: string) => void;
}

const isTutorPro = !!tutorConfig.tutor_pro_url;
const isCertificateAddonEnabled = isAddonEnabled(Addons.TUTOR_CERTIFICATE);

const Certificate = ({
  isSidebarVisible = true,
  currentCertificateKey,
  onSelect,
  certificateTemplates,
}: CertificateProps) => {
  const certificatesData = certificateTemplates ?? [];
  const defaultTemplates = certificatesData.filter((certificate) => certificate.is_default);

  const [activeCertificateTab, setActiveCertificateTab] = useState<CertificateTabValue>('templates');
  const [activeOrientation, setActiveOrientation] = useState<'landscape' | 'portrait'>('landscape');
  const [selectedCertificate, setSelectedCertificate] = useState(currentCertificateKey);
  const { showModal } = useModal();

  const landScapeCertificates = certificatesData.some(
    (certificate) =>
      certificate.orientation === 'landscape' &&
      (activeCertificateTab === 'templates' ? certificate.is_default : !certificate.is_default),
  );

  const portraitCertificates = certificatesData.some(
    (certificate) =>
      certificate.orientation === 'portrait' &&
      (activeCertificateTab === 'templates' ? certificate.is_default : !certificate.is_default),
  );

  useEffect(() => {
    if (certificatesData.length) {
      if (defaultTemplates.length === 0) {
        setActiveCertificateTab('custom_certificates');
      }

      const landScapeCertificates = certificatesData.some((certificate) => certificate.orientation === 'landscape');
      if (!landScapeCertificates && activeOrientation === 'landscape') {
        setActiveOrientation('portrait');
      }
    }

    if (currentCertificateKey === 'none') {
      setSelectedCertificate(currentCertificateKey);
      return;
    }

    const newCertificate = certificatesData.find((certificate) => certificate.key === currentCertificateKey);
    if (newCertificate) {
      if (activeOrientation !== newCertificate.orientation) {
        setActiveOrientation(newCertificate.orientation);
      }
      setActiveCertificateTab(newCertificate.is_default ? 'templates' : 'custom_certificates');
      setSelectedCertificate(newCertificate.key);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentCertificateKey, certificatesData]);

  const filteredCertificatesData = certificatesData.filter(
    (certificate) =>
      certificate.orientation === activeOrientation &&
      (activeCertificateTab === 'templates' ? certificate?.is_default : !certificate?.is_default),
  );

  const handleTabChange = (tab: CertificateTabValue) => {
    setActiveCertificateTab(tab);

    const landScapeCertificates = certificatesData.some(
      (certificate) =>
        certificate.orientation === 'landscape' &&
        (tab === 'templates' ? certificate.is_default : !certificate.is_default),
    );

    const portraitCertificates = certificatesData.some(
      (certificate) =>
        certificate.orientation === 'portrait' &&
        (tab === 'templates' ? certificate.is_default : !certificate.is_default),
    );

    setActiveOrientation((previousOrientation) => {
      if ((landScapeCertificates && portraitCertificates) || (!landScapeCertificates && !portraitCertificates)) {
        return previousOrientation;
      }
      return landScapeCertificates ? 'landscape' : 'portrait';
    });
  };

  const handleOrientationChange = (orientation: 'landscape' | 'portrait') => {
    setActiveOrientation(orientation);
  };

  const handleCertificateSelection = (certificateKey: string) => {
    onSelect(certificateKey);
    setSelectedCertificate(certificateKey);
  };

  const handlePreviewCertificate = (certificate: CertificateType) => {
    showModal({
      component: CertificatePreviewModal,
      props: {
        certificates: certificatesData,
        selectedCertificate: currentCertificateKey,
        currentCertificate: certificate,
        onSelectCertificate: (certificate: CertificateType) => {
          handleCertificateSelection(certificate.key);
        },
      },
    });
  };

  const certificateTabs: CertificateTabItem[] = [
    ...(defaultTemplates.length
      ? ([{ label: __('Templates', __TUTOR_TEXT_DOMAIN__), value: 'templates' }] as CertificateTabItem[])
      : []),
    {
      label: CURRENT_VIEWPORT.isAboveSmallMobile
        ? __('Custom Certificates', __TUTOR_TEXT_DOMAIN__)
        : __('Certificates', __TUTOR_TEXT_DOMAIN__),
      value: 'custom_certificates',
    },
  ];

  return (
    <Show when={isTutorPro && isCertificateAddonEnabled} fallback={<CertificateEmptyState />}>
      <Show when={isCertificateAddonEnabled}>
        <div css={styles.tabs}>
          <Tabs
            wrapperCss={styles.tabsWrapper}
            tabList={certificateTabs}
            activeTab={activeCertificateTab}
            onChange={handleTabChange}
          />
          <div css={styles.orientation}>
            <Show when={landScapeCertificates && portraitCertificates}>
              <Tooltip delay={200} content={__('Landscape', __TUTOR_TEXT_DOMAIN__)}>
                <button
                  type="button"
                  css={[
                    styleUtils.resetButton,
                    styles.orientationButton({
                      isActive: activeOrientation === 'landscape',
                    }),
                  ]}
                  onClick={() => handleOrientationChange('landscape')}
                >
                  <SVGIcon
                    name={activeOrientation === 'landscape' ? 'landscapeFilled' : 'landscape'}
                    width={32}
                    height={32}
                  />
                </button>
              </Tooltip>
              <Tooltip delay={200} content={__('Portrait', __TUTOR_TEXT_DOMAIN__)}>
                <button
                  type="button"
                  css={[
                    styleUtils.resetButton,
                    styles.orientationButton({
                      isActive: activeOrientation === 'portrait',
                    }),
                  ]}
                  onClick={() => handleOrientationChange('portrait')}
                >
                  <SVGIcon
                    name={activeOrientation === 'portrait' ? 'portraitFilled' : 'portrait'}
                    width={32}
                    height={32}
                  />
                </button>
              </Tooltip>
            </Show>
          </div>
        </div>

        <div
          css={styles.certificateWrapper({
            hasCertificates: filteredCertificatesData.length > 0,
            isSidebarVisible,
          })}
        >
          <Show
            when={certificatesData.length && (defaultTemplates.length === 0 || activeCertificateTab === 'templates')}
          >
            <CertificateCard
              selectedCertificate={selectedCertificate}
              onSelectCertificate={handleCertificateSelection}
              onPreviewCertificate={(data) => handlePreviewCertificate(data)}
              data={{
                key: 'none',
                name: __('None', __TUTOR_TEXT_DOMAIN__),
                preview_src: '',
                background_src: '',
                orientation: 'landscape',
                url: '',
              }}
              orientation={activeOrientation}
            />
          </Show>
          <Show
            when={filteredCertificatesData.length > 0}
            fallback={
              <div css={styles.emptyState}>
                <img
                  css={styles.placeholderImage({
                    notFound: true,
                  })}
                  src={notFound}
                  srcSet={`${notFound} 1x, ${notFound2x} 2x`}
                  alt={__('Not Found', __TUTOR_TEXT_DOMAIN__)}
                />

                <div css={styles.featureAndActionWrapper}>
                  <p
                    css={css`
                      ${typography.body('medium')}
                      color: ${colorTokens.text.subdued};
                    `}
                  >
                    {__('You didn’t create any certificate yet!', __TUTOR_TEXT_DOMAIN__)}
                  </p>
                </div>
              </div>
            }
          >
            <For each={filteredCertificatesData}>
              {(certificate) => (
                <CertificateCard
                  key={certificate.key}
                  selectedCertificate={selectedCertificate}
                  onSelectCertificate={handleCertificateSelection}
                  data={certificate}
                  orientation={activeOrientation}
                  onPreviewCertificate={handlePreviewCertificate}
                />
              )}
            </For>
          </Show>
        </div>
      </Show>
    </Show>
  );
};

export default Certificate;

const styles = {
  tabs: css`
    position: relative;
  `,
  tabsWrapper: css`
    button {
      min-width: auto;
    }
  `,
  certificateWrapper: ({
    hasCertificates,
    isSidebarVisible,
  }: {
    hasCertificates: boolean;
    isSidebarVisible: boolean;
  }) => css`
    display: grid;
    grid-template-columns: repeat(${isSidebarVisible ? 3 : 4}, 1fr);
    gap: ${spacing[16]};
    padding-top: ${spacing[12]};

    ${!hasCertificates &&
    css`
      grid-template-columns: 1fr;
      place-items: center;
    `}

    ${Breakpoint.smallMobile} {
      grid-template-columns: 1fr 1fr;
    }
  `,
  orientation: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[8]};
    position: absolute;
    height: 32px;
    right: 0;
    bottom: ${spacing[4]};
  `,
  orientationButton: ({ isActive }: { isActive: boolean }) => css`
    display: inline-flex;
    color: ${isActive ? colorTokens.icon.brand : colorTokens.icon.default};
    border-radius: ${borderRadius[4]};

    &:focus,
    &:active,
    &:hover {
      background: none;
      color: ${isActive ? colorTokens.icon.brand : colorTokens.icon.default};
    }

    &:focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
    }
  `,
  emptyState: css`
    padding-block: ${spacing[16]} ${spacing[12]};
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
  actionsButton: css`
    ${styleUtils.flexCenter()}
    margin-top: ${spacing[4]};
  `,
};
