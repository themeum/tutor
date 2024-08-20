import { GradientLoadingSpinner } from '@Atoms/LoadingSpinner';
import MagicButton from '@Atoms/MagicButton';
import SVGIcon from '@Atoms/SVGIcon';
import { useToast } from '@Atoms/Toast';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { Breakpoint, borderRadius, colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import {
  useGenerateCourseContentMutation,
  useGenerateCourseTopicContentMutation,
  useGenerateCourseTopicNamesMutation,
  useGenerateQuizQuestionsMutation,
  useSaveAIGeneratedCourseContentMutation,
} from '@CourseBuilderServices/magic-ai';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';
import { getObjectKeys, getObjectValues } from '@Utils/util';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import { Controller } from 'react-hook-form';
import ContentAccordion from './ContentAccordion';
import { type Loading, type Topic, useContentGenerationContext } from './ContentGenerationContext';
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
  const { currentContent, loading, updateLoading, updateContents, setPointer } = useContentGenerationContext();
  const params = new URLSearchParams(window.location.search);
  const courseId = Number(params.get('course_id'));

  const generateCourseImageMutation = useGenerateCourseContentMutation('image');
  const generateCourseDescriptionMutation = useGenerateCourseContentMutation('description');
  const generateCourseTopicsMutation = useGenerateCourseTopicNamesMutation();
  const generateCourseTopicContentMutation = useGenerateCourseTopicContentMutation();
  const saveAIGeneratedCourseContentMutation = useSaveAIGeneratedCourseContentMutation();
  const generateQuizQuestionsMutation = useGenerateQuizQuestionsMutation();
  const generateCourseTitleMutation = useGenerateCourseContentMutation('title');

  const form = useFormWithGlobalError<{ prompt: string }>({ defaultValues: { prompt: '' } });

  const { showToast } = useToast();

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (!currentContent.title) {
      return;
    }

    generateCourseImageMutation.mutateAsync({ type: 'image', title: currentContent.title }).then((response) => {
      updateLoading({ image: false });
      updateContents({ image: response.data });
    });

    generateCourseDescriptionMutation
      .mutateAsync({ type: 'description', title: currentContent.title })
      .then((response) => {
        updateLoading({ description: false });
        updateContents({ description: response.data });
      });

    generateCourseTopicsMutation
      .mutateAsync({ type: 'topic_names', title: currentContent.title })
      .then(async (response) => {
        updateLoading({ topic: false });
        const topics = response.data.map(
          (item) =>
            ({
              ...item,
              content: [],
              is_active: true,
            }) as Topic,
        );

        updateContents({ topics });

        const promises = topics.map((item, index) => {
          return generateCourseTopicContentMutation
            .mutateAsync({ title: currentContent.title, topic_name: item.name, index })
            .then((data) => {
              const { index: idx, topic_contents } = data.data;
              topics[idx].content ||= [];
              topics[idx].content.push(...topic_contents);
              updateContents({ topics });
            });
        });

        await Promise.allSettled(promises);
        updateLoading({ content: false });

        /** Generate quiz contents  */
        const quizPromises = [];

        for (let i = 0; i < topics.length; i++) {
          const topic = topics[i];
          for (let j = 0; j < topic.content.length; j++) {
            const quizContent = topic.content[j];
            if (quizContent.type === 'quiz') {
              const promise = generateQuizQuestionsMutation
                .mutateAsync({
                  title: currentContent.title,
                  topic_name: topic.name,
                  quiz_title: quizContent.title,
                })
                .then((response) => {
                  topics[i].content[j].questions ||= [];
                  topics[i].content[j].questions = response.data;
                });
              quizPromises.push(promise);
            }
          }
        }

        await Promise.allSettled(quizPromises);
        updateLoading({ quiz: false });
      });
  }, [loading.title, currentContent?.title]);

  useEffect(() => {
    setLoadingSteps((previous) => {
      const copy = { ...previous };
      const keys = getObjectKeys(loading);
      for (const key of keys) {
        copy[key].completed = !loading[key];
      }
      return copy;
    });
  }, [loading]);

  return (
    <div css={styles.container}>
      <div css={styles.wrapper}>
        <div css={styles.left}>
          <div css={styles.title}>
            <Show when={!loading.title} fallback={<TitleSkeleton />}>
              <SVGIcon name="book" width={40} height={40} />
              <h5 title={currentContent.title}>{currentContent.title}</h5>
            </Show>
          </div>

          <div css={styles.leftContentWrapper}>
            <Show when={!loading.image} fallback={<ImageSkeleton />}>
              <div css={styles.imageWrapper}>
                <img src={currentContent.image} alt="course banner" />
              </div>
            </Show>

            <Show when={!loading.description} fallback={<DescriptionSkeleton />}>
              <div css={styles.section}>
                <h5>{__('Course Info', 'tutor')}</h5>
                <div css={styles.content}>
                  <div dangerouslySetInnerHTML={{ __html: currentContent.description }} />
                </div>
              </div>
            </Show>
            <Show when={!loading.topic} fallback={<ContentSkeleton />}>
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
          <div css={styles.rightContents}>
            <div css={styles.box({ deactivated: false })}>
              <SVGIcon name="magicAiColorize" width={24} height={24} />
              <div css={styles.boxContent}>
                <h6>{__('Generating course content', 'tutor')}</h6>
                <div css={styles.items}>
                  <For each={getObjectKeys(loadingSteps)}>
                    {(stepKey, index) => {
                      const step = loadingSteps[stepKey];

                      return (
                        <div css={styles.item} key={index}>
                          <Show
                            when={step.completed}
                            fallback={
                              <>
                                <GradientLoadingSpinner />
                                {step.loading_label}
                              </>
                            }
                          >
                            <SVGIcon name="checkFilledWhite" width={24} height={24} />
                            {step.completed_label}
                          </Show>
                        </div>
                      );
                    }}
                  </For>
                </div>
                <Show when={getObjectValues(loading).every((item) => !item)}>
                  <div css={styles.boxFooter}>
                    <MagicButton variant="primary_outline">
                      <SVGIcon name="tryAgain" width={24} height={24} />
                      {__('Regenerate course', 'tutor')}
                    </MagicButton>
                    <MagicButton
                      variant="primary_outline"
                      onClick={() => {
                        setIsCreateNewCourse(true);
                      }}
                    >
                      <SVGIcon name="magicWand" width={24} height={24} />
                      {__('Create a new course', 'tutor')}
                    </MagicButton>
                  </div>
                </Show>
              </div>
            </div>

            <Show when={isCreateNewCourse}>
              <div css={styles.box({ deactivated: false })}>
                <form
                  css={styles.regenerateForm}
                  onSubmit={form.handleSubmit(async (values) => {
                    updateLoading({
                      title: true,
                      image: true,
                      description: true,
                      content: true,
                      topic: true,
                      quiz: true,
                    });
                    setPointer((previous) => previous + 1);
                    const response = await generateCourseTitleMutation.mutateAsync({
                      type: 'title',
                      prompt: values.prompt,
                    });
                    updateLoading({ title: false });

                    if (response.data) {
                      updateContents({ title: response.data });
                    }
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
                      onClick={() => {
                        setIsCreateNewCourse(false);
                        setPointer((previous) => previous - 1);
                      }}
                    >
                      {__('Cancel', 'tutor')}
                    </MagicButton>
                    <MagicButton type="submit">
                      <SVGIcon name="magicWand" width={24} height={24} />
                      {__('Create now', 'tutor')}
                    </MagicButton>
                  </div>
                </form>
              </div>
            </Show>
          </div>

          <div css={styles.rightFooter}>
            <MagicButton variant="primary_outline" onClick={onClose}>
              {__('Cancel', 'tutor')}
            </MagicButton>
            <MagicButton
              onClick={() => {
                setPointer((previous) => previous + 1);
                saveAIGeneratedCourseContentMutation.mutate({
                  course_id: courseId,
                  content: JSON.stringify(currentContent),
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

		svg {
			flex-shrink: 0;
		}

		${
      deactivated &&
      css`
			svg {
				color: ${colorTokens.icon.disable.muted} !important;
			}
		`
    }
	`,
  boxFooter: css`
		display: flex;
		align-items: center;
		gap: ${spacing[16]};
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
  boxContent: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[12]};

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
