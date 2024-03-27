import { useToast } from '@Atoms/Toast';
import { wpAuthApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { ErrorResponse } from '@Utils/form';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';

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

const getCategoryList = () => {
  return wpAuthApiInstance.get<Category[]>(endpoints.CATEGORIES, {
    params: {
      per_page: 100,
    },
  });
};

export const useCategoryListQuery = () => {
  return useQuery({
    queryKey: ['CategoryList'],
    queryFn: () => getCategoryList().then((res) => res.data),
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
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};
