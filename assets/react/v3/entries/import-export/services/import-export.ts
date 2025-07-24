import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { type ErrorResponse } from 'react-router-dom';

import { useToast } from '@TutorShared/atoms/Toast';

import { tutorConfig } from '@TutorShared/config/config';
import { type Course } from '@TutorShared/services/course';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type Collection, type TutorMutationResponse } from '@TutorShared/utils/types';
import { convertToErrorMessage } from '@TutorShared/utils/util';

const isTutorPro = !!tutorConfig.tutor_pro_url;

export interface ExportFormData {
  courses: boolean;
  'course-bundle': boolean;
  content_bank: boolean;
  settings: boolean;
  courses__ids: number[];
  'course-bundle__ids': number[];
  content_bank__ids: number[];
  courses__lesson: boolean;
  courses__tutor_quiz: boolean;
  courses__tutor_assignments: boolean;
  keep_media_files: boolean;
}

export interface BulkSelectionFormData {
  courses: Course[];
  'course-bundle': Course[];
  content_bank: Collection[];
}

export const defaultExportFormData: ExportFormData = {
  courses: false,
  'course-bundle': false,
  content_bank: false,
  settings: false,
  courses__ids: [],
  'course-bundle__ids': [],
  content_bank__ids: [],
  courses__lesson: true,
  courses__tutor_quiz: true,
  courses__tutor_assignments: true,
  keep_media_files: false,
};

export const convertExportFormDataToPayload = ({
  data,
  exportableContent,
}: {
  data: ExportFormData;
  exportableContent: ExportableContent[];
}): ExportContentPayload => {
  const payload: ExportContentPayload = {
    export_contents: [],
    keep_media_files: data.keep_media_files ? '1' : '0',
  };

  const isContentInExportableContent = (contentType: ExportableContentType): boolean => {
    return exportableContent.some((content) => content.key === contentType);
  };
  const isSubContentInExportableContent = (
    contentType: ExportableContentType,
    subContentType: ExportableCourseContentType,
  ): boolean => {
    return exportableContent.some(
      (content) =>
        content.key === contentType && content.contents?.some((subContent) => subContent.key === subContentType),
    );
  };

  // Get all unique content type prefixes
  const contentTypes = new Set<string>();

  // Add direct content types (those without '__')
  Object.keys(data).forEach((key) => {
    if (
      !key.includes('__') &&
      data[key as keyof ExportFormData] &&
      key !== 'keep_media_files' &&
      isContentInExportableContent(key as ExportableContentType)
    ) {
      contentTypes.add(key);
    }
  });

  // Add prefixes from keys with '__'
  Object.keys(data).forEach((key) => {
    if (key.includes('__')) {
      const prefix = key.split('__')[0];
      if (data[prefix as keyof ExportFormData]) {
        contentTypes.add(prefix);
      }
    }
  });

  // Process each content type
  contentTypes.forEach((contentType) => {
    const contentItem: ExportContentItem = {
      type: contentType as ExportableContentType,
    };

    // Process ids if they exist
    const idsKey = `${contentType}__ids` as keyof ExportFormData;
    if (data[idsKey] && Array.isArray(data[idsKey]) && (data[idsKey] as number[]).length > 0) {
      contentItem.ids = data[idsKey] as number[];
    }

    // Process sub_contents
    const subContents: Array<ExportableCourseContentType> = [];

    Object.entries(data).forEach(([key, value]) => {
      if (key.startsWith(`${contentType}__`) && value === true) {
        const suffix = key.split('__')[1];
        if (
          suffix &&
          suffix !== 'ids' &&
          suffix !== 'keep_media_files' &&
          isSubContentInExportableContent(contentType as ExportableContentType, suffix as ExportableCourseContentType)
        ) {
          subContents.push(suffix as ExportableCourseContentType);
        }
      }
    });

    if (subContents.length > 0) {
      contentItem.sub_contents = subContents;
    }

    payload.export_contents?.push(contentItem);
  });

  // If no contents were added, set export_contents to undefined
  if (!payload.export_contents?.length) {
    payload.export_contents = undefined;
  }

  return payload;
};

export type ImportExportModalState = 'initial' | 'progress' | 'success' | 'error';

export type ExportableContentType = 'courses' | 'course-bundle' | 'content_bank' | 'settings' | 'keep_media_files';
export type ExportableCourseContentType = 'lesson' | 'tutor_assignments' | 'tutor_quiz' | 'attachment';

export interface ContentItem {
  label: string;
  key: ExportableCourseContentType | (string & {});
  count: number;
}

export interface ExportableContent {
  label: string;
  key: ExportableContentType;
  ids?: number[];
  count?: number;
  keep_media_files?: boolean;
  contents?: ContentItem[];
}

const getExportableContent = () => {
  return wpAjaxInstance.get<ExportableContent[]>(endpoints.GET_EXPORTABLE_CONTENT);
};

