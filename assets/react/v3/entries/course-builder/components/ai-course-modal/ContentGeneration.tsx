import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';
import { GradientLoadingSpinner } from '@Atoms/LoadingSpinner';
import MagicButton from '@Atoms/MagicButton';
import SVGIcon from '@Atoms/SVGIcon';
import { useToast } from '@Atoms/Toast';

import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { Breakpoint, borderRadius, colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { useSaveAIGeneratedCourseContentMutation } from '@CourseBuilderServices/magic-ai';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';
import { getObjectKeys, getObjectValues } from '@Utils/util';

import { useGenerateCourseContent } from '../../hooks/useGenerateCourseContent';
import ContentAccordion from './ContentAccordion';
import { type Loading, useContentGenerationContext } from './ContentGenerationContext';
import ContentSkeleton from './loaders/ContentSkeleton';
import DescriptionSkeleton from './loaders/DescriptionSkeleton';
import ImageSkeleton from './loaders/ImageSkeleton';
import TitleSkeleton from './loaders/TitleSkeleton';

import aiStudioError2x from '@Images/ai-studio-error-2x.webp';
import aiStudioError from '@Images/ai-studio-error.webp';

interface LoadingStep {
  loading_label: string;
  completed_label: string;
  error_label: string;
  completed: boolean;
  hasError: boolean;
  index?: number;
}

const defaultSteps: Record<keyof Loading, LoadingStep> = {
  title: {
    loading_label: __('Now generating course title...', 'tutor'),
    completed_label: __('Course title generated.', 'tutor'),
    error_label: __('Error generating course title.', 'tutor'),
    completed: false,
    hasError: false,
  },
  image: {
    loading_label: __('Now generating course featured image...', 'tutor'),
    completed_label: __('Course banner image generated.', 'tutor'),
    error_label: __('Error generating course banner image.', 'tutor'),
    completed: false,
    hasError: false,
  },
  description: {
    loading_label: __('Now generating course description...', 'tutor'),
    completed_label: __('Course description generated.', 'tutor'),
    error_label: __('Error generating course description.', 'tutor'),
    completed: false,
    hasError: false,
  },
  topic: {
    loading_label: __('Now generating topic names...', 'tutor'),
    completed_label: __('Course topics generated', 'tutor'),
    error_label: __('Error generating topics', 'tutor'),
    completed: false,
    hasError: false,
  },
  content: {
    loading_label: __('Now generating course contents...', 'tutor'),
    completed_label: __('Course contents generated', 'tutor'),
    error_label: __('Error generating course contents.', 'tutor'),
    completed: false,
    hasError: false,
  },
  quiz: {
    loading_label: __('Now generating quiz questions...', 'tutor'),
    completed_label: __('Quiz questions generated', 'tutor'),
    error_label: __('Error generating quiz questions.', 'tutor'),
    completed: false,
    hasError: false,
  },
};

const courseId = getCourseId();

const ContentGeneration = ({ onClose }: { onClose: () => void }) => {
  const [loadingSteps, setLoadingSteps] = useState(defaultSteps);
  const [isCreateNewCourse, setIsCreateNewCourse] = useState(false);
  const {
    contents,
    loading,
    pointer,
    currentContent,
    currentLoading,
    currentErrors,
    updateLoading,
    updateContents,
    setPointer,
    appendContent,
    removeContent,
    appendLoading,
    removeLoading,
    appendErrors,
    removeErrors,
    errors,
  } = useContentGenerationContext();
  const { startGeneration } = useGenerateCourseContent();
  const saveAIGeneratedCourseContentMutation = useSaveAIGeneratedCourseContentMutation();
  const formRef = useRef<HTMLFormElement>(null);
  const boxRef = useRef<HTMLDivElement>(null);

  const form = useFormWithGlobalError<{ prompt: string }>({ defaultValues: { prompt: '' } });
  const promptValue = form.watch('prompt');
  const { showToast } = useToast();

  useEffect(() => {
    setLoadingSteps((previous) => {
      const copy = { ...previous };
      const keys = getObjectKeys(copy);

      for (const key of keys) {
        const step = copy[key];
        const completed = !currentLoading[key];
        const hasError = !!currentErrors[key];

        copy[key] = {
          ...step,
          completed,
          hasError,
        };
      }

      return copy;
    });
    boxRef.current?.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }, [currentLoading, currentErrors]);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (isCreateNewCourse) {
      formRef.current?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      form.setFocus('prompt');
    }
  }, [isCreateNewCourse]);

  const isLoading = getObjectValues(currentLoading).some((item) => item);

  return (
    <div css={styles.container}>
      <div css={styles.wrapper}>
        <div css={styles.left}>
          <Show
            when={Object.values(loadingSteps).every((step) => !step.hasError) || currentContent.title}
            fallback={
              <div css={styles.errorWrapper}>
                <img
                  css={styles.errorImage}
                  src={aiStudioError}
                  srcSet={`${aiStudioError} 1x ${aiStudioError2x} 2x`}
                  alt={__('Ai Studio Error', 'tutor')}
                />

                <h5 css={styles.errorMessage}>
                  {__('An error occurred while generating course content. Please try again.', 'tutor')}
                </h5>
              </div>
            }
          >
            <div css={styles.title}>
              <Show when={!currentLoading.title} fallback={<TitleSkeleton />}>
                <SVGIcon name="book" width={32} height={32} />
                <h5 title={currentContent.title}>{currentContent.title}</h5>
              </Show>
            </div>

            <div css={styles.leftContentWrapper}>
              <Show when={!currentLoading.image} fallback={<ImageSkeleton />}>
                <div css={styles.imageWrapper}>
                  <img src={currentContent?.featured_image} alt="course banner" />
                </div>
              </Show>

              <Show when={!currentLoading.description} fallback={<DescriptionSkeleton />}>
                <div css={styles.section}>
                  <h5>{__('Course Info', 'tutor')}</h5>
                  <div css={styles.content}>
                    <div dangerouslySetInnerHTML={{ __html: currentContent.description }} />
                  </div>
                </div>
              </Show>
              <Show when={!currentLoading.topic} fallback={<ContentSkeleton />}>
                <div css={styles.section}>
                  <h5>{__('Course Content', 'tutor')}</h5>
                  <div css={styles.content}>
                    <ContentAccordion />
                  </div>
                </div>
              </Show>
            </div>
          </Show>
        </div>
        <div css={styles.right}>
          <Show when={contents.length > 1}>
            <div css={styles.navigator}>
              <Button
                variant="text"
                disabled={pointer === 0 || isLoading}
                onClick={() => setPointer((previous) => Math.max(0, previous - 1))}
              >
                <SVGIcon name="chevronLeft" width={20} height={20} />
              </Button>
              <div css={styles.navigatorContent}>
                <span>{pointer + 1}</span>
                <span>/</span>
                <span>{contents.length}</span>
              </div>
              <Button
                variant="text"
                disabled={pointer >= contents.length - 1 || isLoading}
                onClick={() => setPointer((previous) => Math.min(contents.length - 1, previous + 1))}
              >
                <SVGIcon name="chevronRight" width={20} height={20} />
              </Button>
            </div>
          </Show>
          <div css={styles.rightContents}>
            <For each={loading}>
              {(_, index) => {
                const isDeactivated = pointer !== index;
                const showButtons = pointer === index && !isCreateNewCourse;
                const content = contents[index];
                const isLoadingItem = getObjectValues(loading[index]).some((item) => item);
                const hasErrors = getObjectValues(errors[index]).every((error) => error);
                const itemErrors = errors[index];

                return (
                  <div
                    ref={index === pointer && !isCreateNewCourse ? boxRef : undefined}
                    css={styles.box({
                      deactivated: isDeactivated,
                      hasError: getObjectValues(errors[index]).some((error) => error),
                      isActive: pointer === index && !isCreateNewCourse,
                    })}
                    key={index}
                  >
                    <SVGIcon name="magicAiColorize" width={24} height={24} />
                    <div css={styles.boxContent}>
                      <h6>
                        {isLoadingItem
                          ? __('Generating course contents', 'tutor')
                          : hasErrors
                            ? __('Error generating course contents', 'tutor')
                            : __('Generated course contents', 'tutor')}
                      </h6>
                      <Show when={contents[index].prompt}>{(prompt) => <p css={styles.subtitle}>"{prompt}"</p>}</Show>
                      <div css={styles.items}>
                        <Show
                          when={isLoadingItem}
                          fallback={
                            <Show
                              when={!hasErrors}
                              fallback={
                                <For each={getObjectKeys(itemErrors)}>
                                  {(error, index) => (
                                    <div css={styles.item} key={index}>
                                      <SVGIcon name="crossCircle" width={24} height={24} data-check-icon data-error />
                                      {loadingSteps[error].error_label}
                                    </div>
                                  )}
                                </For>
                              }
                            >
                              <div css={styles.item}>
                                <SVGIcon
                                  name={content?.counts?.topics ? 'checkFilledWhite' : 'crossCircle'}
                                  width={24}
                                  height={24}
                                  data-check-icon
                                  data-error={content?.counts?.topics === 0}
                                />
                                {content?.counts?.topics
                                  ? sprintf(__('%d Topics in total', 'tutor'), content.counts?.topics)
                                  : loadingSteps.topic.error_label}
                              </div>
                              <div css={styles.item}>
                                <SVGIcon
                                  name={content?.counts?.lessons ? 'checkFilledWhite' : 'crossCircle'}
                                  width={24}
                                  height={24}
                                  data-check-icon
                                  data-error={content?.counts?.lessons === 0}
                                />
                                {content?.counts?.lessons
                                  ? sprintf(__('%d Lessons', 'tutor'), content.counts?.lessons)
                                  : __('Error generating lessons.', 'tutor')}
                              </div>
                              <div css={styles.item}>
                                <SVGIcon
                                  name={content?.counts?.quizzes ? 'checkFilledWhite' : 'crossCircle'}
                                  width={24}
                                  height={24}
                                  data-check-icon
                                  data-error={content?.counts?.quizzes === 0}
                                />
                                {content?.counts?.quizzes
                                  ? sprintf(__('%d Quizzes', 'tutor'), content.counts?.quizzes)
                                  : __('Error generating quizzes.', 'tutor')}
                              </div>
                              <div css={styles.item}>
                                <SVGIcon
                                  name={content?.counts?.assignments ? 'checkFilledWhite' : 'crossCircle'}
                                  width={24}
                                  height={24}
                                  data-check-icon
                                  data-error={content?.counts?.assignments === 0}
                                />
                                {content?.counts?.assignments
                                  ? sprintf(__('%d Assignments', 'tutor'), content.counts?.assignments)
                                  : __('Error generating assignments.', 'tutor')}
                              </div>
                            </Show>
                          }
                        >
                          <For each={getObjectKeys(loadingSteps)}>
                            {(stepKey, index) => {
                              const step = loadingSteps[stepKey];

                              return (
                                <div css={styles.item} key={index}>
                                  <Show when={isDeactivated}>
                                    <SVGIcon
                                      name={step.hasError ? 'crossCircle' : 'checkFilledWhite'}
                                      width={24}
                                      height={24}
                                      data-check-icon
                                    />
                                    {step.completed_label}
                                  </Show>
                                  <Show when={!isDeactivated}>
                                    <Show
                                      when={step.completed}
                                      fallback={
                                        <>
                                          <GradientLoadingSpinner />
                                          {step.loading_label}
                                        </>
                                      }
                                    >
                                      <SVGIcon
                                        name={step.hasError ? 'crossCircle' : 'checkFilledWhite'}
                                        width={24}
                                        height={24}
                                        data-check-icon
                                      />
                                      {step.completed_label}
                                    </Show>
                                  </Show>
                                </div>
                              );
                            }}
                          </For>
                        </Show>
                        <button
                          type="button"
                          css={styles.overlayButton}
                          onClick={() => setPointer(index)}
                          disabled={isLoading || pointer === index}
                        />
                      </div>

                      <div css={styles.boxFooter}>
                        <Show when={showButtons}>
                          <MagicButton
                            variant="primary_outline"
                            disabled={isLoading}
                            onClick={() => {
                              setIsCreateNewCourse(true);
                            }}
                          >
                            <SVGIcon name="magicWand" width={24} height={24} />
                            {__('Generate a new course', 'tutor')}
                          </MagicButton>
                        </Show>

                        <MagicButton
                          variant="outline"
                          disabled={isLoading}
                          onClick={() => {
                            setPointer(loading.length);
                            appendLoading();
                            appendContent();
                            appendErrors();
                            startGeneration(contents[index].prompt, loading.length);
                          }}
                        >
                          <SVGIcon name="tryAgain" width={24} height={24} />
                          {__('Regenerate course', 'tutor')}
                        </MagicButton>
                      </div>
                    </div>
                  </div>
                );
              }}
            </For>

            <Show when={isCreateNewCourse}>
              <div
                css={styles.box({
                  deactivated: true,
                  hasError: false,
                  isActive: true,
                })}
              >
                <form
                  ref={formRef}
                  css={styles.regenerateForm}
                  onSubmit={form.handleSubmit((values) => {
                    setIsCreateNewCourse(false);
                    setPointer(loading.length);
                    appendLoading();
                    appendContent();
                    appendErrors();
                    startGeneration(values.prompt, loading.length);
                    form.reset();
                  })}
                >
                  <Controller
                    control={form.control}
                    name="prompt"
                    render={(props) => (
                      <FormTextareaInput
                        {...props}
                        isMagicAi
                        placeholder={__('Type your desired course topic. e.g. Learning piano, Cooking 101..', 'tutor')}
                        rows={4}
                      />
                    )}
                  />
                  <div css={styles.formButtons}>
                    <MagicButton
                      variant="primary_outline"
                      disabled={isLoading}
                      onClick={() => {
                        setIsCreateNewCourse(false);
                        form.reset();
                      }}
                    >
                      {__('Cancel', 'tutor')}
                    </MagicButton>
                    <MagicButton type="submit" disabled={isLoading || !promptValue}>
                      <SVGIcon name="magicWand" width={24} height={24} />
                      {__('Generate now', 'tutor')}
                    </MagicButton>
                  </div>
                </form>
              </div>
            </Show>
          </div>

          <div css={styles.rightFooter}>
            <MagicButton variant="primary_outline" onClick={onClose} disabled={isLoading}>
              {__('Cancel', 'tutor')}
            </MagicButton>
            <MagicButton
              variant="primary"
              disabled={isLoading || isCreateNewCourse || !contents[pointer].title}
              onClick={() => {
                saveAIGeneratedCourseContentMutation.mutate({
                  course_id: courseId,
                  payload: JSON.stringify(currentContent),
                });
                onClose();
                showToast({ type: 'success', message: __('Course content stored into a local file.', 'tutor') });
              }}
            >
              {__('Append the course', 'tutor')}
            </MagicButton>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ContentGeneration;

const styles = {
  container: css`
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		display: flex;
    justify-content: center;
    align-items: end;
	`,
  navigator: css`
		display: flex;
		align-items: center;
		margin-left: -${spacing[16]};
		margin-bottom: ${spacing[16]};
	`,
  navigatorContent: css`
		display: flex;
		align-items: center;
		gap: ${spacing[4]};
		
		span {
			${typography.caption()};
		}

		span:first-of-type {
			color: ${colorTokens.text.primary};
		}
	`,
  wrapper: css`
		display: flex;
		gap: ${spacing[28]};
		height: calc(100vh - ${spacing[56]});
		width: 1300px;
		${Breakpoint.smallTablet} {
			width: 90%;
			gap: ${spacing[16]};
		}
	`,
  regenerateForm: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
		width: 100%;

		button {
			width: auto;
		}
	`,
  formButtons: css`
		display: flex;
		width: 100%;
		justify-content: end;
		align-items: center;
		gap: ${spacing[16]};
	`,
  leftContentWrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
		padding-inline: ${spacing[40]};
		margin-top: ${spacing[8]};
	`,
  box: ({ deactivated, hasError, isActive }: { deactivated: boolean; hasError: boolean; isActive: boolean }) => css`
		width: 100%;
		border-radius: ${borderRadius[8]};
		border: 1px solid ${hasError ? colorTokens.stroke.danger : colorTokens.bg.brand};
		padding: ${spacing[16]} ${spacing[12]};
		display: flex;
		gap: ${spacing[12]};
		transition: border 0.3s ease;
		
		svg {
			flex-shrink: 0;
		}

		${
      deactivated &&
      css`
			[data-check-icon] {
				color: ${colorTokens.icon.disable.muted} !important;
			}
		`
    }

		${
      !deactivated &&
      css`
			  border-color: ${hasError ? colorTokens.stroke.danger : colorTokens.bg.brand};
			`
    }

    ${
      isActive &&
      css`
        border-color: ${colorTokens.stroke.brand};
      `
    }

		:hover {
			border-color: ${hasError ? colorTokens.stroke.danger : colorTokens.stroke.brand};
      background-color: ${!isActive && colorTokens.background.hover};
		}
	`,
  boxFooter: css`
		display: flex;
		align-items: center;
		gap: ${spacing[16]};
		justify-content: end;

		button {
			width: auto;
		}
	`,
  rightContents: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
		overflow-y: auto;
		height: 100%;
    padding-right: ${spacing[20]};
    ${styleUtils.overflowYAuto};
	`,
  rightFooter: css`
		margin-top: auto;
		padding-top: ${spacing[16]};
    padding-right: ${spacing[20]};
		display: flex;
		align-items: center;
		justify-content: center;
		gap: ${spacing[12]};
	`,
  subtitle: css`
		${typography.caption()};
		color: ${colorTokens.text.title};
	`,
  boxContent: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};
		width: 100%;

		h6 {
			${typography.body('medium')};
			color: ${colorTokens.color.black.main};
		}

		p {
			${typography.caption('medium')};
			color: ${colorTokens.text.title};
		}
	`,
  items: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[4]};
		position: relative;

    [data-error] {
      color: ${colorTokens.icon.error}
    }
	`,
  item: css`
		display: flex;
		align-items: center;
		gap: ${spacing[8]};
		${typography.caption()};
		color: ${colorTokens.text.title};
		${styleUtils.textEllipsis};

		svg {
			color: ${colorTokens.stroke.success.fill70};

      [data-error='true'] {
        color: ${colorTokens.icon.error};
      }
		}
	`,
  section: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};

		& > h5 {
			${typography.heading6('medium')};
			color: ${colorTokens.text.primary};
			height: 42px;
			border-bottom: 1px solid ${colorTokens.stroke.border};
		}
	`,
  content: css`
		${typography.caption()};
		color: ${colorTokens.text.hints};
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
		
		h6 {
			${typography.caption()};
			color: ${colorTokens.text.primary};
		}
	`,
  left: css`
		width: 792px;
		background-color: ${colorTokens.background.white};
		border-radius: ${borderRadius[12]} ${borderRadius[12]} 0 0;
		display: flex;
		flex-direction: column;
		gap: ${spacing[16]};
		overflow-y: auto;
		padding-bottom: ${spacing[32]};

		${Breakpoint.smallTablet} {
			width: 80%;
		}
	`,
  right: css`
		width: 480px;
		height: 100%;
		background-color: ${colorTokens.background.white};
		border-radius: ${borderRadius[12]} ${borderRadius[12]} 0 0;
		padding: ${spacing[24]} ${spacing[0]} ${spacing[24]} ${spacing[20]};
    display: flex;
    flex-direction: column;
    justify-content: space-between;

		${Breakpoint.smallTablet} {
			width: 20%;
		}
	`,
  title: css`
		display: flex;
		align-items: center;
		gap: ${spacing[8]};
		color: ${colorTokens.icon.default};
		z-index: ${zIndex.header};
		min-height: 40px;	
		padding: ${spacing[40]} ${spacing[40]} ${spacing[16]} ${spacing[40]};	
		background-color: ${colorTokens.background.white};

    svg {
      flex-shrink: 0;
      color: ${colorTokens.text.ai.purple};
    }

		& > h5 {
			${typography.heading5('medium')};
			${styleUtils.textEllipsis};
			color: ${colorTokens.text.ai.purple};
		}
	`,
  imageWrapper: css`
		width: 100%;
		height: 390px;
		border-radius: ${borderRadius[10]};
		overflow: hidden;
		position: relative;
		flex-shrink: 0;

		img {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
	`,
  errorWrapper: css`
    ${styleUtils.flexCenter('column')};
    height: 100%;
    color: ${colorTokens.icon.error};
    text-align: center;
    gap: ${spacing[16]};
  `,
  errorImage: css`
    height: 300px;
    width: 100%;
    object-fit: cover;
    object-position: center;
  `,
  errorMessage: css`
    ${typography.heading5('medium')};
    color: ${colorTokens.text.error};
    margin-inline: ${spacing[96]};
  `,
  overlayButton: css`
    ${styleUtils.resetButton};
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;

    :disabled {
      cursor: default;
    }
  `,
};
