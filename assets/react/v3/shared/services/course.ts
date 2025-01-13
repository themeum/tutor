import { keepPreviousData, useQuery } from '@tanstack/react-query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type PaginatedParams, type PaginatedResult } from '@TutorShared/utils/types';

export interface Course {
  id: number;
  title: string;
  image: string;
  is_purchasable: boolean;
  regular_price: string;
  sale_price: string;
}

interface CourseListParams extends PaginatedParams {
  excludedIds: string[];
}

const getCourseList = (params: CourseListParams) => {
  return wpAjaxInstance.get<PaginatedResult<Course>>(endpoints.GET_COURSE_LIST, {
    params: params,
  });
};

export const useCourseListQuery = ({ params, isEnabled }: { params: CourseListParams; isEnabled: boolean }) => {
  return useQuery({
    queryKey: ['PrerequisiteCourses', params],
    queryFn: () =>
      getCourseList({
        excludedIds: params.excludedIds,
        limit: params.limit,
        offset: params.offset,
        filter: params.filter,
      }).then((res) => res.data),
    placeholderData: keepPreviousData,
    enabled: isEnabled,
  });
};
