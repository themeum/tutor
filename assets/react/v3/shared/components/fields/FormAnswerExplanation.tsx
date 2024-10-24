import { css } from '@emotion/react';
import { useState } from 'react';

import Button from '@Atoms/Button';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { FormControllerProps } from '@Utils/form';

import { styleUtils } from '@Utils/style-utils';
import FormWPEditor from './FormWPEditor';

interface FormAnswerExplanationProps extends FormControllerProps<string | null> {
  label?: string;
  rows?: number;
  columns?: number;
  maxLimit?: number;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string | number) => void;
  onKeyDown?: (keyName: string) => void;
  isHidden?: boolean;
  enableResize?: boolean;
  isSecondary?: boolean;
}

const FormAnswerExplanation = ({
  label,
  maxLimit,
  field,
  fieldState,
  disabled,
  readOnly,
  loading,
  placeholder,
  helpText,
  onChange,
}: FormAnswerExplanationProps) => {
  const inputValue = field.value ?? '';

  const [isEdit, setIsEdit] = useState<boolean>(false);
  const [previousValue, setPreviousValue] = useState<string>(inputValue);

  return (
    <div
      css={styles.wrapper({
        hasValue: !!inputValue && !isEdit,
      })}
    >
      <Show when={isEdit || inputValue}>
        <label css={styles.answerLabel}>{label}</label>
      </Show>
      <div css={styles.editorWrapper({ isEdit })}>
        <div css={styles.container({ isEdit: isEdit || !!inputValue })}>
          <Show
            when={!inputValue && !isEdit}
            fallback={
              <FormWPEditor
                field={field}
                fieldState={fieldState}
                disabled={disabled}
                helpText={helpText}
                loading={loading}
                readOnly={!isEdit}
                onChange={onChange}
                placeholder={placeholder}
                autoFocus
                isMinimal
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
                Cancel
              </Button>
              <Button
                variant="secondary"
                size="small"
                onClick={() => {
                  setPreviousValue(field.value ?? '');
                  setIsEdit(false);
                }}
                disabled={field.value === previousValue}
              >
                Ok
              </Button>
            </div>
          </Show>
          <Show when={!isEdit}>
            <div
              onClick={(e) => {
                if (!isEdit && !disabled) {
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
    </div>
  );
};

export default FormAnswerExplanation;

const styles = {
  wrapper: ({
    hasValue,
  }: {
    hasValue: boolean;
  }) => css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[10]};
    border-radius: ${borderRadius.card};

    ${
      hasValue &&
      css`
        background-color: ${colorTokens.color.success[30]};
        padding: ${spacing[12]} ${spacing[24]};

        &:hover {
          background-color: ${colorTokens.color.success[40]};
        }
      `
    }
  `,
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
  container: ({
    isEdit,
  }: {
    isEdit: boolean;
  }) => css`
    position: relative;
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
    min-height: 48px;
    height: 100%;
    width: 100%;
    inset: 0;
    border-radius: ${borderRadius[6]};
    transition: background 0.15s ease-in-out;

    [data-overlay] {
      position: absolute;
      inset: 0;
      opacity: 0;
    }

    & label {
      ${typography.caption()}
      margin-bottom: ${spacing[6]};
      color: ${colorTokens.text.title};
    }

    ${
      isEdit &&
      css`
      padding: 0;
    `
    }

    &:hover {
      background-color: ${colorTokens.background.white};
      color: ${colorTokens.text.subdued};
      cursor: text;

			[data-action-buttons] {
				opacity: 1;
			}

      ${
        isEdit &&
        css`
        background-color: transparent;
      `
      }
      
    };
  `,
  inputContainer: (enableResize: boolean) => css`
    position: relative;
    display: flex;

    & textarea {
      ${typography.caption()}
      color: ${colorTokens.text.title};
      height: auto;
      padding: ${spacing[8]} ${spacing[12]};
      resize: none;

      ${
        enableResize &&
        css`
        resize: vertical;
      `
      }
    }
  `,
  clearButton: css`
    position: absolute;
    right: ${spacing[2]};
    top: ${spacing[2]};
    width: 36px;
    height: 36px;
    border-radius: ${borderRadius[2]};
    background: transparent;

    button {
      padding: ${spacing[10]};
    }
  `,
  placeholder: css`
    padding: ${spacing[12]} ${spacing[24]};
    ${typography.caption()}
    color: ${colorTokens.text.hints};
    display: flex;
    align-items: center;
    height: 100%;
  `,
  actionButtonWrapper: ({
    isEdit,
  }: {
    isEdit: boolean;
  }) => css`
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
  answer: css`
    padding: ${spacing[12]} ${spacing[24]};
    border-radius: ${borderRadius.card};
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
    background-color: ${colorTokens.background.success.fill30};
    color: ${colorTokens.text.title};
    transition: background 0.15s ease-in-out;

    &:hover {
      background-color: ${colorTokens.background.success.fill40};
    }
  `,
  answerLabel: css`
    ${typography.caption()}
    color: ${colorTokens.text.title};
  `,
  answerParagraph: css`
    pre {
      ${styleUtils.overflowXAuto}
    }
  `,
};
