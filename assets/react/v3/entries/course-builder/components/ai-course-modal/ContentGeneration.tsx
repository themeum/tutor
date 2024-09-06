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
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';
import { getObjectKeys, getObjectValues } from '@Utils/util';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useState, useTransition } from 'react';
import { Controller } from 'react-hook-form';
import { useGenerateCourseContent } from '../../hooks/useGenerateCourseContent';
import ContentAccordion from './ContentAccordion';
import { type Loading, useContentGenerationContext } from './ContentGenerationContext';
import ContentSkeleton from './loaders/ContentSkeleton';
import DescriptionSkeleton from './loaders/DescriptionSkeleton';
import ImageSkeleton from './loaders/ImageSkeleton';
import TitleSkeleton from './loaders/TitleSkeleton';

interface LoadingStep {
  loading_label: string;
  completed_label: string;
  completed: boolean;
  index?: number;
}

const defaultSteps: Record<keyof Loading, LoadingStep> = {
  title: {
    loading_label: __('Now generating course title...', 'tutor'),
    completed_label: __('Course title generated.', 'tutor'),
    completed: false,
  },
  image: {
    loading_label: __('Now generating course banner image...', 'tutor'),
    completed_label: __('Course banner image generated.', 'tutor'),
    completed: false,
  },
  description: {
    loading_label: __('Now generating course description...', 'tutor'),
    completed_label: __('Course description generated.', 'tutor'),
    completed: false,
  },
  topic: {
    loading_label: __('Now generating topic names...', 'tutor'),
    completed_label: __('Course topics generated', 'tutor'),
    completed: false,
  },
  content: {
    loading_label: __('Now generating course contents...', 'tutor'),
    completed_label: __('Course contents generated', 'tutor'),
    completed: false,
  },
  quiz: {
    loading_label: __('Now generating quiz questions...', 'tutor'),
    completed_label: __('Quiz questions generated', 'tutor'),
    completed: false,
  },
};

