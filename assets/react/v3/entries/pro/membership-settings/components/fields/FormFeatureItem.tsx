import Button from '@/v3/shared/atoms/Button';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import FormFieldWrapper from '@/v3/shared/components/fields/FormFieldWrapper';
import { animateLayoutChanges } from '@/v3/shared/utils/dndkit';
import { borderRadius, Breakpoint, colorTokens, shadow, spacing, zIndex } from '@/v3/shared/config/styles';
import { typography } from '@/v3/shared/config/typography';
import For from '@/v3/shared/controls/For';
import { type FormControllerProps } from '@/v3/shared/utils/form';
import { css } from '@emotion/react';
import { CSS } from '@dnd-kit/utilities';
import { useState } from 'react';
import { featureIcons } from '../../config/constants';
import { Portal, usePortalPopover } from '@/v3/shared/hooks/usePortalPopover';
import { isRTL } from '@/v3/shared/config/constants';
import { __ } from '@wordpress/i18n';
import { useSortable } from '@dnd-kit/sortable';

interface Feature {
  id: string;
  icon: string;
  content: string;
}

interface FormFeatureItemProps extends FormControllerProps<Feature> {
  id: string;
  handleDeleteClick: () => void;
}

export default function FormFeatureItem({ id, field, fieldState, handleDeleteClick }: FormFeatureItemProps) {
  const [isOpen, setIsOpen] = useState(false);
  const { triggerRef, position, popoverRef } = usePortalPopover<HTMLButtonElement, HTMLDivElement>({
    isOpen,
  });

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: id,
    animateLayoutChanges,
  });

  const style = {
    transform: CSS.Transform.toString(transform ? { ...transform, scaleX: 1, scaleY: 1 } : null),
    transition,
    zIndex: isDragging ? 1 : 0,
  };

  function handleIconChange(icon: keyof typeof featureIcons) {
    field.onChange({ ...field.value, icon: featureIcons[icon] });
  }

  function handleContentChange(content: string) {
    field.onChange({ ...field.value, content });
  }

  return (
    <div ref={setNodeRef} style={style}>
      <FormFieldWrapper field={field} fieldState={fieldState} inputStyle={styles.input}>
        {(inputProps) => {
          return (
            <>
              <div css={styles.featureItem}>
                <button type="button" {...attributes} {...listeners} css={styles.dragButton}>
                  <SVGIcon name="dragVertical" width={24} height={24} />
                </button>
                <button
                  ref={triggerRef}
                  type="button"
                  css={styles.iconSelector}
                  onClick={() => setIsOpen(!isOpen)}
                  dangerouslySetInnerHTML={{ __html: field.value.icon }}
                />
                <input
                  {...inputProps}
                  value={field.value.content}
                  onChange={(e) => handleContentChange(e.target.value)}
                />
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
        }}
      </FormFieldWrapper>
    </div>
  );
}

const styles = {
  featureItem: css`
    position: relative;
    display: flex;

    &:hover {
      button[data-delete-button] {
        opacity: 1;
      }
    }
  `,
  input: css`
    &.tutor-input-field {
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
      padding: ${spacing[4]} ${spacing[36]} ${spacing[4]} ${spacing[8]};

      &:focus {
        border-radius: ${borderRadius[6]};
      }
    }
  `,
  iconSelector: css`
    height: 40px;
    display: flex;
    align-items: center;
    background-color: ${colorTokens.background.white};
    color: ${colorTokens.icon.hover};
    border: 1px solid ${colorTokens.stroke.default};
    border-right: none;
    border-top-left-radius: ${borderRadius[6]};
    border-bottom-left-radius: ${borderRadius[6]};
    cursor: pointer;
    transition: background-color 0.25s;

    :hover {
      background-color: ${colorTokens.background.hover};
    }

    :focus-visible {
      border-radius: ${borderRadius[4]};
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 2px;
      z-index: 1;
    }
  `,
  dragButton: css`
    display: flex;
    align-items: center;
    padding: 0;
    color: ${colorTokens.icon.default};
    background: transparent;
    border: none;
    cursor: grab;

    :focus-visible {
      border-radius: ${borderRadius[4]};
      outline: 2px solid ${colorTokens.stroke.brand};
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

    ${Breakpoint.mobile} {
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
