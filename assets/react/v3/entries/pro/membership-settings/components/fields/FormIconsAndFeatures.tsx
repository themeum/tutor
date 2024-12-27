import Button from '@/v3/shared/atoms/Button';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import FormFieldWrapper from '@/v3/shared/components/fields/FormFieldWrapper';
import { borderRadius, colorTokens, fontSize, lineHeight, shadow, spacing, zIndex } from '@/v3/shared/config/styles';
import { typography } from '@/v3/shared/config/typography';
import For from '@/v3/shared/controls/For';
import Show from '@/v3/shared/controls/Show';
import { type FormControllerProps } from '@/v3/shared/utils/form';
import { css } from '@emotion/react';
import { useState } from 'react';
import { featureIcons } from '../../config/constants';
import { Portal, usePortalPopover } from '@/v3/shared/hooks/usePortalPopover';
import { isRTL } from '@/v3/shared/config/constants';
import { __ } from '@wordpress/i18n';

interface Feature {
  icon: string;
  content: string;
}

interface FeatureItemProps {
  data: Feature;
  handleContentChange: (value: string) => void;
  handleIconChange: (value: keyof typeof featureIcons) => void;
  handleDeleteClick: () => void;
}

const FeatureItem = ({ data, handleContentChange, handleIconChange, handleDeleteClick }: FeatureItemProps) => {
  const [isOpen, setIsOpen] = useState(false);
  const { triggerRef, position, popoverRef } = usePortalPopover<HTMLButtonElement, HTMLDivElement>({
    isOpen,
  });

  return (
    <>
      <div css={styles.featureItem}>
        <button
          ref={triggerRef}
          type="button"
          css={styles.iconSelector}
          onClick={() => setIsOpen(!isOpen)}
          dangerouslySetInnerHTML={{ __html: data.icon }}
        />
        <input value={data.content} onChange={(e) => handleContentChange(e.target.value)} />
        <button css={styles.deleteButton} type="button" onClick={handleDeleteClick} data-delete-button>
          <SVGIcon name="delete" width={24} height={24} />
        </button>
      </div>
      <Portal
        isOpen={isOpen}
        onClickOutside={() => {
          setIsOpen(false);
        }}
        onEscape={() => {
          setIsOpen(false);
        }}
      >
        <div
          ref={popoverRef}
          css={[
            styles.popoverWrapper,
            {
              [isRTL ? 'right' : 'left']: position.left,
              top: position.top,
              maxWidth: 208,
            },
          ]}
        >
          <div css={styles.popoverHeader}>
            <label>{__('Icons', 'tutor')}</label>
            <Button variant="text" onClick={() => setIsOpen(false)}>
              <SVGIcon name="cross" width={24} height={24} />
            </Button>
          </div>
          <div css={styles.popoverContent}>
            <For each={Object.getOwnPropertyNames(featureIcons) as (keyof typeof featureIcons)[]}>
              {(icon: keyof typeof featureIcons) => {
                return (
                  <button
                    css={styles.popoverContentButton}
                    type="button"
                    onClick={() => {
                      handleIconChange(icon);
                      setIsOpen(false);
                    }}
                    dangerouslySetInnerHTML={{ __html: featureIcons[icon] }}
                  />
                );
              }}
            </For>
          </div>
        </div>
      </Portal>
    </>
  );
};

interface FormIconsAndFeaturesProps extends FormControllerProps<Feature[]> {
  label: string;
}

