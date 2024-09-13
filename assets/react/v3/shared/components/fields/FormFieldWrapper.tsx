import { type SerializedStyles, css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import type { ReactNode } from 'react';

import Button from '@Atoms/Button';
import LoadingSpinner from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';

import { useModal } from '@Components/modals/Modal';
import AiProIdentifierModal from '@CourseBuilderComponents/modals/AiProIdentifierModal';
import SetupOpenAiModal from '@CourseBuilderComponents/modals/SetupOpenAiModal';

import config, { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, lineHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { nanoid } from '@Utils/util';

import emptyStatImage2x from '@Images/empty-state-illustration-2x.webp';
import emptyStateImage from '@Images/empty-state-illustration.webp';

interface InputOptions {
  variant: unknown;
  hasFieldError: boolean;
  removeBorder: boolean;
  readOnly: boolean;
  hasHelpText: boolean;
  isSecondary: boolean;
  isMagicAi: boolean;
}

interface InputProps {
  id: string;
  name: string;
  css: SerializedStyles[];
  'aria-invalid': 'true' | 'false';
  disabled: boolean;
  readOnly: boolean;
  placeholder?: string;
  className?: string;
}

interface FormFieldWrapperProps<T> extends FormControllerProps<T> {
  label?: string | ReactNode;
  isInlineLabel?: boolean;
  children: (inputProps: InputProps) => ReactNode;
  placeholder?: string;
  variant?: unknown;
  loading?: boolean;
  disabled?: boolean;
  readOnly?: boolean;
  helpText?: string;
  isHidden?: boolean;
  removeBorder?: boolean;
  characterCount?: { maxLimit: number; inputCharacter: number };
  isSecondary?: boolean;
  inputStyle?: SerializedStyles;
  generateWithAi?: boolean;
  onClickAiButton?: () => void;
  isMagicAi?: boolean;
  replaceEntireLabel?: boolean;
}

const styles = {
  container: ({ disabled, isHidden }: { disabled: boolean; isHidden: boolean }) => css`
    display: flex;
    flex-direction: column;
    position: relative;
    background: inherit;
    width: 100%;

    ${
      disabled &&
      css`
      opacity: 0.5;
    `
    }

    ${
      isHidden &&
      css`
      display: none;
    `
    }
  `,
  inputContainer: (isInlineLabel: boolean) => css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
    width: 100%;

    ${
      isInlineLabel &&
      css`
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
      gap: ${spacing[12]};
    `
    }
  `,
  input: (options: InputOptions) => css`
    &.tutor-input-field {
      width: 100%;
      border-radius: ${borderRadius[6]};
      border: 1px solid ${colorTokens.stroke.default};
      padding: ${spacing[8]} ${spacing[16]};
      color: ${colorTokens.text.title};
      appearance: textfield;

      &:not(textarea) {
        height: 40px;
      }

      ${
        options.hasHelpText &&
        css`
        padding: 0 ${spacing[32]} 0 ${spacing[12]};
      `
      }

      ${
        options.removeBorder &&
        css`
        border-radius: 0;
        border: none;
        box-shadow: none;
      `
      }

      ${
        options.isSecondary &&
        css`
        border-color: transparent;
      `
      }

      :focus {
        ${styleUtils.inputFocus};

        ${
          options.isMagicAi &&
          css`
          outline-color: ${colorTokens.stroke.magicAi};
          background-color: ${colorTokens.background.magicAi[8]};
        `
        } 

        ${
          options.hasFieldError &&
          css`
          border-color: ${colorTokens.stroke.danger};
        `
        }
      }

      ::-webkit-outer-spin-button,
      ::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
      }

      ::placeholder {
        ${typography.caption('regular')};
        color: ${colorTokens.text.hints};

        ${
          options.isSecondary &&
          css`
          color: ${colorTokens.text.hints};
        `
        }
      }

      ${
        options.hasFieldError &&
        css`
        border-color: ${colorTokens.stroke.danger};
        background-color: ${colorTokens.background.status.errorFail};
      `
      }

      ${
        options.readOnly &&
        css`
        border-color: ${colorTokens.background.disable};
        background-color: ${colorTokens.background.disable};
      `
      }
    }
    
  `,
  errorLabel: (hasError: boolean) => css`
    ${typography.small()};
    line-height: ${lineHeight[20]};
    display: flex;
    align-items: start;
    margin-top: ${spacing[4]};
    ${
      hasError &&
      css`
      color: ${colorTokens.text.status.onHold};
    `
    }
    & svg {
      margin-right: ${spacing[2]};
      transform: rotate(180deg);
    }
  `,
  labelContainer: css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};

    > div {
      display: flex;
      color: ${colorTokens.color.black[30]};
    }
  `,
  label: (isInlineLabel: boolean, replaceEntireLabel: boolean) => css`
    ${typography.caption()};
    width: ${replaceEntireLabel ? '100%' : 'auto'};
    color: ${colorTokens.text.title};
    display: flex;
    align-items: center;
    gap: ${spacing[4]};

    ${
      isInlineLabel &&
      css`
      ${typography.caption()};
    `
    }
  `,
  aiButton: css`
    ${styleUtils.resetButton};
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    
    :disabled {
      cursor: not-allowed;
    }
  `,
  inputWrapper: css`
    position: relative;
  `,
  loader: css`
    position: absolute;
    top: 50%;
    right: ${spacing[12]};
    transform: translateY(-50%);
    display: flex;
  `,
  alertIcon: css`
    flex-shrink: 0;
  `,
  aiGradientText: css`
    background: ${colorTokens.text.ai.gradient};
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  `,
};

const isTutorPro = !!tutorConfig.tutor_pro_url;
const hasOpenAiAPIKey = tutorConfig.settings.chatgpt_key_exist;

const FormFieldWrapper = <T,>({
  field,
  fieldState,
  children,
  disabled = false,
  readOnly = false,
  label,
  isInlineLabel = false,
  variant,
  loading,
  placeholder,
  helpText,
  isHidden = false,
  removeBorder = false,
  characterCount,
  isSecondary = false,
  inputStyle,
  onClickAiButton,
  isMagicAi = false,
  generateWithAi = false,
  replaceEntireLabel = false,
}: FormFieldWrapperProps<T>) => {
  const id = nanoid();
  const { showModal } = useModal();

  const inputCss = [
    styles.input({
      variant,
      hasFieldError: !!fieldState.error,
      removeBorder,
      readOnly,
      hasHelpText: !!helpText,
      isSecondary,
      isMagicAi,
    }),
  ];

  if (isDefined(inputStyle)) {
    inputCss.push(inputStyle);
  }

  const inputContent = (
    <div css={styles.inputWrapper}>
      {children({
        id,
        name: field.name,
        css: inputCss,
        'aria-invalid': fieldState.error ? 'true' : 'false',
        disabled: disabled,
        readOnly: readOnly,
        placeholder,
        className: 'tutor-input-field',
      })}

      {loading && (
        <div css={styles.loader}>
          <LoadingSpinner size={20} color={colorTokens.icon.default} />
        </div>
      )}
    </div>
  );

  return (
    <div css={styles.container({ disabled, isHidden })}>
      <div css={styles.inputContainer(isInlineLabel)}>
        {(label || helpText) && (
          <div css={styles.labelContainer}>
            {label && (
              <label htmlFor={id} css={styles.label(isInlineLabel, replaceEntireLabel)}>
                {label}
                <Show when={generateWithAi}>
                  <button
                    type="button"
                    onClick={() => {
                      if (!isTutorPro) {
                        showModal({
                          component: AiProIdentifierModal,
                          props: {
                            title: (
                              <>
                                {__('Upgrade to Tutor Pro to enjoy the Tutor LMS ', 'tutor')}
                                <span css={styles.aiGradientText}>{__('AI Studio', 'tutor')} </span>
                                {__('feature', 'tutor')}
                              </>
                            ),
                            image: emptyStateImage,
                            image2x: emptyStatImage2x,
                            featuresTitle: __('Don’t miss out on this game-changing feature! Here’s why:', 'tutor'),
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
                        });
                      } else {
                        onClickAiButton?.();
                      }
                    }}
                    css={styles.aiButton}
                  >
                    <SVGIcon name="magicAiColorize" width={32} height={32} />
                  </button>
                </Show>
              </label>
            )}

            {helpText && !replaceEntireLabel && (
              <Tooltip content={helpText} placement="top" allowHTML>
                <SVGIcon name="info" width={20} height={20} />
              </Tooltip>
            )}
          </div>
        )}

        {characterCount ? (
          <Tooltip
            placement="right"
            hideOnClick={false}
            content={
              characterCount.maxLimit - characterCount.inputCharacter >= 0
                ? characterCount.maxLimit - characterCount.inputCharacter
                : __('Limit exceeded', 'tutor')
            }
          >
            {inputContent}
          </Tooltip>
        ) : (
          inputContent
        )}
      </div>
      {fieldState.error?.message && (
        <p css={styles.errorLabel(!!fieldState.error)}>
          <SVGIcon style={styles.alertIcon} name="info" width={20} height={20} /> {fieldState.error.message}
        </p>
      )}
    </div>
  );
};

export default FormFieldWrapper;
