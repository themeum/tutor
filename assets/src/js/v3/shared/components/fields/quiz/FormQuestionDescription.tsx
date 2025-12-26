import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import type { FormControllerProps } from '@TutorShared/utils/form';

import FormTextareaInput from '@TutorShared/components/fields/FormTextareaInput';
import FormWPEditor from '@TutorShared/components/fields/FormWPEditor';

interface FormQuestionDescriptionProps extends FormControllerProps<string | null> {
  label?: string;
  maxLimit?: number;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string | number) => void;
}

const isTutorPro = !!tutorConfig.tutor_pro_url;

const FormQuestionDescription = ({
  label,
  field,
  fieldState,
  disabled = false,
  loading,
  placeholder,
  helpText,
  onChange,
}: FormQuestionDescriptionProps) => {
  const inputValue = field.value ?? '';

  const [isEdit, setIsEdit] = useState<boolean>(false);
  const [previousValue, setPreviousValue] = useState<string>(inputValue);

  const handleCancelClick = () => {
    field.onChange(previousValue);
    setIsEdit(false);
  };

  const handleOkClick = () => {
    setIsEdit(false);
    setPreviousValue(field.value || '');
  };

  const handleOverlayActivation = () => {
    if (!isEdit && !disabled) {
      setIsEdit(true);
    }
  };

  const handleOverlayKeyDown = (event: React.KeyboardEvent) => {
    if ((event.key === 'Enter' || event.key === ' ') && !isEdit) {
      event.preventDefault();
      setIsEdit(true);
    }
  };

  return (
    <div css={styles.editorWrapper({ isEdit })} tabIndex={-1}>
      <div css={styles.container({ isEdit, isDisabled: disabled })}>
        <Show
          when={!isEdit && (!isTutorPro || !inputValue)}
          fallback={
            <div
              css={styles.editorContainer}
              aria-hidden={!isEdit}
              // @ts-ignore
              inert={!isEdit ? '' : undefined}
            >
              <Show
                when={isTutorPro}
                fallback={
                  <FormTextareaInput
                    field={field}
                    fieldState={fieldState}
                    label={label}
                    disabled={disabled}
                    helpText={helpText}
                    loading={loading}
                    readOnly={!isEdit}
                    onChange={onChange}
                    placeholder={placeholder}
                    autoResize
                    maxHeight={400}
                  />
                }
              >
                <FormWPEditor
                  field={field}
                  fieldState={fieldState}
                  label={label}
                  disabled={disabled}
                  helpText={helpText}
                  loading={loading}
                  readOnly={!isEdit}
                  onChange={onChange}
                  placeholder={placeholder}
                  min_height={100}
                  max_height={400}
                  autoFocus={true}
                  toolbar1={`bold italic underline | bullist numlist | blockquote | alignleft aligncenter alignright | link unlink | ${
                    isTutorPro ? ' codesample' : ''
                  } | wp_adv`}
                  toolbar2={
                    'formatselect strikethrough hr wp_more forecolor pastetext removeformat charmap outdent indent undo redo wp_help fullscreen tutor_button undoRedoDropdown'
                  }
                />
              </Show>
            </div>
          }
        >
          <div
            css={styles.placeholder}
            dangerouslySetInnerHTML={{
              __html: inputValue || placeholder || '',
            }}
            tabIndex={-1}
            aria-hidden="true"
          />
        </Show>

        <Show when={isEdit}>
          <div data-action-buttons css={styles.actionButtonWrapper({ isEdit })}>
            <Button variant="text" size="small" onClick={handleCancelClick}>
              {__('Cancel', __TUTOR_TEXT_DOMAIN__)}
            </Button>
            <Button variant="secondary" size="small" onClick={handleOkClick} disabled={inputValue === previousValue}>
              {__('Ok', __TUTOR_TEXT_DOMAIN__)}
            </Button>
          </div>
        </Show>

        <Show when={!isEdit}>
          <div
            onClick={handleOverlayActivation}
            onKeyDown={handleOverlayKeyDown}
            data-overlay
            tabIndex={0}
            role="button"
            aria-label={
              inputValue ? __('Edit description', __TUTOR_TEXT_DOMAIN__) : __('Add description', __TUTOR_TEXT_DOMAIN__)
            }
          />
        </Show>
      </div>
    </div>
  );
};

export default FormQuestionDescription;

const styles = {
  editorWrapper: ({ isEdit }: { isEdit: boolean }) => css`
    position: relative;
    max-height: 400px;
    overflow-y: auto;
    border-radius: ${borderRadius[6]};

    ${isEdit &&
    css`
      padding-inline: 0;
      max-height: unset;
      overflow: unset;
    `}
  `,
  container: ({ isEdit, isDisabled }: { isEdit: boolean; isDisabled: boolean }) => css`
    position: relative;
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
    min-height: 64px;
    height: 100%;
    width: 100%;
    inset: 0;
    padding-inline: ${spacing[8]} ${spacing[16]};
    border-radius: ${borderRadius[6]};
    transition: background-color 0.15s ease-in-out;

    [data-overlay] {
      position: absolute;
      inset: 0;
      opacity: 1;
      cursor: pointer;

      &:focus-visible {
        outline: 2px solid ${colorTokens.stroke.brand};
        outline-offset: -2px;
        border-radius: ${borderRadius.card};
      }
    }

    & label {
      ${typography.caption()};
      margin-bottom: ${spacing[6]};
      color: ${colorTokens.text.title};
    }

    ${!isDisabled &&
    css`
      &:hover,
      &:has([data-overlay]:focus-visible) {
        background-color: ${!isEdit && colorTokens.background.white};
        color: ${colorTokens.text.subdued};

        [data-action-buttons] {
          opacity: 1;
        }
      }
    `}

    ${isEdit &&
    css`
      padding-inline: 0;
    `}
  `,
  editorContainer: css`
    position: relative;
    width: 100%;
  `,
  placeholder: css`
    ${typography.caption()}
    color: ${colorTokens.text.hints};
    height: 100%;
    inset: 0;
    padding-block: ${spacing[8]};
    overflow-x: hidden;
    pointer-events: none;
  `,
  actionButtonWrapper: ({ isEdit }: { isEdit: boolean }) => css`
    display: flex;
    justify-content: flex-end;
    gap: ${spacing[8]};
    opacity: 0;
    transition: opacity 0.15s ease-in-out;

    ${isEdit &&
    css`
      opacity: 1;
    `}
  `,
};
