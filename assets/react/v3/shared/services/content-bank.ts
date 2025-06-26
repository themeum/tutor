import { useQuery } from '@tanstack/react-query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type CollectionResponse } from '@TutorShared/utils/types';

interface CollectionParams {
  search?: string;
  page?: number;
  per_page?: number;
}

export const getCollections = (params: CollectionParams) => {
  return wpAjaxInstance
    .get<CollectionResponse>(endpoints.GET_CONTENT_BANK_COLLECTIONS, { params })
    .then((response) => response.data);
};

export const useGetCollectionsPaginatedQuery = (params: CollectionParams) => {
  return useQuery({
    queryKey: ['ContentBankCollections', params],
    queryFn: () => getCollections({ ...params }),
    enabled: !!params.page && !!params.per_page,
  });
};
