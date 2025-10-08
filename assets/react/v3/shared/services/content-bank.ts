import { useInfiniteQuery, useQuery } from '@tanstack/react-query';

import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type CollectionResponse, type ContentBankContents, type ID } from '@TutorShared/utils/types';
import { __ } from '@wordpress/i18n';

interface CollectionParams {
  search?: string;
  page?: number;
  per_page?: number;
  hide_empty?: number;
  context?: 'topic' | 'quiz_builder';
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
    placeholderData: (previousData) => previousData,
    enabled: !!params.page && !!params.per_page,
  });
};

export const useGetCollectionsInfinityQuery = (
  params: CollectionParams & {
    isEnabled?: boolean;
  },
) => {
  return useInfiniteQuery({
    queryKey: ['ContentBankCollectionsInfinity', params],
    queryFn: ({ pageParam = 1 }) => {
      return getCollections({
        ...params,
        page: pageParam,
      });
    },
    getNextPageParam: (lastPage) =>
      lastPage.current_page < lastPage.total_page ? lastPage.current_page + 1 : undefined,
    initialPageParam: 1,
    enabled: params.isEnabled && !!params.per_page,
  });
};

interface ContentBankContentsParams {
  collection_id: number | null;
  per_page?: number;
  page?: string;
  search?: string;
  order?: string;
  content_types?: ('lesson' | 'assignment' | 'question')[];
  question_types?: string[];
  context?: 'quiz_builder';
  exclude?: ID[];
}

const getContentBankContents = (params: ContentBankContentsParams) => {
  return wpAjaxInstance
    .get<ContentBankContents>(endpoints.GET_CONTENT_BANK_CONTENTS, {
      params,
    })
    .then((response) => response.data);
};

export const useGetContentBankContents = (params: ContentBankContentsParams) => {
  return useQuery({
    queryFn: () => getContentBankContents(params),
    queryKey: ['ContentBankContents', params],
    placeholderData: (previousData) => previousData,
    enabled: !!params.collection_id,
  });
};

export const CONTENT_BANK_POST_TYPE_MAP = {
  'cb-lesson': __('Lesson', __TUTOR_TEXT_DOMAIN__),
  'cb-assignment': __('Assignment', __TUTOR_TEXT_DOMAIN__),
  'cb-question': __('Question', __TUTOR_TEXT_DOMAIN__),
} as const;
