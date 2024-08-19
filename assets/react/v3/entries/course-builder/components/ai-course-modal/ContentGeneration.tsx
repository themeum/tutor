import { GradientLoadingSpinner } from '@Atoms/LoadingSpinner';
import MagicButton from '@Atoms/MagicButton';
import SVGIcon from '@Atoms/SVGIcon';
import { Breakpoint, borderRadius, colorTokens, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { useGenerateCourseContentMutation } from '@CourseBuilderServices/magic-ai';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import ContentAccordion from './ContentAccordion';
import { type Content, type Loading, useContentGenerationContext } from './ContentGenerationContext';
import ContentSkeleton from './loaders/ContentSkeleton';
import DescriptionSkeleton from './loaders/DescriptionSkeleton';
import ImageSkeleton from './loaders/ImageSkeleton';
import TitleSkeleton from './loaders/TitleSkeleton';

interface LoadingStep {
  type: keyof Loading;
  loading_label: string;
  completed_label: string;
  completed: boolean;
}

const defaultSteps: LoadingStep[] = [
  {
    type: 'title',
    loading_label: __('Generating course title...', 'tutor'),
    completed_label: __('Course title created.', 'tutor'),
    completed: false,
  },
  {
    type: 'image',
    loading_label: __('Generating course banner image...', 'tutor'),
    completed_label: __('Course banner image created.', 'tutor'),
    completed: false,
  },
  {
    type: 'description',
    loading_label: __('Generating course description...', 'tutor'),
    completed_label: __('Course description created.', 'tutor'),
    completed: false,
  },
  {
    type: 'content',
    loading_label: __('Generating course contents...', 'tutor'),
    completed_label: __('Course contents created.', 'tutor'),
    completed: false,
  },
] as const;

const ContentGeneration = ({ onClose }: { onClose: () => void }) => {
  const { content, loading, updateLoading, updateContent } = useContentGenerationContext();
  const generateCourseImageMutation = useGenerateCourseContentMutation('image');
  const generateCourseDescriptionMutation = useGenerateCourseContentMutation('description');
  const generateCourseContentMutation = useGenerateCourseContentMutation('content');
  const [loadingSteps, setLoadingSteps] = useState(defaultSteps);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (!content.title) {
      return;
    }

    generateCourseImageMutation.mutateAsync({ type: 'image', title: content.title }).then((response) => {
      updateLoading({ image: false });
      updateContent({ image: response.data });
    });

    generateCourseDescriptionMutation.mutateAsync({ type: 'description', title: content.title }).then((response) => {
      updateLoading({ description: false });
      updateContent({ description: response.data });
    });

    generateCourseContentMutation.mutateAsync({ type: 'content', title: content.title }).then((response) => {
      updateLoading({ content: false });
      updateContent({ content: response.data as unknown as Content[] });
    });
  }, [loading.title, content.title]);

  useEffect(() => {
    setLoadingSteps((previous) => {
      return previous.map((item) => {
        return { ...item, completed: !loading[item.type] };
      });
    });
  }, [loading]);

  return (
    <div css={styles.container}>
      <div css={styles.wrapper}>
        <div css={styles.left}>
          <div css={styles.title}>
            <Show when={!loading.title} fallback={<TitleSkeleton />}>
              <SVGIcon name="book" width={40} height={40} />
              <h5 title={content.title}>{content.title}</h5>
            </Show>
          </div>

          <div css={styles.leftContentWrapper}>
            <Show when={!loading.image} fallback={<ImageSkeleton />}>
              <div css={styles.imageWrapper}>
                <img src={content.image} alt="course banner" />
              </div>
            </Show>

            <Show when={!loading.description} fallback={<DescriptionSkeleton />}>
              <div css={styles.section}>
                <h5>{__('Course Info', 'tutor')}</h5>
                <div css={styles.content}>
                  <div dangerouslySetInnerHTML={{ __html: content.description }} />
                </div>
              </div>
            </Show>
            <Show when={!loading.content} fallback={<ContentSkeleton />}>
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
                  <For each={loadingSteps}>
                    {(step, index) => {
                      return (
                        <div css={styles.item}>
                          <Show
                            key={index}
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

                <Show when={loadingSteps.every((item) => item.completed)}>
                  <div css={styles.boxFooter}>
                    <MagicButton variant="primary_outline">
                      <SVGIcon name="tryAgain" width={24} height={24} />
                      Regenerate course
                    </MagicButton>
                    <MagicButton variant="primary_outline">
                      <SVGIcon name="magicWand" width={24} height={24} />
                      Make a little different
                    </MagicButton>
                  </div>
                </Show>
              </div>
            </div>
          </div>

          <div css={styles.rightFooter}>
            <MagicButton variant="primary_outline" onClick={onClose}>
              {__('Cancel', 'tutor')}
            </MagicButton>
            <MagicButton
              onClick={() => {
                alert('@TODO: will be implemented later.');
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
		display: grid;
		grid-template-columns: 24px auto;
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
		// position: sticky;
		// top: 0;
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
