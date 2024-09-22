import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';
import Tabs from '@Molecules/Tabs';
import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import Button from '@Atoms/Button';

import CertificateCard from '@CourseBuilderComponents/additional/CertificateCard';
import type { CourseDetailsResponse, CourseFormData } from '@CourseBuilderServices/course';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';

import config, { tutorConfig } from '@Config/config';
import { Addons } from '@Config/constants';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

import addonDisabled2x from '@Images/addon-disabled-2x.webp';
import addonDisabled from '@Images/addon-disabled.webp';
import notFound2x from '@Images/not-found-2x.webp';
import notFound from '@Images/not-found.webp';
import CertificateEmptyState from './CertificateEmptyState';

type CertificateTabValue = 'templates' | 'custom_certificates';

const certificateTabs: { label: string; value: CertificateTabValue }[] = [
  { label: __('Templates', 'tutor'), value: 'templates' },
  { label: __('Custom Certificates', 'tutor'), value: 'custom_certificates' },
];

const courseId = getCourseId();
const isTutorPro = !!tutorConfig.tutor_pro_url;
const isCertificateAddonEnabled = isAddonEnabled(Addons.TUTOR_CERTIFICATE);

const Certificate = () => {
  const queryClient = useQueryClient();

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;
  const certificatesData = courseDetails?.course_certificates_templates ?? [];

  const form = useFormContext<CourseFormData>();
  const currentCertificateKey = form.watch('tutor_course_certificate_template');

  const [activeCertificateTab, setActiveCertificateTab] = useState<CertificateTabValue>('templates');
  const [activeOrientation, setActiveOrientation] = useState<'landscape' | 'portrait'>('landscape');
  const [selectedCertificate, setSelectedCertificate] = useState(currentCertificateKey);

  const hasLandScapeCertificatesForActiveTab = certificatesData.some(
    (certificate) =>
      certificate.orientation === 'landscape' &&
      (activeCertificateTab === 'templates' ? certificate.is_default : !certificate.is_default),
  );

  const hasPortraitCertificatesForActiveTab = certificatesData.some(
    (certificate) =>
      certificate.orientation === 'portrait' &&
      (activeCertificateTab === 'templates' ? certificate.is_default : !certificate.is_default),
  );

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (!currentCertificateKey) {
      return;
    }

    const newCertificate = certificatesData.find((certificate) => certificate.key === currentCertificateKey);
    if (newCertificate) {
      if (activeOrientation !== newCertificate.orientation) {
        setActiveOrientation(newCertificate.orientation);
      }
      setActiveCertificateTab(newCertificate.is_default ? 'templates' : 'custom_certificates');
      setSelectedCertificate(currentCertificateKey);
    }
  }, [currentCertificateKey, certificatesData]);

  const filteredCertificatesData = certificatesData.filter(
    (certificate) =>
      certificate.orientation === activeOrientation &&
      (activeCertificateTab === 'templates' ? certificate?.is_default : !certificate?.is_default),
  );

  const handleTabChange = (tab: CertificateTabValue) => {
    setActiveCertificateTab(tab);

    const hasLandScapeCertificatesForSelectedTab = certificatesData.some(
      (certificate) =>
        certificate.orientation === 'landscape' &&
        (tab === 'templates' ? certificate.is_default : !certificate.is_default),
    );

    const hasPortraitCertificatesForSelectedTab = certificatesData.some(
      (certificate) =>
        certificate.orientation === 'portrait' &&
        (tab === 'templates' ? certificate.is_default : !certificate.is_default),
    );

    setActiveOrientation((previousOrientation) => {
      if (hasLandScapeCertificatesForSelectedTab && hasPortraitCertificatesForSelectedTab) {
        return previousOrientation;
      }
      return hasLandScapeCertificatesForSelectedTab ? 'landscape' : 'portrait';
    });
  };

  const handleOrientationChange = (orientation: 'landscape' | 'portrait') => {
    setActiveOrientation(orientation);
  };

  const handleCertificateSelection = (certificateKey: string) => {
    form.setValue('tutor_course_certificate_template', certificateKey);
    setSelectedCertificate(certificateKey);
  };

  return (
    <Show
      when={isTutorPro && isCertificateAddonEnabled}
      fallback={
        <Show
          when={!isTutorPro}
          fallback={
            <div css={styles.emptyState}>
              <img
                css={styles.placeholderImage}
                src={addonDisabled}
                srcSet={`${addonDisabled} 1x, ${addonDisabled2x} 2x`}
                alt={__('Addon Disabled', 'tutor')}
              />

              <div css={styles.featureAndActionWrapper}>
                <h6 css={typography.heading6('medium')}>
                  {__('You can use this feature by enabling Certificate Addon', 'tutor')}
                </h6>
              </div>

              <div css={styles.actionsButton}>
                <Button
                  icon={<SVGIcon name="linkExternal" width={24} height={24} />}
                  onClick={() => {
                    window.open(config.TUTOR_ADDONS_PAGE, '_blank', 'noopener');
                  }}
                >
                  {__('Enable Certificate Addons', 'tutor')}
                </Button>
              </div>
            </div>
          }
        >
          <CertificateEmptyState />
        </Show>
      }
    >
      <Show when={isCertificateAddonEnabled}>
        <div css={styles.tabs}>
          <Tabs tabList={certificateTabs} activeTab={activeCertificateTab} onChange={handleTabChange} />
          <div css={styles.orientation}>
            <Show when={hasLandScapeCertificatesForActiveTab && hasPortraitCertificatesForActiveTab}>
              <Tooltip delay={200} content={__('Landscape', 'tutor')}>
                <button
                  type="button"
                  css={[
                    styleUtils.resetButton,
                    styles.activeOrientation({
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
              <Tooltip delay={200} content={__('Portrait', 'tutor')}>
                <button
                  type="button"
                  css={[
                    styleUtils.resetButton,
                    styles.activeOrientation({
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
            activeCertificateTab,
          })}
        >
          <Show when={activeCertificateTab === 'templates'}>
            <CertificateCard
              selectedCertificate={selectedCertificate}
              onSelectCertificate={handleCertificateSelection}
              data={{
                key: '',
                name: __('None', 'tutor'),
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
              <Show when={activeCertificateTab === 'custom_certificates'}>
                <div css={styles.emptyState}>
                  <img
                    css={styles.placeholderImage({
                      notFound: true,
                    })}
                    src={notFound}
                    srcSet={`${notFound} 1x, ${notFound2x} 2x`}
                    alt={__('Not Found', 'tutor')}
                  />

                  <div css={styles.featureAndActionWrapper}>
                    <h6
                      css={css`
                        ${typography.heading6('medium')}
                        color: ${colorTokens.text.subdued};
                      `}
                    >
                      {__('You didn’t create any certificate yet!', 'tutor')}
                    </h6>
                  </div>
                </div>
              </Show>
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
  certificateWrapper: ({
    hasCertificates,
    activeCertificateTab,
  }: {
    hasCertificates: boolean;
    activeCertificateTab: CertificateTabValue;
  }) => css`
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: ${spacing[16]};
    padding-top: ${spacing[12]};

    ${
      !hasCertificates &&
      activeCertificateTab !== 'templates' &&
      css`
        grid-template-columns: 1fr;
        place-items: center;
      `
    }
  `,
  orientation: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[8]};
    position: absolute;
    right: 0;
    top: 0;
  `,
  activeOrientation: ({
    isActive,
  }: {
    isActive: boolean;
  }) => css`
    color: ${isActive ? colorTokens.icon.brand : colorTokens.icon.default};
  `,
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
  actionsButton: css`
    ${styleUtils.flexCenter()}
    margin-top: ${spacing[4]};
  `,
};
