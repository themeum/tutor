import { css } from '@emotion/react';
import SettingsField from '@Settings/components/SettingsField';
import { type SettingsSegment } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Tabs, { type TabItem } from '@TutorShared/molecules/Tabs';
import React, { useState } from 'react';

interface BlockSegmentsProps {
  segments: SettingsSegment[];
  blockLabel?: string | false;
}

const styles = {
  container: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,

  header: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,

  title: css`
    ${typography.heading6('medium')};
    color: ${colorTokens.text.title};
    margin: 0;
  `,

  tabsContainer: css`
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[8]};
    background-color: ${colorTokens.background.white};
    overflow: hidden;
  `,

  tabContent: css`
    padding: ${spacing[24]};
  `,

  fieldsContainer: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
};

const BlockSegments: React.FC<BlockSegmentsProps> = ({ segments, blockLabel }) => {
  const [activeTab, setActiveTab] = useState<string>(segments[0]?.slug || '');

  if (!segments || !Array.isArray(segments) || segments.length === 0) {
    return null;
  }

  // Create tab items from segments
  const tabItems: TabItem<string>[] = segments.map((segment) => ({
    label: segment.label,
    value: segment.slug,
  }));

  // Find active segment
  const activeSegment = segments.find((segment) => segment.slug === activeTab);

  return (
    <div css={styles.container}>
      {blockLabel && (
        <div css={styles.header}>
          <h3 css={styles.title}>{blockLabel}</h3>
        </div>
      )}

      <div css={styles.tabsContainer}>
        <Tabs activeTab={activeTab} onChange={setActiveTab} tabList={tabItems} orientation="horizontal" />

        <div css={styles.tabContent}>
          {activeSegment && (
            <div css={styles.fieldsContainer}>
              {activeSegment.fields.map((field, idx) => (
                <SettingsField key={field.key || idx} field={field} />
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default BlockSegments;
