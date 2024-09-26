import { css } from '@emotion/react';
import { useState } from 'react';

import Button from '@Atoms/Button';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { FormControllerProps } from '@Utils/form';
import { __ } from '@wordpress/i18n';
import FormWPEditor from './FormWPEditor';

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

const FormQuestionDescription = ({
  label,
  field,
  fieldState,
  disabled,
  loading,
  placeholder,
  helpText,
  onChange,
}: FormQuestionDescriptionProps) => {
  const inputValue = field.value ?? '';

  const [isEdit, setIsEdit] = useState<boolean>(false);
  const [previousValue, setPreviousValue] = useState<string>(inputValue);

  return (
    <div css={styles.editorWrapper({ isEdit })}>
      <div css={styles.container({ isEdit })}>
        <Show
          when={!inputValue && !isEdit}
          fallback={
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
            />
          }
        >
          <div css={styles.placeholder}>{placeholder}</div>
        </Show>
        <Show when={isEdit}>
          <div data-action-buttons css={styles.actionButtonWrapper({ isEdit })}>
            <Button
              variant="text"
              size="small"
              onClick={() => {
                field.onChange(previousValue);
                setIsEdit(false);
              }}
            >
              {__('Cancel', 'tutor')}
            </Button>
            <Button
              variant="secondary"
              size="small"
              onClick={() => {
                setIsEdit(false);
                setPreviousValue(field.value || '');
              }}
              disabled={inputValue === previousValue}
            >
              {__('Ok', 'tutor')}
            </Button>
          </div>
        </Show>
        <Show when={!isEdit}>
          <div
            onClick={(e) => {
              if (!isEdit) {
                setIsEdit(true);
              }
            }}
            onKeyDown={(event) => {
              if ((event.key === 'Enter' || event.key === ' ') && !isEdit) {
                setIsEdit(true);
              }
            }}
            data-overlay
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
    overflow-y: scroll;

    ${
      isEdit &&
      css`
        padding-inline: 0;
        max-height: unset;
        overflow: unset;
      `
    }
  `,
  container: ({ isEdit }: { isEdit: boolean }) => css`
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
      opacity: 0;
    }

    & label {
      ${typography.caption()};
      margin-bottom: ${spacing[6]};
      color: ${colorTokens.text.title};
    }

    &:hover {
      background-color: ${!isEdit && colorTokens.background.white};
      color: ${colorTokens.text.subdued};

      [data-action-buttons] {
        opacity: 1;
      }
    }

    ${
      isEdit &&
      css`
        padding-inline: 0;
      `
    }
  `,
  placeholder: css`
    ${typography.caption()}
    color: ${colorTokens.text.hints};
    height: 100%;
    inset: 0;
  `,
  actionButtonWrapper: ({ isEdit }: { isEdit: boolean }) => css`
    display: flex;
    justify-content: flex-end;
    gap: ${spacing[8]};
    opacity: 0;
    transition: opacity 0.15s ease-in-out;

    ${
      isEdit &&
      css`
        opacity: 1;
      `
    }
  `,
  overlay: css`
    position: absolute;
    inset: 0;
  `,
};
