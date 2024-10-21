import { css } from '@emotion/react';
import { useSpring } from '@react-spring/web';
import { animated } from '@react-spring/web';
import { __ } from '@wordpress/i18n';
import { type ReactNode, useEffect, useState } from 'react';

import { GradientLoadingSpinner } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';

import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { noop } from '@Utils/util';

import { type Topic, useContentGenerationContext } from './ContentGenerationContext';
import TopicContentSkeleton from './loaders/TopicContentSkeleton';

interface AccordionItemData extends Topic {
  is_active: boolean;
}

const icons: Record<'lesson' | 'quiz' | 'assignment', ReactNode> = {
  lesson: <SVGIcon name="lesson" width={24} height={24} data-lesson-icon />,
  assignment: <SVGIcon name="assignment" width={24} height={24} data-assignment-icon />,
  quiz: <SVGIcon name="quiz" width={24} height={24} data-quiz-icon />,
};

const AccordionItem = ({
  data,
  setIsActive,
}: { isActive: boolean; setIsActive: () => void; data: AccordionItemData }) => {
  const { currentLoading } = useContentGenerationContext();
  const isLoading = currentLoading.content && data.contents.length === 0;

  const collapseAnimation = useSpring({
    maxHeight: data.is_active ? 300 : 0,
    opacity: data.is_active ? 1 : 0,
    config: {
      duration: 300,
      easing: (t) => t * (2 - t),
    },
  });

  return (
    <div
      onClick={data.contents.length ? setIsActive : noop}
      onKeyDown={noop}
      css={css`cursor: ${data.contents.length ? 'pointer' : 'default'};`}
    >
      <div css={styles.title}>
        <div
          css={styles.titleAndIcon({
            isActive: data.is_active,
          })}
        >
          <SVGIcon name="chevronDown" width={24} height={24} />
          <p>{data.title}</p>
        </div>
        <p>
          {data.contents.length} {__('Contents', 'tutor')}
        </p>
      </div>
      <Show
        when={data.contents.length > 0 && !isLoading}
        fallback={
          <div css={css`margin-top: ${spacing[16]};`}>
            <TopicContentSkeleton />
          </div>
        }
      >
        <animated.div style={collapseAnimation}>
          <div css={styles.content(data.is_active)}>
            <Show when={!isLoading}>
              <For each={data.contents}>
                {(item, idx) => {
                  return (
                    <div css={styles.contentItem} key={idx}>
                      {item.type === 'quiz' && !currentLoading.content && currentLoading.quiz && !item?.questions ? (
                        <GradientLoadingSpinner />
                      ) : (
                        icons[item.type]
                      )}

                      <span>{item.title}</span>
                    </div>
                  );
                }}
              </For>
            </Show>
          </div>
        </animated.div>
      </Show>
    </div>
  );
};

const ContentAccordion = () => {
  const { currentContent } = useContentGenerationContext();
  const [items, setItems] = useState<AccordionItemData[]>([]);

  useEffect(() => {
    if (currentContent.topics) {
      setItems(currentContent.topics.map((item) => ({ ...item, is_active: true }) as AccordionItemData));
    }
  }, [currentContent.topics]);

  return (
    <div css={styles.wrapper}>
      <For each={items}>
        {(content, index) => {
          return (
            <AccordionItem
              data={content}
              key={index}
              isActive={index === 0}
              setIsActive={() => {
                setItems((previous) => {
                  return previous.map((item, idx) => {
                    if (idx === index) {
                      return { ...item, is_active: !item.is_active };
                    }
                    return { ...item };
                  });
                });
              }}
            />
          );
        }}
      </For>
    </div>
  );
};

export default ContentAccordion;

const styles = {
  wrapper: css`
		display: flex;
		flex-direction: column;
		gap: ${spacing[24]};
	`,
  content: (isActive: boolean) => css`
		margin-left: ${spacing[32]};
		display: flex;
		flex-direction: column;
		gap: ${spacing[8]};

		${
      isActive &&
      css`
        margin-top: ${spacing[16]};
      `
    };
		
	`,
  contentItem: css`
		display: flex;
		align-items: center;
		gap: ${spacing[8]};

		[data-lesson-icon] {
			color: ${colorTokens.icon.subdued};
		}
		[data-assignment-icon] {
			color: ${colorTokens.icon.processing};
		}
		[data-quiz-icon] {
			color: ${colorTokens.design.warning};
		}
	`,
  titleAndIcon: ({
    isActive,
  }: {
    isActive: boolean;
  }) => css`
		display: flex;
		align-items: center;
		gap: ${spacing[8]};
    
    svg {
      transition: transform 0.3s ease-in-out;
    }

    ${
      isActive &&
      css`
        svg {
          transform: rotate(180deg);
        }
      `
    };
	`,
  title: css`
		display: flex;
		align-items: center;
		justify-content: space-between;

		p {
			${typography.body('medium')};
			color: ${colorTokens.text.subdued};
		}

		& > p {
			${typography.caption()};
			
		}
	`,
};
