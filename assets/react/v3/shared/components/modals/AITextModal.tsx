import Button from '@Atoms/Button';
import MagicButton from '@Atoms/MagicButton';
import SVGIcon from '@Atoms/SVGIcon';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { OptionList } from '@Components/magic-ai-content/OptionList';
import { PromptControls } from '@Components/magic-ai-content/PromptControls';
import SkeletonLoader from '@Components/magic-ai-content/SkeletonLoader';
import { inspirationPrompts } from '@Components/magic-ai-image/ImageContext';
import { type ChatFormat, type ChatLanguage, type ChatTone, languageOptions, toneOptions } from '@Config/magic-ai';
import { borderRadius, colorTokens, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import {
  type ModificationType,
  useMagicTextGenerationMutation,
  useModifyContentMutation,
} from '@CourseBuilderServices/magic-ai';
import { AnimationType } from '@Hooks/useAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import Popover from '@Molecules/Popover';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useMemo, useRef, useState } from 'react';
import {
  Controller,
  type ControllerFieldState,
  type ControllerRenderProps,
  type FieldValues,
  type Path,
} from 'react-hook-form';
import BasicModalWrapper from './BasicModalWrapper';
import type { ModalProps } from './Modal';

interface AITextModalProps<T extends FieldValues> extends ModalProps {
  field: ControllerRenderProps<T, Path<T>>;
  fieldState: ControllerFieldState;
  is_html?: boolean;
  fieldLabel?: string;
  fieldPlaceholder?: string;
}

export interface GenerateTextFieldProps {
  prompt: string;
  characters: number;
  language: ChatLanguage;
  tone: ChatTone;
  format: ChatFormat;
}

const AITextModal = <T extends FieldValues>({
  title,
  icon,
  closeModal,
  field,
  fieldState,
  is_html = false,
  fieldLabel = '',
  fieldPlaceholder = '',
}: AITextModalProps<T>) => {
  const form = useFormWithGlobalError<GenerateTextFieldProps>({
    defaultValues: {
      prompt: '',
      characters: 250,
      language: 'english',
      tone: 'formal',
      format: 'essay',
    },
  });
  const magicTextGenerationMutation = useMagicTextGenerationMutation();
  const modifyContentMutation = useModifyContentMutation();
  const [content, setContent] = useState<string[]>([]);
  const [pointer, setPointer] = useState(0);
  const [isCopied, setIsCopied] = useState(false);
  const [popover, setPopover] = useState<'tone' | 'translate' | null>(null);
  const toneRef = useRef<HTMLButtonElement>(null);
  const translateRef = useRef<HTMLButtonElement>(null);

  const currentContent = useMemo(() => {
    return content[pointer];
  }, [content, pointer]);

  const prompt = form.watch('prompt');

  function insertText(text: string) {
    setContent((previous) => [text, ...previous]);
    setPointer(0);
  }

  async function handleContentModification(type: 'translation', language: ChatLanguage): Promise<void>;
  async function handleContentModification(type: 'change_tone', tone: ChatTone): Promise<void>;
  async function handleContentModification(type: ModificationType): Promise<void>;
  async function handleContentModification(
    type: ModificationType | 'translation' | 'change_tone',
    value?: ChatLanguage | ChatTone,
  ) {
    if (content.length === 0) {
      return;
    }
    const currentContent = content[pointer];

    if (type === 'translation' && !!value) {
      const response = await modifyContentMutation.mutateAsync({
        type: 'translation',
        content: currentContent,
        language: value as ChatLanguage,
        is_html,
      });
      if (response.data) {
        insertText(response.data);
      }
      return;
    }

    if (type === 'change_tone' && !!value) {
      const response = await modifyContentMutation.mutateAsync({
        type: 'change_tone',
        content: currentContent,
        tone: value as ChatTone,
        is_html,
      });
      if (response.data) {
        insertText(response.data);
      }
      return;
    }

    const response = await modifyContentMutation.mutateAsync({
      type: type as ModificationType,
      content: currentContent,
      is_html,
    });

    if (response.data) {
      insertText(response.data);
    }
  }

  return (
    <BasicModalWrapper onClose={closeModal} title={title} icon={icon}>
      <form
        css={styles.wrapper}
        onSubmit={form.handleSubmit(async (values) => {
          const response = await magicTextGenerationMutation.mutateAsync({ ...values, is_html });

          if (response.data) {
            insertText(response.data);
          }
        })}
      >
        <div css={styles.container}>
          <div css={styles.fieldsWrapper}>
            <Controller
              control={form.control}
              name="prompt"
              render={(props) => (
                <FormTextareaInput
                  {...props}
                  label={fieldLabel || __('Craft Your Course Description', 'tutor')}
                  placeholder={
                    fieldPlaceholder ||
                    __('Provide a brief overview of your course topic, target audience, and key takeaways', 'tutor')
                  }
                  rows={4}
                  isMagicAi
                />
              )}
            />
            <button
              type="button"
              css={styles.inspireButton}
              onClick={() => {
                const length = inspirationPrompts.length;
                const index = Math.floor(Math.random() * length);
                form.reset({ ...form.getValues(), prompt: inspirationPrompts[index] });
              }}
            >
              <SVGIcon name="bulbLine" />
              {__('Inspire me', 'tutor')}
            </button>
          </div>
          <Show
            when={!magicTextGenerationMutation.isPending && !modifyContentMutation.isPending}
            fallback={<SkeletonLoader />}
          >
            <Show when={content.length > 0} fallback={<PromptControls form={form} />}>
              <div>
                <div css={styles.actionBar}>
                  <div css={styles.navigation}>
                    <Show when={content.length > 1}>
                      <Button
                        variant="text"
                        onClick={() => setPointer((previous) => Math.max(0, previous - 1))}
                        disabled={pointer === 0}
                      >
                        <SVGIcon name="chevronLeft" width={20} height={20} />
                      </Button>

                      <div css={styles.pageInfo}>
                        <span>{pointer + 1}</span> / {content.length}
                      </div>

                      <Button
                        variant="text"
                        onClick={() => setPointer((previous) => Math.min(content.length - 1, previous + 1))}
                        disabled={pointer === content.length - 1}
                      >
                        <SVGIcon name="chevronRight" width={20} height={20} />
                      </Button>
                    </Show>
                  </div>
                  <Button
                    variant="text"
                    onClick={async () => {
                      if (content.length === 0) {
                        return;
                      }
                      const currentContent = content[pointer];
                      await navigator.clipboard.writeText(currentContent);
                      setIsCopied(true);
                      setTimeout(() => {
                        setIsCopied(false);
                      }, 1500);
                    }}
                  >
                    <Show when={isCopied} fallback={<SVGIcon name="copy" width={20} height={20} />}>
                      <SVGIcon
                        name="checkFilled"
                        width={20}
                        height={20}
                        style={css`color: ${colorTokens.text.success} !important;`}
                      />
                    </Show>
                  </Button>
                </div>
                <div css={styles.content} dangerouslySetInnerHTML={{ __html: currentContent }} />
              </div>
              <div css={styles.otherActions}>
                <MagicButton
                  variant="outline"
                  roundedFull={false}
                  onClick={() => handleContentModification('rephrase')}
                >
                  {__('Rephrase', 'tutor')}
                </MagicButton>
                <MagicButton
                  variant="outline"
                  roundedFull={false}
                  onClick={() => handleContentModification('make_shorter')}
                >
                  {__('Make shorter', 'tutor')}
                </MagicButton>
                <MagicButton variant="outline" roundedFull={false} ref={toneRef} onClick={() => setPopover('tone')}>
                  {__('Change tone', 'tutor')}
                  <SVGIcon name="chevronDown" width={16} height={16} />
                </MagicButton>
                <MagicButton
                  variant="outline"
                  roundedFull={false}
                  ref={translateRef}
                  onClick={() => setPopover('translate')}
                >
                  {__('Translate to', 'tutor')}
                  <SVGIcon name="chevronDown" width={16} height={16} />
                </MagicButton>
                <MagicButton
                  variant="outline"
                  roundedFull={false}
                  onClick={() => handleContentModification('write_as_bullets')}
                >
                  {__('Write as bullets', 'tutor')}
                </MagicButton>
                <MagicButton
                  variant="outline"
                  roundedFull={false}
                  onClick={() => handleContentModification('make_longer')}
                >
                  {__('Make longer', 'tutor')}
                </MagicButton>
                <MagicButton
                  variant="outline"
                  roundedFull={false}
                  onClick={() => handleContentModification('simplify_language')}
                >
                  {__('Simplify language', 'tutor')}
                </MagicButton>
              </div>
            </Show>
          </Show>
        </div>
        <Popover
          isOpen={popover === 'tone'}
          triggerRef={toneRef}
          closePopover={() => setPopover(null)}
          maxWidth={'160px'}
          animationType={AnimationType.slideUp}
        >
          <OptionList
            options={toneOptions}
            onChange={async (value) => {
              setPopover(null);
              await handleContentModification('change_tone', value);
            }}
          />
        </Popover>
        <Popover
          isOpen={popover === 'translate'}
          triggerRef={translateRef}
          closePopover={() => setPopover(null)}
          maxWidth={'160px'}
          animationType={AnimationType.slideUp}
        >
          <OptionList
            options={languageOptions}
            onChange={async (value) => {
              setPopover(null);
              await handleContentModification('translation', value);
            }}
          />
        </Popover>
        <div css={styles.footer}>
          <Show
            when={content.length > 0}
            fallback={
              <MagicButton
                type="submit"
                disabled={magicTextGenerationMutation.isPending || !prompt || modifyContentMutation.isPending}
              >
                <SVGIcon name="magicWand" width={24} height={24} />
                {__('Generate now', 'tutor')}
              </MagicButton>
            }
          >
            <MagicButton
              variant="outline"
              type="submit"
              disabled={magicTextGenerationMutation.isPending || !prompt || modifyContentMutation.isPending}
            >
              {__('Generate again', 'tutor')}
            </MagicButton>
            <MagicButton
              variant="primary"
              disabled={
                magicTextGenerationMutation.isPending || content.length === 0 || modifyContentMutation.isPending
              }
              onClick={() => {
                field.onChange(content[pointer]);
                closeModal();
              }}
            >
              {__('Use this', 'tutor')}
            </MagicButton>
          </Show>
        </div>
      </form>
    </BasicModalWrapper>
  );
};

export default AITextModal;
const styles = {
  wrapper: css`
		width: 524px;
	`,
  container: css`
		padding: ${spacing[20]};
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
	`,
  fieldsWrapper: css`
		position: relative;
		textarea {
      padding-bottom: ${spacing[40]} !important;
    }
	`,
  footer: css`
		padding: ${spacing[12]} ${spacing[16]};
		display: flex;
		align-items: center;
		justify-content: end;
		gap: ${spacing[10]};
		box-shadow: 0px 1px 0px 0px #E4E5E7 inset;

		button {
			width: fit-content;
		}
	`,
  pageInfo: css`
		${typography.caption()};
		color: ${colorTokens.text.hints};
		
		& > span {
			font-weight: ${fontWeight.medium};
			color: ${colorTokens.text.primary};
		}
	`,
  inspireButton: css`
		${styleUtils.resetButton};	
		${typography.small()};
		position: absolute;
		height: 28px;
		bottom: ${spacing[12]};
		left: ${spacing[12]};
		border: 1px solid ${colorTokens.stroke.brand};
		border-radius: ${borderRadius[4]};
		display: flex;
		align-items: center;
		gap: ${spacing[4]};
		color: ${colorTokens.text.brand};
		padding-inline: ${spacing[12]};
		background-color: ${colorTokens.background.white};

		&:hover {
			background-color: ${colorTokens.background.brand};
			color: ${colorTokens.text.white};
		}
	`,
  navigation: css`
		margin-left: -${spacing[8]};
		display: flex;
		align-items: center;
	`,
  content: css`
		${typography.caption()};
		height: 180px;
		overflow-y: auto;
		background-color: ${colorTokens.background.magicAi.default};
		border-radius: ${borderRadius[6]};
		padding: ${spacing[6]} ${spacing[12]};
		color: ${colorTokens.text.magicAi};
	`,
  actionBar: css`
		display: flex;
		align-items: center;
		justify-content: space-between;
	`,
  otherActions: css`
		display: flex;
		gap: ${spacing[10]};
		flex-wrap: wrap;

		& > button {
			width: fit-content;
		}
	`,
};
