import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useToast } from '@TutorShared/atoms/Toast';
import { wpAuthApiInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import type { ErrorResponse } from '@TutorShared/utils/form';
import { convertToErrorMessage } from '@TutorShared/utils/util';

export interface Category {
  id: number;
  name: string;
  slug?: string;
  parent: number;
}

export type CategoryWithChildren = Category & { children: CategoryWithChildren[] };

interface CreateCategoryPayload {
  name: string;
  parent?: number;
}

interface CreateCategoryResponse {
  data: Category;
}

const getCategoryList = (search?: string) => {
  return wpAuthApiInstance.get<Category[]>(
    endpoints.CATEGORIES,
    search
      ? {
          params: {
            per_page: 100,
            search,
          },
        }
      : {
          params: {
            per_page: 100,
          },
        },
  );
};

export const useCategoryListQuery = (search?: string) => {
  return useQuery({
    queryKey: ['CategoryList', search],
    queryFn: () => getCategoryList(search).then((res) => res.data),
  });
};

const createCategory = (payload: CreateCategoryPayload) => {
  return wpAuthApiInstance.post<CreateCategoryPayload, CreateCategoryResponse>(endpoints.CATEGORIES, payload);
};

export const useCreateCategoryMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: createCategory,
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: ['CategoryList'],
      });
    },
    onError: (error: ErrorResponse) => {
      // @TODO: Need to add proper type definition for wp rest api errors
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};
