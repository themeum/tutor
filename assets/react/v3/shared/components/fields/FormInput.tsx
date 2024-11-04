import { type SerializedStyles, css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from 'react';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import FormFieldWrapper from '@Components/fields/FormFieldWrapper';
import AITextModal from '@Components/modals/AITextModal';
import { useModal } from '@Components/modals/Modal';
import ProIdentifierModal from '@CourseBuilderComponents/modals/ProIdentifierModal';
import SetupOpenAiModal from '@CourseBuilderComponents/modals/SetupOpenAiModal';

import config, { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import Show from '@Controls/Show';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { parseNumberOnly } from '@Utils/util';

import generateText2x from '@Images/pro-placeholders/generate-text-2x.webp';
import generateText from '@Images/pro-placeholders/generate-text.webp';

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
      onClickAiButton={() => {
        if (!isTutorPro) {
          showModal({
            component: ProIdentifierModal,
            props: {
              title: (
                <>
                  {__('Upgrade to Tutor LMS Pro today and experience the power of ', 'tutor')}
                  <span css={styleUtils.aiGradientText}>{__('AI Studio', 'tutor')} </span>
                </>
              ),
              image: generateText,
              image2x: generateText2x,
              featuresTitle: __('Don’t miss out on this game-changing feature!', 'tutor'),
              features: [
                __('Whip up a course outline in mere seconds—no sweat, no stress.', 'tutor'),
                __(
                  'Let the AI Studio create Quizzes on your behalf and give your brain a well-deserved break.',
                  'tutor',
                ),
                __(
                  'Want to jazz up your course? Generate images, tweak backgrounds, or even ditch unwanted objects with ease.',
                  'tutor',
                ),
                __('Say goodbye to pricey grammar checkers—copy editing is now a breeze!', 'tutor'),
              ],
              footer: (
                <Button
                  onClick={() => window.open(config.TUTOR_PRICING_PAGE, '_blank', 'noopener')}
                  icon={<SVGIcon name="crown" width={24} height={24} />}
                >
                  {__('Get Tutor LMS Pro', 'tutor')}
                </Button>
              ),
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
              field,
              fieldState,
              is_html: true,
              fieldLabel: __('Create a Compelling Title', 'tutor'),
              fieldPlaceholder: __('Describe the main focus of your course in a few words', 'tutor'),
            },
          });
          onClickAiButton?.();
        }
      }}
      isMagicAi={isMagicAi}
    >
      {(inputProps) => {
        const ref = useRef<HTMLInputElement>(null);
        return (
          <>
            <div css={styles.container(isClearable || isPassword)}>
              <input
                {...field}
                {...inputProps}
                {...additionalAttributes}
                type={fieldType === 'number' ? 'text' : fieldType}
                value={inputValue}
                // biome-ignore lint/a11y/noAutofocus: <explanation>
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

export default FormInput;

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
  eyeButtonWrapper: css`
		position: absolute;
		display: flex;
		right: ${spacing[8]};
		top: 50%;
		transform: translateY(-50%);
		border-radius: ${borderRadius[2]};
		background: transparent;
	`,

  eyeButton: ({ type }: { type: 'password' | 'text' | 'number' }) => css`
		${styleUtils.resetButton}
		${styleUtils.flexCenter()}
    color: ${colorTokens.icon.default};

		${
      type !== 'password' &&
      css`
			color: ${colorTokens.icon.brand};
		`
    }
	`,
};
