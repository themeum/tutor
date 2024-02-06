import { useToast } from '@Atoms/Toast';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { authWPApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';

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
  return authWPApiInstance.get<Category[]>(endpoints.CATEGORIES, {
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
  return authWPApiInstance.post<CreateCategoryPayload, CreateCategoryResponse>(endpoints.CATEGORIES, payload);
};

export const useCreateTagMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: createCategory,
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: ['CategoryList'],
      });
    },
    onError: (error: any) => {
      // @TODO: Need to add proper type definition for wp rest api errors
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};
