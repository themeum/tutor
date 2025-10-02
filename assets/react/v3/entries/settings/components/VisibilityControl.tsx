import { css } from '@emotion/react';
import { type VisibilityControlSection, useSettings } from '@Settings/contexts/SettingsContext';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import React from 'react';

interface VisibilityControlProps {
  sections: VisibilityControlSection[];
  blockLabel?: string | false;
}

const styles = {
  container: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,

  header: css``,

  title: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,

  wrapper: css`
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.border};
    border-radius: ${borderRadius[6]};
    padding: ${spacing[24]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,

  segmentContainer: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,

  segmentHeader: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: ${spacing[12]};
  `,

  segmentTitle: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
  `,

  rolesHeader: css`
    display: flex;
    justify-content: center;
  `,

  roleColumn: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    text-align: center;
    width: 120px;
  `,

  segmentContent: css`
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[6]};
    padding: 0;
    padding-left: ${spacing[16]};
    overflow: hidden;
  `,

  fieldsContainer: css`
    display: flex;
    flex-direction: column;
  `,

  fieldRow: css`
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 48px;
  `,

  fieldLabel: css`
    ${typography.caption('medium')};
    flex-grow: 1;
    color: ${colorTokens.text.title};
  `,

  roleToggle: css`
    display: flex;
    justify-content: center;
    align-items: center;
    width: 120px;
    height: 100%;
  `,

  checkbox: css`
    width: 16px;
    height: 16px;
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: 2px;
    cursor: pointer;
    position: relative;
    background-color: white;

    &.checked {
      background-color: ${colorTokens.primary.main};
      border-color: ${colorTokens.primary.main};
    }

    &.checked::after {
      content: '✓';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-size: 10px;
      font-weight: bold;
    }
  `,

  emptyState: css`
    text-align: center;
    padding: ${spacing[32]};
    color: ${colorTokens.text.subdued};
    ${typography.body()};
  `,
};

const VisibilityControl: React.FC<VisibilityControlProps> = ({ sections, blockLabel }) => {
  const { state, dispatch } = useSettings();

  if (!sections || !Array.isArray(sections) || sections.length === 0) {
    return <div css={styles.emptyState}>No visibility control sections available</div>;
  }

  // Handle checkbox change for a specific field and role
  const handleCheckboxChange = (fieldKey: string, role: string, checked: boolean) => {
    const currentValues = state.values[fieldKey] || {};
    const newValues = {
      ...currentValues,
      [role]: checked,
    };

    dispatch({
      type: 'UPDATE_VALUE',
      payload: { key: fieldKey, value: newValues },
    });
  };

  // Get checkbox state for a specific field and role
  const getCheckboxState = (fieldKey: string, role: string): boolean => {
    const fieldValues = state.values[fieldKey];
    if (typeof fieldValues === 'object' && fieldValues !== null) {
      return fieldValues[role] === true || fieldValues[role] === 'on';
    }
    return true; // Default to enabled
  };

  return (
    <div css={styles.container}>
      {blockLabel && (
        <div css={styles.header}>
          <div css={styles.title}>{blockLabel}</div>
        </div>
      )}

      <div css={styles.wrapper}>
        {sections.map((section) => (
          <div key={section.label} css={styles.segmentContainer}>
            <div css={styles.segmentHeader}>
              <div css={styles.segmentTitle}>{section.label}</div>
              {section.roles && (
                <div css={styles.rolesHeader}>
                  {Object.entries(section.roles).map(([roleKey, roleLabel]) => (
                    <div key={roleKey} css={styles.roleColumn}>
                      {roleLabel}
                    </div>
                  ))}
                </div>
              )}
            </div>

            <div css={styles.segmentContent}>
              <div css={styles.fieldsContainer}>
                {section.fields.map((field) => (
                  <div key={field.key} css={styles.fieldRow}>
                    <div css={styles.fieldLabel}>{field.label}</div>

                    {section.roles &&
                      Object.entries(section.roles).map(([roleKey]) => (
                        <div key={roleKey} css={styles.roleToggle}>
                          <div
                            css={styles.checkbox}
                            className={getCheckboxState(field.key, roleKey) ? 'checked' : ''}
                            onClick={() =>
                              handleCheckboxChange(field.key, roleKey, !getCheckboxState(field.key, roleKey))
                            }
                          />
                        </div>
                      ))}
                  </div>
                ))}
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default VisibilityControl;