export const useExportableContentQuery = () => {
  const isTutorPro = !!tutorConfig.tutor_pro_url;

  return useQuery({
    queryKey: ['ExportableContent'],
    queryFn: () => getExportableContent().then((res) => res.data),
    enabled: isTutorPro,
  });
};

interface ExportContentItem {
  type: ExportableContentType;
  ids?: number[];
  sub_contents?: ExportableCourseContentType[];
}

export interface ExportContentPayload {
  export_contents?: ExportContentItem[];
  keep_media_files?: '0' | '1';
  job_id?: string | number; // need to send back the job id to get the status
}

interface ImportExportCompletedContentsItem {
  success: string[];
  failed: string[];
}

export interface ImportExportContentResponseBase {
  job_id: string;
  job_progress: number;
  job_status: string;
  job_requirements: {
    type: string;
    ids: string[];
    sub_contents: string[];
  }[];
  completed_contents: {
    courses: ImportExportCompletedContentsItem;
    'course-bundle': ImportExportCompletedContentsItem;
    content_bank: ImportExportCompletedContentsItem;
    settings: boolean;
  };
}

export interface ExportContentResponse extends ImportExportContentResponseBase {
  exported_data: {
    schema_version: string;
    data: {
      content_type: string;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      data: Record<string, any>;
    }[];
  };
}
const exportContents = async (payload: ExportContentPayload) => {
  return wpAjaxInstance
    .post<ExportContentPayload, TutorMutationResponse<ExportContentResponse>>(
      isTutorPro ? endpoints.EXPORT_CONTENTS : endpoints.EXPORT_SETTINGS_FREE,
      payload.job_id
        ? { job_id: payload.job_id }
        : {
            export_contents: payload.export_contents,
            keep_media_files: payload.keep_media_files,
          },
    )
    .then((res) => res.data);
};

export const useExportContentsMutation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: exportContents,
    mutationKey: ['ExportContents'],
    onSuccess: (response) => {
      if (response.job_progress === 100) {
        queryClient.invalidateQueries({
          queryKey: ['ImportExportHistory'],
        });
      }
    },
  });
};

interface ImportContentPayload {
  data?: File;
  collection_id?: number; // for content bank import
  job_id?: string | number; // need to send back the job id to get the status
}

interface ImportContentResponse extends ImportExportContentResponseBase {
  imported_data: [];
}

const importContents = async (payload: ImportContentPayload) => {
  return wpAjaxInstance
    .post<
      ImportContentPayload,
      TutorMutationResponse<ImportContentResponse>
    >(isTutorPro ? endpoints.IMPORT_CONTENTS : endpoints.IMPORT_SETTINGS_FREE, payload)
    .then((res) => res.data);
};

export const useImportContentsMutation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: importContents,
    mutationKey: ['ImportContents'],
    onSuccess: (response) => {
      if (response.job_progress === 100) {
        queryClient.invalidateQueries({
          queryKey: ['ImportExportHistory'],
        });
      }
    },
  });
};

export interface ImportExportHistory {
  option_id: string;
  option_name: string;
  option_value: {
    created_at: string;
    user_name: string;
    job_id: number;
    job_progress: number;
    job_status: string;
    job_requirements: {
      type: string;
      ids: string[];
    }[];
    exported_data?: unknown;
    imported_data?: ExportableContentType[];
    completed_contents?: {
      courses: ImportExportCompletedContentsItem;
      'course-bundle': ImportExportCompletedContentsItem;
      content_bank: ImportExportCompletedContentsItem;
      settings: boolean;
    };
    failed_course_ids?: [];
    failed_bundle_ids?: [];
  };
}

const getImportExportHistory = () => {
  return wpAjaxInstance.get<ImportExportHistory[]>(endpoints.GET_IMPORT_EXPORT_HISTORY).then((res) => res.data);
};

export const useImportExportHistoryQuery = () => {
  const isTutorPro = !!tutorConfig.tutor_pro_url;

  return useQuery({
    queryKey: ['ImportExportHistory'],
    queryFn: () => getImportExportHistory(),
    enabled: isTutorPro,
  });
};

const deleteHistoryItem = async (optionId: string) => {
  return wpAjaxInstance.post<{ option_id: string }, TutorMutationResponse<string>>(
    endpoints.DELETE_IMPORT_EXPORT_HISTORY,
    { option_id: optionId },
  );
};

export const useDeleteImportExportHistoryMutation = () => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: deleteHistoryItem,
    mutationKey: ['DeleteImportExportHistory'],
    onSuccess: (response) => {
      queryClient.invalidateQueries({
        queryKey: ['ImportExportHistory'],
      });
      showToast({
        message: response.message,
        type: 'success',
      });
    },
    onError: (error: ErrorResponse) => {
      showToast({
        message: convertToErrorMessage(error),
        type: 'danger',
      });
    },
  });
};
