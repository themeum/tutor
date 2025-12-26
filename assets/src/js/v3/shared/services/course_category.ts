import { keepPreviousData, useQuery } from '@tanstack/react-query';
import { wpAjaxInstance } from '../utils/api';
import { type PaginatedParams, type PaginatedResult } from '../utils/types';
import endpoints from '../utils/endpoints';

export interface Category {
  id: number;
  title: string;
  image: string;
  total_courses: number;
}

export interface Course {
  id: number;
  title: string;
  image: '';
  author: string;
  regular_price: string;
  sale_price: string | null;
  plan_start_price?: string;
}

interface GetCourseCategoryParam extends PaginatedParams {
  applies_to: 'specific_courses' | 'specific_bundles' | 'specific_category';
}

const getCourseCategoryList = (params: GetCourseCategoryParam) => {
  return wpAjaxInstance.get<PaginatedResult<Course | Category>>(endpoints.COUPON_APPLIES_TO, {
    params: {
      ...params,
    },
  });
};

export const useCourseCategoryQuery = (params: GetCourseCategoryParam) => {
  return useQuery({
    queryKey: ['CourseCategory', params],
    placeholderData: keepPreviousData,
    queryFn: () => {
      return getCourseCategoryList(params).then((res) => {
        return res.data;
      });
    },
  });
};
