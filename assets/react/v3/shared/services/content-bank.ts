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
  content_type?: 'lesson' | 'assignment' | 'question';
}

const getContentBankContents = (params: ContentBankContentsParams) => {
  return wpAjaxInstance
    .get<ContentBankContents>(endpoints.GET_CONTENT_BANK_CONTENTS, {
      params,
    })
    .then((response) => {
      // Return mock data if no data exists
      if (!response.data || !response.data.data || response.data.data.length === 0) {
        return {
          collection: {
            ID: 0,
            post_title: 'Collection Placeholder',
            count_stats: {
              lesson: 0,
              assignment: 0,
              question: 0,
              total: 0,
            },
          },
          total_record: 3,
          current_page: 1,
          per_page: 10,
          total_page: 1,
          data: [
            {
              ID: 1,
              post_title: 'Lesson',
              post_content: '',
              post_name: null,
              post_date: '',
              post_parent: '',
              linked_courses: {
                total: 0,
                courses: [],
                more_text: '',
              },
              post_type: 'cb_lesson',
            },
            {
              ID: 2,
              post_title: 'Assignment',
              post_content: '',
              post_name: null,
              post_date: '',
              post_parent: '',
              linked_courses: {
                total: 0,
                courses: [],
                more_text: '',
              },
              post_type: 'cb_assignment',
            },
            {
              ID: 3,
              post_title: 'Question',
              post_author: '',
              post_content: '',
              post_name: null,
              post_date: '',
              post_parent: '',
              linked_courses: {
                total: 0,
                courses: [],
                more_text: '',
              },
              post_type: 'cb_question',
            },
          ],
        } as ContentBankContents;
      }
      return response.data;
    });
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
  cb_lesson: __('Lesson', 'tutor'),
  cb_assignment: __('Assignment', 'tutor'),
  cb_question: __('Question', 'tutor'),
} as const;
