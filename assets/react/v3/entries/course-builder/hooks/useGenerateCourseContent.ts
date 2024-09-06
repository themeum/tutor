import {
  type Topic,
  useContentGenerationContext,
} from '@CourseBuilderComponents/ai-course-modal/ContentGenerationContext';
import {
  useGenerateCourseContentMutation,
  useGenerateCourseTopicContentMutation,
  useGenerateCourseTopicNamesMutation,
  useGenerateQuizQuestionsMutation,
} from '@CourseBuilderServices/magic-ai';

export const useGenerateCourseContent = () => {
  const { updateContents, updateLoading } = useContentGenerationContext();
  const generateCourseTitleMutation = useGenerateCourseContentMutation('title');
  const generateCourseImageMutation = useGenerateCourseContentMutation('image');
  const generateCourseDescriptionMutation = useGenerateCourseContentMutation('description');
  const generateCourseTopicsMutation = useGenerateCourseTopicNamesMutation();
  const generateCourseTopicContentMutation = useGenerateCourseTopicContentMutation();
  const generateQuizQuestionsMutation = useGenerateQuizQuestionsMutation();

  const startGeneration = async (prompt: string, pointer?: number) => {
    const start = Date.now();

    updateLoading({ title: true, image: true, description: true, content: true, topic: true, quiz: true }, pointer);
    const response = await generateCourseTitleMutation.mutateAsync({ type: 'title', prompt });
    updateLoading({ title: false }, pointer);

    if (!response.data) {
      return;
    }

    const courseTitle = response.data;
    updateContents({ title: courseTitle, prompt }, pointer);

    generateCourseImageMutation.mutateAsync({ type: 'image', title: courseTitle }).then((response) => {
      updateLoading({ image: false }, pointer);
      updateContents({ featured_image: response.data }, pointer);
    });

    generateCourseDescriptionMutation.mutateAsync({ type: 'description', title: courseTitle }).then((response) => {
      updateLoading({ description: false }, pointer);
      updateContents({ description: response.data }, pointer);
    });

    generateCourseTopicsMutation.mutateAsync({ type: 'topic_names', title: courseTitle }).then(async (response) => {
      updateLoading({ topic: false }, pointer);
      const topics = response.data.map(
        (item) =>
          ({
            ...item,
            contents: [],
            is_active: true,
          }) as Topic,
      );

      updateContents({ topics }, pointer);

      const promises = topics.map((item, index) => {
        return generateCourseTopicContentMutation
          .mutateAsync({ title: courseTitle, topic_name: item.title, index })
          .then((data) => {
            const { index: idx, topic_contents } = data.data;
            topics[idx].contents ||= [];
            topics[idx].contents.push(...topic_contents);
            updateContents({ topics }, pointer);
          });
      });

      await Promise.allSettled(promises);
      updateLoading({ content: false }, pointer);

      /** Generate quiz contents  */
      const quizPromises = [];

      for (let i = 0; i < topics.length; i++) {
        const topic = topics[i];
        for (let j = 0; j < topic.contents.length; j++) {
          const quizContent = topic.contents[j];
          if (quizContent.type === 'quiz') {
            const promise = generateQuizQuestionsMutation
              .mutateAsync({
                title: courseTitle,
                topic_name: topic.title,
                quiz_title: quizContent.title,
              })
              .then((response) => {
                topics[i].contents[j].questions ||= [];
                topics[i].contents[j].questions = response.data;
              });
            quizPromises.push(promise);
          }
        }
      }

      await Promise.allSettled(quizPromises);
      updateLoading({ quiz: false }, pointer);
      const end = Date.now();
      updateContents({ topics, time: end - start }, pointer);
    });
  };

  return { startGeneration };
};
