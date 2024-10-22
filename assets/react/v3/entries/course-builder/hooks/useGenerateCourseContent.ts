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
  const { abortControllerRef, updateContents, updateLoading, updateErrors, updateAbortStatus } =
    useContentGenerationContext();
  const generateCourseTitleMutation = useGenerateCourseContentMutation('title');
  const generateCourseDescriptionMutation = useGenerateCourseContentMutation('description');
  const generateCourseTopicsMutation = useGenerateCourseTopicNamesMutation();
  const generateCourseTopicContentMutation = useGenerateCourseTopicContentMutation();
  const generateQuizQuestionsMutation = useGenerateQuizQuestionsMutation();

  const startGeneration = async (prompt: string, pointer?: number) => {
    const start = Date.now();

    if (!abortControllerRef.current) {
      abortControllerRef.current = new AbortController();
    }

    if (prompt.length) {
      updateContents(
        {
          prompt: prompt,
        },
        pointer,
      );
    }

    try {
      updateLoading({ title: true, description: true, content: true, topic: true, quiz: true }, pointer);
      const response = await generateCourseTitleMutation.mutateAsync({
        type: 'title',
        prompt,
        signal: abortControllerRef.current.signal,
      });
      updateLoading({ title: false }, pointer);

      if (!response.data) {
        return;
      }

      const courseTitle = response.data;
      updateContents({ title: courseTitle, prompt }, pointer);

      try {
        const descriptionResponse = await generateCourseDescriptionMutation.mutateAsync({
          type: 'description',
          title: courseTitle,
          signal: abortControllerRef.current.signal,
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
          signal: abortControllerRef.current.signal,
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
            .mutateAsync({
              title: courseTitle,
              topic_name: item.title,
              index,
              signal: abortControllerRef.current?.signal,
            })
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
                  signal: abortControllerRef.current?.signal,
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
      updateLoading({ title: false, content: false, description: false, quiz: false, topic: false }, pointer);
      updateErrors(
        {
          title: error as string,
          content: error as string,
          description: error as string,
          quiz: error as string,
          topic: error as string,
        },
        pointer,
      );
    }
  };

  const cancelGeneration = () => {
    if (abortControllerRef.current) {
      abortControllerRef.current.abort();
      abortControllerRef.current = null;
      updateLoading({ title: false, content: false, description: false, quiz: false, topic: false });
      updateAbortStatus(true);
    }
  };

  return { startGeneration, cancelGeneration };
};
