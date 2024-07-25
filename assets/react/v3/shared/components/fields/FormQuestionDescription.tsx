import { css } from '@emotion/react';
import { useEffect, useRef, useState } from 'react';

import Button from '@Atoms/Button';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { FormControllerProps } from '@Utils/form';
import { isDefined } from '@Utils/types';

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
  maxLimit,
  field,
  fieldState,
  disabled,
  readOnly,
  loading,
  placeholder,
  helpText,
  onChange,
}: FormQuestionDescriptionProps) => {
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
    <div
      css={styles.container({ isEdit })}
      onClick={() => {
        if (!isEdit) {
          setIsEdit(true);
        }
      }}
      onKeyDown={(event) => {
        if ((event.key === 'Enter' || event.key === ' ') && !isEdit) {
          setIsEdit(true);
        }
      }}
    >
      <Show
        when={isEdit}
        fallback={
          <div css={styles.placeholder} dangerouslySetInnerHTML={{ __html: field.value || placeholder || '' }} />
        }
      >
        <FormWPEditor
          field={field}
          fieldState={fieldState}
          label={label}
          disabled={disabled}
          helpText={helpText}
          key={field.name}
          loading={loading}
          readOnly={readOnly}
          onChange={onChange}
          placeholder={placeholder}
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

export default FormQuestionDescription;

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
    min-height: 64px;
    height: 100%;
    width: 100%;
    inset: 0;
    padding-inline: ${spacing[8]} ${spacing[16]};
    border-radius: ${borderRadius[6]};
    transition: background 0.15s ease-in-out;

    & label {
      ${typography.caption()}
      margin-bottom: ${spacing[6]};
      color: ${colorTokens.text.title};
    }

    &:hover {
      background-color: ${colorTokens.background.white};
      color: ${colorTokens.text.subdued};

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

    ${
      isEdit &&
      css`
        padding-inline: 0;
      `
    }
  `,
  inputContainer: (enableResize: boolean) => css`
    position: relative;
    display: flex;
    cursor: text;

    & textarea {
      ${typography.heading6()}
      height: auto;
      resize: none;

      &.tutor-input-field {
        padding: ${spacing[8]};
      }

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
    ${typography.heading6()}
    color: ${colorTokens.text.hints};
    height: 100%;
    inset: 0;
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
};
