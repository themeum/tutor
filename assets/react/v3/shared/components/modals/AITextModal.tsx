import AiButton from '@Atoms/AiButton';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { OptionList } from '@Components/magic-ai-content/OptionList';
import { PromptControls } from '@Components/magic-ai-content/PromptControls';
import SkeletonLoader from '@Components/magic-ai-content/SkeletonLoader';
import { inspirationPrompts } from '@Components/magic-ai-image/ImageContext';
import { type ChatFormat, type ChatLanguage, type ChatTone, languageOptions, toneOptions } from '@Config/magic-ai';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
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
}

export interface GenerateTextFieldProps {
  prompt: string;
  characters: number;
  language: ChatLanguage;
  tone: ChatTone;
  format: ChatFormat;
}

const mockContent = [
  'Lorem ipsum dolor sit amet consectetur adipisicing elit. Culpa aspernatur eligendi suscipit nulla amet, in voluptatum quisquam. Non, atque facilis similique deleniti illum adipisci? Rem repellat qui ea! Repellendus neque repellat id modi odit perferendis nam corrupti officiis eum quis at animi vel vitae magnam doloremque nulla fugit, asperiores fugiat ex incidunt! Consectetur quasi atque impedit sapiente, aliquam debitis quidem eaque labore nihil voluptatem, aperiam deleniti illo excepturi quae nemo. Accusantium amet iste quasi atque, iure molestiae quibusdam vero enim cum repudiandae tempore rem vitae laboriosam, repellendus, ex incidunt eum eius neque magnam? Corporis reiciendis, ab sed quidem provident eos voluptates suscipit architecto explicabo quo labore maiores magni! Nam asperiores quaerat ullam nulla saepe soluta explicabo, ad molestiae laboriosam pariatur.',
  'Lorem ipsum dolor sit amet consectetur adipisicing elit. Non animi, ipsa impedit architecto iure consequuntur maxime aliquid corporis omnis hic cupiditate cum ea eligendi possimus voluptates quaerat sed adipisci velit.',
  'Lorem ipsum dolor sit amet consectetur adipisicing elit. Labore sequi reprehenderit totam mollitia dolore animi eaque assumenda consectetur autem natus minus, dolor eum aliquam modi rem ab qui voluptatibus fugit? Autem iusto cupiditate odio maxime cumque amet similique doloremque maiores.',
  'Lorem ipsum dolor sit amet consectetur adipisicing elit. Praesentium illum ipsam explicabo ratione illo sapiente aut optio animi, voluptates, corrupti minus rerum consequatur natus reiciendis, placeat assumenda harum vitae tempore accusamus ad. Quasi rem laboriosam veritatis esse illo, eum repudiandae consectetur quam in. Voluptatum, architecto? Minus, praesentium. Dicta minima culpa consequuntur aspernatur nostrum eveniet, officia placeat nulla non quod sapiente vero odit eius vel minus? Ratione, tempora perspiciatis similique, voluptates iste quaerat iure nesciunt, eveniet harum veniam ex veritatis repellat quis. Iure tenetur fugiat distinctio sunt quasi assumenda dolores at quibusdam ipsam nihil facere dolorum quo eum atque nesciunt eaque, commodi doloribus corrupti. Dignissimos, modi? Soluta consequatur harum perferendis distinctio animi maiores doloremque optio. Sequi ducimus distinctio est cumque placeat praesentium nihil nam et rerum. Vel libero quibusdam quasi, praesentium reiciendis eveniet repudiandae consectetur inventore omnis, perferendis voluptatum expedita. Quo nam facilis assumenda, sapiente ipsam unde, at odit deleniti incidunt voluptas accusantium porro voluptatibus cum. Voluptatibus inventore soluta quia assumenda alias repudiandae atque? Aliquam, ipsam dolorem incidunt, quas minima dolores iure quasi accusamus asperiores velit laborum dolor corrupti commodi blanditiis perspiciatis earum nesciunt quo repudiandae architecto officiis? Iure quis odit iusto sit nihil aut nulla tempore sed distinctio. Unde blanditiis architecto molestiae accusamus, ea quasi repudiandae modi accusantium eligendi, voluptatibus facilis repellat adipisci harum? Facere aut pariatur est excepturi maxime voluptatem obcaecati asperiores dolor sapiente, accusamus quibusdam reprehenderit iste amet quisquam in, id possimus, laborum alias! Cumque id, pariatur voluptatem fuga animi quae laborum laudantium impedit dicta nam doloribus totam et eaque aliquam sunt, velit perspiciatis qui porro facilis quidem! Eius aspernatur amet cumque ratione, deleniti fugiat magni ipsa, velit voluptatibus quae temporibus ipsam enim, mollitia sint deserunt iste a. Iusto suscipit laborum at aliquam provident cumque nesciunt saepe, pariatur culpa aliquid deserunt! Consectetur sint, quae at illo minima eum mollitia obcaecati libero pariatur modi rerum quod officia itaque ipsam adipisci, officiis dolor, aperiam similique aliquam quo voluptatum nesciunt! Illo cumque est animi, praesentium saepe adipisci incidunt quasi a! Eos obcaecati modi dolorem nesciunt iusto totam, error atque nulla quisquam temporibus, facilis nihil neque veniam ut illo quis eius cum! Officiis, fugiat nulla. Accusantium, earum quos dolor totam a, animi tempore accusamus nobis iste placeat laboriosam neque soluta, culpa deleniti reiciendis itaque eaque cupiditate? Fugiat nulla consequuntur ad recusandae non maiores molestias obcaecati voluptatem facere reprehenderit aperiam nisi unde, ab numquam cumque itaque labore laborum neque magnam aliquid beatae? Molestiae laudantium blanditiis laboriosam eligendi magnam sapiente nobis quae voluptates consequatur inventore deserunt accusantium itaque quisquam minus officia consequuntur eaque, eum nesciunt libero dolores doloremque assumenda distinctio at. Aliquid sunt qui voluptas voluptate perspiciatis? Eius dignissimos magnam eveniet assumenda quia nam sunt sequi consectetur magni et est molestiae cum perspiciatis atque ea velit officiis quos, aliquid voluptatum alias iure inventore. Nobis ducimus, sunt libero facere qui ex excepturi repudiandae quas, voluptates odit adipisci expedita ipsa nostrum nisi. Odio, harum qui. Consequatur tempore, natus quibusdam a labore, aliquid ullam provident maiores voluptatem doloribus itaque ab quidem voluptate voluptas aspernatur eveniet dignissimos excepturi.',
];

