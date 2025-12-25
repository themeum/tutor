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
import { colorTokens, spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import { withVisibilityControl } from '@TutorShared/hoc/withVisibilityControl';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { isDefined } from '@TutorShared/utils/types';
import { parseNumberOnly } from '@TutorShared/utils/util';

import generateText2x from '@SharedImages/pro-placeholders/generate-text-2x.webp';
import generateText from '@SharedImages/pro-placeholders/generate-text.webp';
import { styleUtils } from '@TutorShared/utils/style-utils';

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
          title: __('AI Studio', __TUTOR_TEXT_DOMAIN__),
          icon: <SVGIcon name="magicAiColorize" width={24} height={24} />,
          characters: 120,
          field,
          fieldState,
          format: 'title',
          is_html: false,
          fieldLabel: __('Create a Compelling Title', __TUTOR_TEXT_DOMAIN__),
          fieldPlaceholder: __('Describe the main focus of your course in a few words', __TUTOR_TEXT_DOMAIN__),
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
                <Button
                  isIconOnly
                  variant="text"
                  size="small"
                  onClick={() => setFieldType((prev) => (prev === 'password' ? 'text' : 'password'))}
                  icon={<SVGIcon name="eye" width={24} height={24} />}
                  aria-label={__('Show/Hide Password', __TUTOR_TEXT_DOMAIN__)}
                  buttonCss={styles.eyeButton({ type: fieldType })}
                />
              </Show>
              <Show when={isClearable && !!field.value && fieldType !== 'password'}>
                <Button
                  isIconOnly
                  variant="text"
                  size="small"
                  onClick={() => field.onChange('')}
                  buttonCss={styleUtils.inputClearButton}
                  icon={<SVGIcon name="cross" width={24} height={24} />}
                  aria-label={__('Clear', __TUTOR_TEXT_DOMAIN__)}
                />
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
  eyeButton: ({ type }: { type: 'password' | 'text' | 'number' }) => css`
    ${styleUtils.inputClearButton};
    ${type !== 'password' &&
    css`
      svg {
        color: ${colorTokens.icon.brand};
      }
    `}
  `,
};
