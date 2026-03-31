import { type CourseTopicWithCollapse } from '@CourseBuilderPages/Curriculum';
import { type Content } from '@CourseBuilderServices/curriculum';
import { type AnimateLayoutChanges, defaultAnimateLayoutChanges, useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import TopicContent from './TopicContent';

interface TopicContentWrapperProps {
  topic: CourseTopicWithCollapse;
  content: Content;
}

const animateLayoutChanges: AnimateLayoutChanges = (args) =>
  defaultAnimateLayoutChanges({ ...args, wasDragging: true });

const TopicContentWrapper = ({ topic, content }: TopicContentWrapperProps) => {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: content.ID,
    data: {
      type: 'content',
    },
    animateLayoutChanges,
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  };

  return (
    <div {...attributes} ref={setNodeRef} style={style}>
      <TopicContent
        type={content.post_type}
        topic={topic}
        listeners={listeners}
        isDragging={isDragging}
        content={{
          id: content.ID,
          title: content.post_title,
          total_question: content.total_question || 0,
        }}
      />
    </div>
  );
};

export default TopicContentWrapper;
