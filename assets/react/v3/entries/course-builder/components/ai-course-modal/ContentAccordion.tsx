import SVGIcon from '@Atoms/SVGIcon';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import { noop } from '@Utils/util';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { type ReactNode, useEffect, useState } from 'react';
import { useContentGenerationContext } from './ContentGenerationContext';

interface AccordionContent {
  type: 'lesson' | 'assignment' | 'quiz';
  title: string;
}
interface AccordionItem {
  name: string;
  content: AccordionContent[];
  is_active: boolean;
}
const accordionContent: AccordionItem[] = [
  {
    name: 'Introduction',
    is_active: true,
    content: [
      {
        type: 'lesson',
        title: 'Business studies, often simply called business',
      },
      {
        type: 'quiz',
        title: 'Business studies, often simply called',
      },
      {
        type: 'assignment',
        title: 'Business studies, often simply called business, is a field of study that',
      },
    ],
  },
  {
    name: 'Sensors, Pixels and Resolution',
    is_active: false,
    content: [
      {
        type: 'lesson',
        title: 'Business studies, often simply called business',
      },
      {
        type: 'quiz',
        title: 'Business studies, often simply called',
      },
      {
        type: 'assignment',
        title: 'Business studies, often simply called business, is a field of study that',
      },
    ],
  },
  {
    name: 'Course outline',
    is_active: false,
    content: [
      {
        type: 'lesson',
        title: 'Business studies, often simply called business',
      },
      {
        type: 'quiz',
        title: 'Business studies, often simply called',
      },
      {
        type: 'assignment',
        title: 'Business studies, often simply called business, is a field of study that',
      },
    ],
  },
  {
    name: 'Hand on exercise',
    is_active: false,
    content: [
      {
        type: 'lesson',
        title: 'Business studies, often simply called business',
      },
      {
        type: 'quiz',
        title: 'Business studies, often simply called',
      },
      {
        type: 'assignment',
        title: 'Business studies, often simply called business, is a field of study that',
      },
    ],
  },
];

const icons: Record<'lesson' | 'quiz' | 'assignment', ReactNode> = {
  lesson: <SVGIcon name="lesson" width={24} height={24} data-lesson-icon />,
  assignment: <SVGIcon name="assignment" width={24} height={24} data-assignment-icon />,
  quiz: <SVGIcon name="quiz" width={24} height={24} data-quiz-icon />,
};

const AccordionItem = ({
  content,
  setIsActive,
}: { isActive: boolean; setIsActive: () => void; content: AccordionItem }) => {
  return (
    <div onClick={setIsActive} onKeyDown={noop} css={css`cursor: pointer;`}>
      <div css={styles.title}>
        <div css={styles.titleAndIcon}>
          <SVGIcon name="chevronDown" width={24} height={24} />
          <p>{content.name}</p>
        </div>
        <p>
          {content.content.length} {__('Contents', 'tutor')}
        </p>
      </div>
      <div css={styles.content(content.is_active)}>
        <For each={content.content}>
          {(item, idx) => {
            return (
              <div css={styles.contentItem} key={idx}>
                {icons[item.type]}
                <span>{item.title}</span>
              </div>
            );
          }}
        </For>
      </div>
    </div>
  );
};

const ContentAccordion = () => {
  const { content } = useContentGenerationContext();
  const [items, setItems] = useState<AccordionItem[]>(accordionContent);

  useEffect(() => {
    if (content.content) {
      // setItems(content.content.map((item) => ({ ...item, is_active: false })));
    }
  }, [content.content]);

  return (
    <div css={styles.wrapper}>
      <For each={items}>
        {(content, index) => {
          return (
            <AccordionItem
              content={content}
              key={index}
              isActive={index === 0}
              setIsActive={() => {
                setItems((previous) => {
                  const updated = [...previous].map((item) => ({ ...item, is_active: false }));
                  updated[index].is_active = true;
                  return updated;
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
		height: 0;
		opacity: 0;

		transition: height 0.3s ease-in-out, opacity 0.3s ease-in-out;

		${
      isActive &&
      css`
			height: auto;
			opacity: 1;
			margin-top: ${spacing[16]};
		`
    },
		
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
  titleAndIcon: css`
		display: flex;
		align-items: center;
		gap: ${spacing[8]};
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
