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
  const { updateContents, updateLoading, updateErrors } = useContentGenerationContext();
  const generateCourseTitleMutation = useGenerateCourseContentMutation('title');
  const generateCourseImageMutation = useGenerateCourseContentMutation('image');
  const generateCourseDescriptionMutation = useGenerateCourseContentMutation('description');
  const generateCourseTopicsMutation = useGenerateCourseTopicNamesMutation();
  const generateCourseTopicContentMutation = useGenerateCourseTopicContentMutation();
  const generateQuizQuestionsMutation = useGenerateQuizQuestionsMutation();

  const startGeneration = async (prompt: string, pointer?: number) => {
    const start = Date.now();

    try {
      updateLoading({ title: true, image: true, description: true, content: true, topic: true, quiz: true }, pointer);
      const response = await generateCourseTitleMutation.mutateAsync({ type: 'title', prompt });
      updateLoading({ title: false }, pointer);

      if (!response.data) {
        return;
      }

      const courseTitle = response.data;
      updateContents({ title: courseTitle, prompt }, pointer);

      try {
        const imageResponse = await generateCourseImageMutation.mutateAsync({ type: 'image', title: courseTitle });
        updateLoading({ image: false }, pointer);
        updateContents({ featured_image: imageResponse.data }, pointer);
      } catch (error) {
        updateLoading({ image: false }, pointer);
        updateErrors({ image: error as string }, pointer);
      }

      try {
        const descriptionResponse = await generateCourseDescriptionMutation.mutateAsync({
          type: 'description',
          title: courseTitle,
        });
        updateLoading({ description: false }, pointer);
        updateContents({ description: descriptionResponse.data }, pointer);
      } catch (error) {
        updateLoading({ description: false }, pointer);
        updateErrors({ description: error as string }, pointer);
      }

      try {
        const topicsResponse = await generateCourseTopicsMutation.mutateAsync({
          type: 'topic_names',
          title: courseTitle,
        });
        updateLoading({ topic: false }, pointer);
        const topics = topicsResponse.data.map(
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
            })
            .catch((error) => {
              updateErrors({ topic: error as string }, pointer);
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
                })
                .catch((error) => {
                  updateErrors({ quiz: error as string }, pointer);
                });
              quizPromises.push(promise);
            }
          }
        }

        await Promise.allSettled(quizPromises);
        updateLoading({ quiz: false }, pointer);
        const end = Date.now();
        updateContents({ topics, time: end - start }, pointer);
      } catch (error) {
        updateLoading({ topic: false, content: false, quiz: false }, pointer);
        updateErrors({ topic: error as string }, pointer);
      }
    } catch (error) {
      updateLoading(
        { title: false, image: false, content: false, description: false, quiz: false, topic: false },
        pointer,
      );
      updateErrors(
        {
          title: error as string,
          image: error as string,
          content: error as string,
          description: error as string,
          quiz: error as string,
          topic: error as string,
        },
        pointer,
      );
    }
  };

  return { startGeneration };
};
