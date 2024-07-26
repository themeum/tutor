import { css } from '@emotion/react';
import { useEffect, useRef, useState } from 'react';

import Button from '@Atoms/Button';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { FormControllerProps } from '@Utils/form';
import { isDefined } from '@Utils/types';

import { __ } from '@wordpress/i18n';
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

const DEFAULT_ROWS = 6;

const FormAnswerExplanation = ({
  label,
  rows = DEFAULT_ROWS,
  columns,
  maxLimit,
  field,
  fieldState,
  disabled,
  readOnly,
  loading,
  placeholder,
  helpText,
  onChange,
  onKeyDown,
  isHidden,
  enableResize = false,
  isSecondary = false,
}: FormAnswerExplanationProps) => {
  const inputValue = field.value ?? '';
  const textareaRef = useRef<HTMLTextAreaElement>(null);

  const [isEdit, setIsEdit] = useState<boolean>(false);
  const [previousValue, setPreviousValue] = useState<string>(inputValue);

  let characterCount:
    | {
        maxLimit: number;
        inputCharacter: number;
      }
    | undefined = undefined;

  if (maxLimit) {
    characterCount = { maxLimit, inputCharacter: inputValue.toString().length };
  }

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (isDefined(textareaRef.current)) {
      textareaRef.current.focus();
      setPreviousValue(inputValue);
    }
  }, [isEdit, textareaRef.current]);

  return (
    <div css={styles.container({ isEdit: isEdit || !!inputValue })}>
      <Show
        when={isEdit}
        fallback={
          <Show
            when={inputValue}
            fallback={
              <div
                css={styles.placeholder}
                role="button"
                onClick={() => setIsEdit(true)}
                onKeyDown={(event) => {
                  if (event.key === 'Enter' || event.key === ' ') {
                    setIsEdit(true);
                  }
                }}
              >
                {placeholder}
              </div>
            }
          >
            <div
              css={styles.answer}
              role="button"
              onClick={() => setIsEdit(true)}
              onKeyDown={(event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                  setIsEdit(true);
                }
              }}
            >
              <div css={styles.answerLabel}>{__('Answer explanation', 'tutor')}</div>
              <p
                dangerouslySetInnerHTML={{
                  __html: inputValue,
                }}
              />
            </div>
          </Show>
        }
      >
        <FormWPEditor
          field={field}
          fieldState={fieldState}
          disabled={disabled}
          helpText={helpText}
          key={field.name}
          label={label}
          loading={loading}
          onChange={onChange}
          placeholder={placeholder}
          readOnly={readOnly}
        />
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
              setIsEdit(false);
            }}
            disabled={field.value === previousValue}
          >
            Ok
          </Button>
        </div>
      </Show>
    </div>
  );
};

export default FormAnswerExplanation;

const styles = {
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
    border-radius: ${borderRadius[6]};
    transition: background 0.15s ease-in-out;

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
};