const AITextModal = <T extends FieldValues>({
  title,
  icon,
  closeModal,
  field,
  fieldState,
  is_html = false,
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
                  label={__('Describe your text', 'tutor')}
                  placeholder={__('Write 5 words to describe...', 'tutor')}
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
                    <Button
                      variant="text"
                      onClick={() => setPointer((previous) => Math.max(0, previous - 1))}
                      disabled={pointer === 0}
                    >
                      <SVGIcon name="chevronLeft" width={20} height={20} />
                    </Button>
                    <Button
                      variant="text"
                      onClick={() => setPointer((previous) => Math.min(content.length - 1, previous + 1))}
                      disabled={pointer === content.length - 1}
                    >
                      <SVGIcon name="chevronRight" width={20} height={20} />
                    </Button>
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
                <AiButton variant="outline" roundedFull={false} onClick={() => handleContentModification('rephrase')}>
                  {__('Rephrase', 'tutor')}
                </AiButton>
                <AiButton
                  variant="outline"
                  roundedFull={false}
                  onClick={() => handleContentModification('make_shorter')}
                >
                  {__('Make shorter', 'tutor')}
                </AiButton>
                <AiButton variant="outline" roundedFull={false} ref={toneRef} onClick={() => setPopover('tone')}>
                  {__('Change tone', 'tutor')}
                  <SVGIcon name="chevronDown" width={16} height={16} />
                </AiButton>
                <AiButton
                  variant="outline"
                  roundedFull={false}
                  ref={translateRef}
                  onClick={() => setPopover('translate')}
                >
                  {__('Translate to', 'tutor')}
                  <SVGIcon name="chevronDown" width={16} height={16} />
                </AiButton>
                <AiButton
                  variant="outline"
                  roundedFull={false}
                  onClick={() => handleContentModification('write_as_bullets')}
                >
                  {__('Write as bullets', 'tutor')}
                </AiButton>
                <AiButton
                  variant="outline"
                  roundedFull={false}
                  onClick={() => handleContentModification('make_longer')}
                >
                  {__('Make longer', 'tutor')}
                </AiButton>
                <AiButton
                  variant="outline"
                  roundedFull={false}
                  onClick={() => handleContentModification('simplify_language')}
                >
                  {__('Simplify language', 'tutor')}
                </AiButton>
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
              <AiButton
                type="submit"
                disabled={magicTextGenerationMutation.isPending || !prompt || modifyContentMutation.isPending}
              >
                <SVGIcon name="magicWand" width={24} height={24} />
                {__('Generate now', 'tutor')}
              </AiButton>
            }
          >
            <AiButton
              variant="outline"
              type="submit"
              disabled={magicTextGenerationMutation.isPending || !prompt || modifyContentMutation.isPending}
            >
              {__('Generate again', 'tutor')}
            </AiButton>
            <AiButton
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
            </AiButton>
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
