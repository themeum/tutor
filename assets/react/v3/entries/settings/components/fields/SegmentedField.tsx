import { css } from '@emotion/react';
import { type SettingsField } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Tabs, { type TabItem } from '@TutorShared/molecules/Tabs';
import React, { useState } from 'react';

// Import field components directly to avoid circular dependency

interface SegmentedFieldProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
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
    ${typography.heading5('medium')};
    color: ${colorTokens.text.title};
    margin: 0;
  `,

  description: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;

    p {
      margin: 0;
    }

    a {
      color: ${colorTokens.text.brand};
      text-decoration: none;

      &:hover {
        text-decoration: underline;
      }
    }
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
    gap: ${spacing[20]};
  `,
};

const SegmentedField: React.FC<SegmentedFieldProps> = ({ field, value, onChange }) => {
  const [activeTab, setActiveTab] = useState<string>(field.segments?.[0]?.slug || '');

  // Handle nested field value changes
  // eslint-disable-next-line @typescript-eslint/no-explicit-any, @typescript-eslint/no-unused-vars
  const handleNestedFieldChange = (fieldKey: string, fieldValue: any) => {
    const currentValues = typeof value === 'object' && value !== null ? value : {};
    const newValues = {
      ...currentValues,
      [fieldKey]: fieldValue,
    };
    onChange(newValues);
  };

  // Get value for a specific nested field
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const getNestedFieldValue = (fieldKey: string) => {
    if (typeof value === 'object' && value !== null) {
      return value[fieldKey];
    }
    return undefined;
  };

  if (!field.segments || !Array.isArray(field.segments)) {
    return null;
  }

  // Create tab items from segments
  const tabItems: TabItem<string>[] = field.segments.map((segment) => ({
    label: segment.label,
    value: segment.slug,
  }));

  // Find active segment
  const activeSegment = field.segments.find((segment) => segment.slug === activeTab);

  return (
    <div css={styles.container}>
      <div css={styles.header}>
        <h3 css={styles.title}>{field.label}</h3>
        {field.desc && (
          <div css={styles.description}>
            <div dangerouslySetInnerHTML={{ __html: field.desc }} />
          </div>
        )}
      </div>

      <div css={styles.tabsContainer}>
        <Tabs activeTab={activeTab} onChange={setActiveTab} tabList={tabItems} orientation="horizontal" />

        <div css={styles.tabContent}>
          {activeSegment && (
            <div css={styles.fieldsContainer}>
              {activeSegment.fields.map((nestedField) => (
                <div key={nestedField.key} css={fieldStyles.fieldRow}>
                  <div css={fieldStyles.labelColumn}>
                    <div css={fieldStyles.labelContainer}>
                      <label css={fieldStyles.label}>{nestedField.label}</label>
                      {nestedField.label_title && <div css={fieldStyles.labelTitle}>{nestedField.label_title}</div>}
                    </div>
                  </div>

                  <div css={fieldStyles.inputColumn}>
                    <div css={fieldStyles.inputContainer}>
                      {renderNestedField(nestedField)}

                      {nestedField.desc && (
                        <div css={fieldStyles.description}>
                          <div dangerouslySetInnerHTML={{ __html: nestedField.desc }} />
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default SegmentedField;
