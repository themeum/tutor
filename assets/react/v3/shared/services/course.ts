import { keepPreviousData, useMutation, useQuery } from '@tanstack/react-query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type PaginatedParams, type PaginatedResult, type TutorMutationResponse } from '@TutorShared/utils/types';

export interface Course {
  id: number;
  title: string;
  image: string;
  is_purchasable: boolean;
  regular_price: string;
  sale_price: string;
}

interface CourseListParams extends PaginatedParams {
  exclude: string[];
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
        exclude: params.exclude,
        limit: params.limit,
        offset: params.offset,
        filter: params.filter,
      }).then((res) => res.data),
    placeholderData: keepPreviousData,
    enabled: isEnabled,
  });
};

interface UnlinkPageBuilderPayload {
  courseId: number;
  builder: string;
}

const unlinkPageBuilder = ({ courseId, builder }: UnlinkPageBuilderPayload) => {
  return wpAjaxInstance.post<UnlinkPageBuilderPayload, TutorMutationResponse<null>>(
    endpoints.TUTOR_UNLINK_PAGE_BUILDER,
    {
      course_id: courseId,
      builder: builder,
    },
  );
};

export const useUnlinkPageBuilderMutation = () => {
  return useMutation({
    mutationFn: unlinkPageBuilder,
  });
};
