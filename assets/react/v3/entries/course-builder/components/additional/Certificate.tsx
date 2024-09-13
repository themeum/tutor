import { css } from '@emotion/react';
import { useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';

import EmptyState from '@Molecules/EmptyState';
import Tabs from '@Molecules/Tabs';

import CertificateCard from '@CourseBuilderComponents/additional/CertificateCard';
import type { CourseDetailsResponse, CourseFormData } from '@CourseBuilderServices/course';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';

import config, { tutorConfig } from '@Config/config';
import { Addons } from '@Config/constants';
import { colorTokens, spacing } from '@Config/styles';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

import emptyStateImage2x from '@Images/empty-state-illustration-2x.webp';
import emptyStateImage from '@Images/empty-state-illustration.webp';

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

    const hasLandScapeCertificatesForActiveTab = certificatesData.some(
      (certificate) =>
        certificate.orientation === 'landscape' &&
        (tab === 'templates' ? certificate.is_default : !certificate.is_default),
    );

    const hasPortraitCertificatesForActiveTab = certificatesData.some(
      (certificate) =>
        certificate.orientation === 'portrait' &&
        (tab === 'templates' ? certificate.is_default : !certificate.is_default),
    );

    setActiveOrientation((previousOrientation) => {
      if (hasLandScapeCertificatesForActiveTab && hasPortraitCertificatesForActiveTab) {
        return previousOrientation;
      }

      if (hasLandScapeCertificatesForActiveTab) {
        return 'landscape';
      }

      if (hasPortraitCertificatesForActiveTab) {
        return 'portrait';
      }

      return 'landscape';
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
        <EmptyState
          size="small"
          title={__('Your students deserve certificates!', 'tutor')}
          description={
            !isTutorPro
              ? __('Unlock this feature by upgrading to Tutor LMS Pro.', 'tutor')
              : __('Enable the Certificate Addon to start creating certificates for your students.', 'tutor')
          }
          emptyStateImage={emptyStateImage}
          emptyStateImage2x={emptyStateImage2x}
          imageAltText={__('Illustration of a certificate', 'tutor')}
          actions={
            <Show
              when={!isTutorPro}
              fallback={
                <Button
                  variant="primary"
                  size="small"
                  onClick={() => {
                    window.open(config.TUTOR_ADDONS_PAGE, '_blank', 'noopener');
                  }}
                  icon={<SVGIcon name="linkExternal" width={24} height={24} />}
                >
                  {__('Enable Certificate Addon', 'tutor')}
                </Button>
              }
            >
              <Button
                variant="primary"
                size="small"
                onClick={() => {
                  window.open(config.TUTOR_PRICING_PAGE, '_blank', 'noopener');
                }}
                icon={<SVGIcon name="crown" width={24} height={24} />}
              >
                {__('Get Tutor LMS Pro', 'tutor')}
              </Button>
            </Show>
          }
        />
      }
    >
      <Show when={isCertificateAddonEnabled}>
        <div css={styles.tabs}>
          <Tabs tabList={certificateTabs} activeTab={activeCertificateTab} onChange={handleTabChange} />
          <div css={styles.orientation}>
            <Show when={hasLandScapeCertificatesForActiveTab}>
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
            </Show>
            <Show when={hasPortraitCertificatesForActiveTab}>
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
                <EmptyState
                  size="small"
                  title={__('No templates found', 'tutor')}
                  description={__('No custom certificates found. Create a new one.', 'tutor')}
                  emptyStateImage={emptyStateImage}
                  emptyStateImage2x={emptyStateImage2x}
                  imageAltText={__('Illustration of a certificate', 'tutor')}
                />
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
};
