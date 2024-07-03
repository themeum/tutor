import SVGIcon from '@Atoms/SVGIcon';
import { colorTokens, spacing } from '@Config/styles';
import Show from '@Controls/Show';
import Tabs from '@Molecules/Tabs';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import CertificateCard from './CertificateCard';
import For from '@Controls/For';
import { type CourseFormData, useCourseDetailsQuery } from '@CourseBuilderServices/course';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { useFormContext } from 'react-hook-form';
import config, { tutorConfig } from '@Config/config';
import { Addons } from '@Config/constants';
import EmptyState from '@Molecules/EmptyState';
import Button from '@Atoms/Button';
import emptyStateImage from '@Images/empty-state-illustration.webp';
import emptyStateImage2x from '@Images/empty-state-illustration-2x.webp';

type CertificateTabValue = 'templates' | 'my_certificates';

const certificateTabs: { label: string; value: CertificateTabValue }[] = [
  { label: __('Templates', 'tutor'), value: 'templates' },
  { label: __('My Certificates', 'tutor'), value: 'my_certificates' },
];

const Certificate = () => {
  const courseId = getCourseId();
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const courseDetailsQuery = useCourseDetailsQuery(courseId);
  const certificatesData = courseDetailsQuery.data?.course_certificates_templates ?? [];

  const form = useFormContext<CourseFormData>();
  const currentCertificateKey = form.watch('tutor_course_certificate_template');
  const currentCertificate = currentCertificateKey
    ? certificatesData.find((certificate) => certificate.key === currentCertificateKey)
    : null;
  const [activeCertificateTab, setActiveCertificateTab] = useState<CertificateTabValue>('templates');
  const [activeOrientation, setActiveOrientation] = useState<'landscape' | 'portrait'>(
    currentCertificate?.orientation ?? 'landscape'
  );
  const [selectedCertificate, setSelectedCertificate] = useState(currentCertificateKey);

  const filteredCertificatesData = certificatesData.filter(
    (certificate) => certificate.orientation === activeOrientation
  );

  const handleTabChange = (tab: CertificateTabValue) => {
    setActiveCertificateTab(tab);
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    form.setValue('tutor_course_certificate_template', selectedCertificate);
  }, [selectedCertificate]);

  return (
    <Show
      when={isTutorPro}
      fallback={
        <EmptyState
          size="small"
          title={__('Your students deserve certificates!', 'tutor')}
          description={__('Unlock this feature by upgrading to Tutor LMS Pro.', 'tutor')}
          emptyStateImage={emptyStateImage}
          emptyStateImage2x={emptyStateImage2x}
          imageAltText={__('Illustration of a certificate', 'tutor')}
          actions={
            <Button
              variant="primary"
              size="small"
              onClick={() => {
                window.open(config.TUTOR_PRICING_PAGE, '_blank');
              }}
              icon={<SVGIcon name="crown" width={24} height={24} />}
            >
              {__('Get Tutor LMS Pro', 'tutor')}
            </Button>
          }
        />
      }
    >
      <Show when={isAddonEnabled(Addons.TUTOR_CERTIFICATE)}>
        <div css={styles.tabs}>
          <Tabs tabList={certificateTabs} activeTab={activeCertificateTab} onChange={handleTabChange} />
          <div css={styles.orientation}>
            <button
              type="button"
              css={[
                styleUtils.resetButton,
                styles.activeOrientation({
                  isActive: activeOrientation === 'landscape',
                }),
              ]}
              onClick={() => setActiveOrientation('landscape')}
            >
              <SVGIcon name="landscape" width={32} height={32} />
            </button>
            <button
              type="button"
              css={[
                styleUtils.resetButton,
                styles.activeOrientation({
                  isActive: activeOrientation === 'portrait',
                }),
              ]}
              onClick={() => setActiveOrientation('portrait')}
            >
              <SVGIcon name="portrait" width={32} height={32} />
            </button>
          </div>
        </div>

        <Show when={activeCertificateTab === 'templates'}>
          <div css={styles.certificateWrapper}>
            <CertificateCard
              isSelected={selectedCertificate === ''}
              setSelectedCertificate={setSelectedCertificate}
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
            <For each={filteredCertificatesData}>
              {(certificate) => (
                <CertificateCard
                  key={certificate.key}
                  isSelected={selectedCertificate === certificate.key}
                  setSelectedCertificate={setSelectedCertificate}
                  data={certificate}
                  orientation={activeOrientation}
                />
              )}
            </For>
          </div>
        </Show>
        <Show when={activeCertificateTab === 'my_certificates'}>
          <div css={styles.certificateWrapper}>
            <For each={filteredCertificatesData}>
              {(certificate) => (
                <CertificateCard
                  key={certificate.key}
                  isSelected={selectedCertificate === certificate.key}
                  setSelectedCertificate={setSelectedCertificate}
                  data={certificate}
                  orientation={activeOrientation}
                />
              )}
            </For>
          </div>
        </Show>
      </Show>
    </Show>
  );
};

export default Certificate;

const styles = {
  tabs: css`
    position: relative;
  `,
  certificateWrapper: css`
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: ${spacing[16]};
    padding-top: ${spacing[12]};
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