function FormIconsAndFeatures({ label, field, fieldState }: FormIconsAndFeaturesProps) {
  const fieldValue = field.value || [];

  return (
    <FormFieldWrapper field={field} fieldState={fieldState}>
      {() => {
        return (
          <div css={styles.wrapper}>
            <div css={styles.header}>
              <label>{label}</label>
              <Button
                variant="text"
                onClick={() => field.onChange([...fieldValue, { icon: featureIcons.tickCircleFill, content: '' }])}
              >
                <SVGIcon name="plus" width={24} height={24} />
              </Button>
            </div>
            <Show when={fieldValue.length > 0}>
              <div css={styles.features}>
                <For each={fieldValue}>
                  {(feature, index) => {
                    return (
                      <FeatureItem
                        data={feature}
                        handleIconChange={(value: keyof typeof featureIcons) => {
                          field.onChange(
                            [...fieldValue].map((item, idx) => {
                              return idx === index ? { ...item, icon: featureIcons[value] } : item;
                            }),
                          );
                        }}
                        handleContentChange={(value) => {
                          field.onChange(
                            [...fieldValue].map((item, idx) => {
                              return idx === index ? { ...item, content: value } : item;
                            }),
                          );
                        }}
                        handleDeleteClick={() => {
                          field.onChange(fieldValue.filter((_, idx) => idx !== index));
                        }}
                      />
                    );
                  }}
                </For>
              </div>
            </Show>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
}

export default FormIconsAndFeatures;

const styles = {
  wrapper: css`
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[6]};
    padding: ${spacing[12]} ${spacing[16]};
  `,
  header: css`
    display: flex;
    align-items: center;
    justify-content: space-between;

    label {
      ${typography.caption()};
      color: ${colorTokens.text.title};
    }

    button {
      color: ${colorTokens.icon.default};
      border: 1px solid ${colorTokens.stroke.default};
      border-radius: ${borderRadius[4]};
      padding: 3px;
    }
  `,
  features: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
    padding: ${spacing[12]} 0 ${spacing[8]};
  `,
  featureItem: css`
    position: relative;
    display: flex;

    &:hover {
      button[data-delete-button] {
        opacity: 1;
      }
    }

    input {
      width: 100%;
      font-size: ${fontSize[16]};
      line-height: ${lineHeight[24]};
      border: 1px solid ${colorTokens.stroke.default};
      border-top-right-radius: ${borderRadius[6]};
      border-bottom-right-radius: ${borderRadius[6]};
      border-left: none;
      padding: ${spacing[4]} ${spacing[36]} ${spacing[4]} ${spacing[8]};

      &:focus {
        border-radius: ${borderRadius[6]};
        outline: 2px solid ${colorTokens.stroke.brand};
        outline-offset: 2px;
      }
    }
  `,
  iconSelector: css`
    height: 40px;
    display: flex;
    align-items: center;
    background-color: transparent;
    color: ${colorTokens.icon.hover};
    border: 1px solid ${colorTokens.stroke.default};
    border-top-left-radius: ${borderRadius[6]};
    border-bottom-left-radius: ${borderRadius[6]};
    cursor: pointer;
    transition: background-color 0.25s;

    :hover {
      background-color: ${colorTokens.background.hover};
    }

    :focus-visible {
      border-radius: ${borderRadius[6]};
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 2px;
      z-index: 1;
    }
  `,
  deleteButton: css`
    display: flex;
    position: absolute;
    right: ${spacing[12]};
    top: ${spacing[8]};
    padding: 0;
    color: ${colorTokens.icon.default};
    background: transparent;
    border: none;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.25s;

    :focus-visible {
      border-radius: ${borderRadius[2]};
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 2px;
      opacity: 1;
    }
  `,
  popoverWrapper: css`
    position: absolute;
    width: 100%;
    z-index: ${zIndex.dropdown};
    background-color: ${colorTokens.background.white};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};
    max-height: 300px;
    overflow-y: auto;
  `,
  popoverHeader: css`
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    padding: ${spacing[8]};

    label {
      ${typography.caption('medium')};
      color: ${colorTokens.text.title};
    }

    button {
      padding: 0px;
    }
  `,
  popoverContent: css`
    display: flex;
    flex-wrap: wrap;
    gap: ${spacing[8]};
    padding: ${spacing[12]};
  `,
  popoverContentButton: css`
    display: flex;
    background-color: ${colorTokens.background.default};
    color: ${colorTokens.icon.hover};
    border: none;
    border-radius: ${borderRadius[4]};
    padding: ${spacing[8]};
    cursor: pointer;
    transition:
      background-color 0.25s,
      box-shadow 0.25s;

    :hover {
      background-color: ${colorTokens.background.hover};
      box-shadow: inset 0px 0px 0px 1px ${colorTokens.action.primary.hover};
    }

    :focus-visible {
      border-radius: ${borderRadius[6]};
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 2px;
      z-index: 1;
    }
  `,
};
