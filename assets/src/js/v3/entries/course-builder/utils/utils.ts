import { type ID } from '@TutorShared/utils/types';

export const getCourseId = () => {
  const params = new URLSearchParams(window.location.search);
  const courseId = params.get('course_id');
  return Number(courseId);
};

export const getIdWithoutPrefix = (prefix: string, id: ID) => {
  return id.toString().replace(prefix, '');
};
