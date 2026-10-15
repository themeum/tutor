import { useQuery } from '@tanstack/react-query';

import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type H5PContentResponse, type TopicContentType } from '@TutorShared/utils/types';

const getH5PLessonContents = (search: string) => {
  return wpAjaxInstance.post<H5PContentResponse>(endpoints.GET_H5P_LESSON_CONTENT, {
    search_filter: search,
  });
};

export const useGetH5PLessonContentsQuery = (search: string, contentType: TopicContentType) => {
  return useQuery({
    queryKey: ['H5PLessonContents', search],
    queryFn: () => getH5PLessonContents(search).then((response) => response.data),
    enabled: contentType === 'lesson',
  });
};

const getH5PQuizContents = (search: string) => {
  return wpAjaxInstance.post<H5PContentResponse>(endpoints.GET_H5P_QUIZ_CONTENT, {
    search_filter: search,
  });
};

export const useGetH5PQuizContentsQuery = (search: string, contentType: TopicContentType) => {
  return useQuery({
    queryKey: ['H5PQuizContents', search],
    queryFn: () => getH5PQuizContents(search).then((response) => response.data),
    enabled: contentType === 'tutor_h5p_quiz',
  });
};