const ContentGeneration = ({ onClose }: { onClose: () => void }) => {
  const [loadingSteps, setLoadingSteps] = useState(defaultSteps);
  const [isCreateNewCourse, setIsCreateNewCourse] = useState(false);
  const {
    contents,
    loading,
    pointer,
    currentContent,
    currentLoading,
    updateLoading,
    updateContents,
    setPointer,
    appendContent,
    removeContent,
    appendLoading,
    removeLoading,
  } = useContentGenerationContext();
  const params = new URLSearchParams(window.location.search);
  const courseId = Number(params.get('course_id'));

  const { startGeneration } = useGenerateCourseContent();
  const saveAIGeneratedCourseContentMutation = useSaveAIGeneratedCourseContentMutation();

  const form = useFormWithGlobalError<{ prompt: string }>({ defaultValues: { prompt: '' } });
  const promptValue = form.watch('prompt');
  const { showToast } = useToast();
  const [isPending, startTransition] = useTransition();

  useEffect(() => {
    setLoadingSteps((previous) => {
      const copy = { ...previous };
      const keys = getObjectKeys(currentLoading);
      for (const key of keys) {
        copy[key].completed = !currentLoading[key];
      }
      return copy;
    });
  }, [currentLoading]);

  const isLoading = getObjectValues(currentLoading).some((item) => item);

  return (
    <div css={styles.container}>
      <div css={styles.wrapper}>
        <div css={styles.left}>
          <div css={styles.title}>
            <Show when={!currentLoading.title} fallback={<TitleSkeleton />}>
              <SVGIcon name="book" width={40} height={40} />
              <h5 title={currentContent.title}>{currentContent.title}</h5>
            </Show>
          </div>

          <div css={styles.leftContentWrapper}>
            <Show when={!currentLoading.image} fallback={<ImageSkeleton />}>
              <div css={styles.imageWrapper}>
                <img src={currentContent.featured_image} alt="course banner" />
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
        </div>
        <div css={styles.right}>
          <Show when={contents.length > 1}>
            <div css={styles.navigator}>
              <Button
                variant="text"
                disabled={pointer === 0}
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
                disabled={pointer >= contents.length - 1}
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
                const showButtons = index === loading.length - 1;
                const content = contents[index];
                const isLoadingItem = getObjectValues(loading[index]).some((item) => item);

                return (
                  <div css={styles.box({ deactivated: isDeactivated })} key={index}>
                    <SVGIcon name="magicAiColorize" width={24} height={24} />
                    <div css={styles.boxContent}>
                      <h6>
                        {isLoadingItem
                          ? __('Generating course contents', 'tutor')
                          : __('Generated course contents', 'tutor')}
                      </h6>
                      <Show when={contents[index].prompt}>{(prompt) => <p css={styles.subtitle}>"{prompt}"</p>}</Show>
                      <div css={styles.items}>
                        <Show
                          when={isLoadingItem}
                          fallback={
                            <>
                              <div css={styles.item}>
                                <SVGIcon name="checkFilledWhite" width={24} height={24} data-check-icon />
                                {sprintf(__('%d Topics', 'tutor'), content.counts?.topics)}
                              </div>
                              <div css={styles.item}>
                                <SVGIcon name="checkFilledWhite" width={24} height={24} data-check-icon />
                                {sprintf(__('%d Lessons in total', 'tutor'), content.counts?.lessons)}
                              </div>
                              <div css={styles.item}>
                                <SVGIcon name="checkFilledWhite" width={24} height={24} data-check-icon />
                                {sprintf(__('%d Quizzes', 'tutor'), content.counts?.quizzes)}
                              </div>
                              <div css={styles.item}>
                                <SVGIcon name="checkFilledWhite" width={24} height={24} data-check-icon />
                                {sprintf(__('%d Assignments', 'tutor'), content.counts?.assignments)}
                              </div>
                            </>
                          }
                        >
                          <For each={getObjectKeys(loadingSteps)}>
                            {(stepKey, index) => {
                              const step = loadingSteps[stepKey];

                              return (
                                <div css={styles.item} key={index}>
                                  <Show when={isDeactivated}>
                                    <SVGIcon name="checkFilledWhite" width={24} height={24} data-check-icon />
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
                                      <SVGIcon name="checkFilledWhite" width={24} height={24} data-check-icon />
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
                          css={css`
														${styleUtils.resetButton};
														position: absolute;
														top: 0;
														left: 0;
														width: 100%;
														height: 100%;
													`}
                          onClick={() => setPointer(index)}
                          disabled={isLoadingItem}
                        />
                      </div>

                      <div css={styles.boxFooter}>
                        <Show when={showButtons}>
                          <MagicButton
                            variant="primary_outline"
                            disabled={isLoadingItem}
                            onClick={() => {
                              setIsCreateNewCourse(true);
                            }}
                          >
                            <SVGIcon name="magicWand" width={24} height={24} />
                            {__('Create a new course', 'tutor')}
                          </MagicButton>
                        </Show>

                        <MagicButton
                          variant="outline"
                          disabled={isLoadingItem}
                          onClick={() => {
                            setPointer(loading.length);
                            appendLoading();
                            appendContent();
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
              <div css={styles.box({ deactivated: true })}>
                <form
                  css={styles.regenerateForm}
                  onSubmit={form.handleSubmit((values) => {
                    setIsCreateNewCourse(false);
                    setPointer(loading.length);
                    appendLoading();
                    appendContent();
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
                      {__('Create now', 'tutor')}
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
              disabled={isLoading || isCreateNewCourse}
              onClick={() => {
                saveAIGeneratedCourseContentMutation.mutate({
                  course_id: courseId,
                  payload: JSON.stringify(currentContent),
                });
                onClose();
                showToast({ type: 'success', message: 'Course content stored into a local file.' });
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
  box: ({ deactivated }: { deactivated: boolean }) => css`
		width: 100%;
		border-radius: ${borderRadius[8]};
		border: 1px solid ${colorTokens.bg.brand};
		padding: ${spacing[16]} ${spacing[12]};
		display: flex;
		gap: ${spacing[12]};
		transition: border 0.3s ease;
		cursor: pointer;
		
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
			border-color: ${colorTokens.stroke.brand};
			`
    }

		&:hover {
				border-color: ${colorTokens.stroke.brand};
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
	`,
  rightFooter: css`
		margin-top: auto;
		padding-top: ${spacing[16]};
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
		padding: ${spacing[24]} ${spacing[20]};
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
};
