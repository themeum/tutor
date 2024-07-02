import SVGIcon from '@Atoms/SVGIcon';
import { colorTokens, spacing } from '@Config/styles';
import Show from '@Controls/Show';
import Tabs from '@Molecules/Tabs';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import CertificateCard from './CertificateCard';
import For from '@Controls/For';

const mockCertificateData = [
  {
    id: '1',
    title: 'Certificate 1',
    image: 'https://via.placeholder.com/150',
  },
  {
    id: '2',
    title: 'Certificate 2',
    image: 'https://via.placeholder.com/150',
  },
  {
    id: '3',
    title: 'Certificate 3',
    image: 'https://via.placeholder.com/150',
  },
  {
    id: '4',
    title: 'Certificate 4',
    image: 'https://via.placeholder.com/150',
  },
];

type CertificateTabValue = 'templates' | 'my_certificates';

const certificateTabs: { label: string; value: CertificateTabValue }[] = [
  { label: __('Templates', 'tutor'), value: 'templates' },
  { label: __('My Certificates', 'tutor'), value: 'my_certificates' },
];

const Certificate = () => {
  const [activeCetificateTab, setActiveCertificateTab] = useState<CertificateTabValue>('templates');
  const [activeOrientation, setActiveOrientation] = useState<'landscape' | 'portrait'>('landscape');
  const [selectedCertificate, setSelectedCertificate] = useState('');

  const handleTabChange = (tab: CertificateTabValue) => {
    setActiveCertificateTab(tab);
  };

  return (
    <>
      <div css={styles.tabs}>
        <Tabs tabList={certificateTabs} activeTab={activeCetificateTab} onChange={handleTabChange} />
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

      <Show when={activeCetificateTab === 'templates'}>
        <div css={styles.certificateWrapper}>
          <CertificateCard
            isSelected={selectedCertificate === ''}
            setSelectedCertificate={setSelectedCertificate}
            data={{
              id: '',
              title: __('None', 'tutor'),
              image: '',
            }}
            orientation={activeOrientation}
          />
          <For each={mockCertificateData}>
            {(certificate) => (
              <CertificateCard
                key={certificate.id}
                isSelected={selectedCertificate === certificate.id}
                setSelectedCertificate={setSelectedCertificate}
                data={certificate}
                orientation={activeOrientation}
              />
            )}
          </For>
        </div>
      </Show>
      <Show when={activeCetificateTab === 'my_certificates'}>
        <div css={styles.certificateWrapper}>
          <For each={mockCertificateData}>
            {(certificate) => (
              <CertificateCard
                key={certificate.id}
                isSelected={selectedCertificate === certificate.id}
                setSelectedCertificate={setSelectedCertificate}
                data={certificate}
                orientation={activeOrientation}
              />
            )}
          </For>
        </div>
      </Show>
    </>
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
