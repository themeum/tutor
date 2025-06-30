import { useQuery } from '@tanstack/react-query';

import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type CollectionResponse, type ContentBankContents } from '@TutorShared/utils/types';
import { __ } from '@wordpress/i18n';

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
    placeholderData: (previousData) => previousData,
    enabled: !!params.page && !!params.per_page,
  });
};

interface ContentBankContentsParams {
  collection_id: number | null;
  page?: string;
  search?: string;
  order?: string;
  content_types?: ('lesson' | 'assignment' | 'question')[];
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
  'cb-lesson': __('Lesson', 'tutor'),
  'cb-assignment': __('Assignment', 'tutor'),
  'cb-question': __('Question', 'tutor'),
} as const;
