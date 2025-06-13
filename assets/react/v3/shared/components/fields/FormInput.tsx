import { type SerializedStyles, css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import FormFieldWrapper from '@TutorShared/components/fields/FormFieldWrapper';
import AITextModal from '@TutorShared/components/modals/AITextModal';
import { useModal } from '@TutorShared/components/modals/Modal';
import ProIdentifierModal from '@TutorShared/components/modals/ProIdentifierModal';
import SetupOpenAiModal from '@TutorShared/components/modals/SetupOpenAiModal';

import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import { withVisibilityControl } from '@TutorShared/hoc/withVisibilityControl';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { isDefined } from '@TutorShared/utils/types';
import { parseNumberOnly } from '@TutorShared/utils/util';

import generateText2x from '@SharedImages/pro-placeholders/generate-text-2x.webp';
import generateText from '@SharedImages/pro-placeholders/generate-text.webp';

interface FormInputProps extends FormControllerProps<string | number | null> {
  label?: string | React.ReactNode;
  type?: 'number' | 'text' | 'password';
  maxLimit?: number;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string | number) => void;
  onKeyDown?: (keyName: string) => void;
  isHidden?: boolean;
  isClearable?: boolean;
  isSecondary?: boolean;
  removeBorder?: boolean;
  dataAttribute?: string;
  isInlineLabel?: boolean;
  isPassword?: boolean;
  style?: SerializedStyles;
  selectOnFocus?: boolean;
  autoFocus?: boolean;
  generateWithAi?: boolean;
  onClickAiButton?: () => void;
  isMagicAi?: boolean;
  allowNegative?: boolean;
}

const isTutorPro = !!tutorConfig.tutor_pro_url;
const hasOpenAiAPIKey = tutorConfig.settings?.chatgpt_key_exist;

const FormInput = ({
  label,
  type = 'text',
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
  isClearable = false,
  isSecondary = false,
  removeBorder,
  dataAttribute,
  isInlineLabel = false,
  isPassword = false,
  style,
  selectOnFocus = false,
  autoFocus = false,
  generateWithAi = false,
  isMagicAi = false,
  allowNegative = false,
  onClickAiButton,
}: FormInputProps) => {
  const [fieldType, setFieldType] = useState<typeof type>(type);
  const { showModal } = useModal();
  const ref = useRef<HTMLInputElement>(null);

  let inputValue = field.value ?? '';
  let characterCount:
    | {
        maxLimit: number;
        inputCharacter: number;
      }
    | undefined = undefined;
  if (fieldType === 'number') {
    inputValue = parseNumberOnly(`${inputValue}`, allowNegative).replace(/(\..*)\./g, '$1');
  }

  if (maxLimit) {
    characterCount = { maxLimit, inputCharacter: inputValue.toString().length };
  }

  const additionalAttributes = {
    ...(isDefined(dataAttribute) && { [dataAttribute]: true }),
  };

  const handleAiButtonClick = () => {
    if (!isTutorPro) {
      showModal({
        component: ProIdentifierModal,
        props: {
          image: generateText,
          image2x: generateText2x,
        },
      });
    } else if (!hasOpenAiAPIKey) {
      showModal({
        component: SetupOpenAiModal,
        props: {
          image: generateText,
          image2x: generateText2x,
        },
      });
    } else {
      showModal({
        component: AITextModal,
        isMagicAi: true,
        props: {
          title: __('AI Studio', 'tutor'),
          icon: <SVGIcon name="magicAiColorize" width={24} height={24} />,
          characters: 120,
          field,
          fieldState,
          format: 'title',
          is_html: false,
          fieldLabel: __('Create a Compelling Title', 'tutor'),
          fieldPlaceholder: __('Describe the main focus of your course in a few words', 'tutor'),
        },
      });
      onClickAiButton?.();
    }
  };

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      readOnly={readOnly}
      loading={loading}
      placeholder={placeholder}
      helpText={helpText}
      isHidden={isHidden}
      characterCount={characterCount}
      isSecondary={isSecondary}
      removeBorder={removeBorder}
      isInlineLabel={isInlineLabel}
      inputStyle={style}
      generateWithAi={generateWithAi}
      onClickAiButton={handleAiButtonClick}
      isMagicAi={isMagicAi}
    >
      {(inputProps) => {
        return (
          <>
            <div css={styles.container(isClearable || isPassword)}>
              <input
                {...field}
                {...inputProps}
                {...additionalAttributes}
                type={fieldType === 'number' ? 'text' : fieldType}
                value={inputValue}
                autoFocus={autoFocus}
                onChange={(event) => {
                  const { value } = event.target;

                  const fieldValue: string | number = fieldType === 'number' ? parseNumberOnly(value) : value;

                  field.onChange(fieldValue);

                  if (onChange) {
                    onChange(fieldValue);
                  }
                }}
                onClick={(event) => {
                  event.stopPropagation();
                }}
                onKeyDown={(event) => {
                  event.stopPropagation();
                  onKeyDown?.(event.key);
                }}
                autoComplete="off"
                ref={(element) => {
                  field.ref(element);
                  // @ts-ignore
                  ref.current = element; // this is not ideal but it is the only way to set ref to the input element
                }}
                onFocus={() => {
                  if (!selectOnFocus || !ref.current) {
                    return;
                  }
                  ref.current.select();
                }}
              />
              <Show when={isPassword}>
                <div css={styles.eyeButtonWrapper}>
                  <button
                    type="button"
                    css={styles.eyeButton({ type: fieldType })}
                    onClick={() => setFieldType((prev) => (prev === 'password' ? 'text' : 'password'))}
                  >
                    <SVGIcon name="eye" height={24} width={24} />
                  </button>
                </div>
              </Show>
              <Show when={isClearable && !!field.value && fieldType !== 'password'}>
                <div css={styles.clearButton}>
                  <Button variant="text" onClick={() => field.onChange('')}>
                    <SVGIcon name="timesAlt" />
                  </Button>
                </div>
              </Show>
            </div>
          </>
        );
      }}
    </FormFieldWrapper>
  );
};

export default withVisibilityControl(FormInput);

const styles = {
  container: (isClearable: boolean) => css`
    position: relative;
    display: flex;

    input {
      &.tutor-input-field {
        ${isClearable && `padding-right: ${spacing[36]};`};
      }
    }
  `,
  clearButton: css`
    position: absolute;
    right: ${spacing[4]};
    top: ${spacing[4]};
    width: 32px;
    height: 32px;
    background: transparent;

    button {
      padding: ${spacing[8]};
      border-radius: ${borderRadius[2]};
    }
  `,
  eyeButtonWrapper: css`
    position: absolute;
    display: flex;
    right: ${spacing[4]};
    top: 50%;
    transform: translateY(-50%);
  `,

  eyeButton: ({ type }: { type: 'password' | 'text' | 'number' }) => css`
    ${styleUtils.resetButton}
    ${styleUtils.flexCenter()}
    color: ${colorTokens.icon.default};
    padding: ${spacing[4]};
    border-radius: ${borderRadius[2]};
    background: transparent;

    ${type !== 'password' &&
    css`
      color: ${colorTokens.icon.brand};
    `}

    &:focus,
    &:active,
    &:hover {
      background: none;
      color: ${colorTokens.icon.default};
    }

    :focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 2px;
    }
  `,
};
