import { useMutation, useQuery } from '@tanstack/react-query';
import { type ErrorResponse } from 'react-router-dom';

import { useToast } from '@TutorShared/atoms/Toast';

import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type WPUser } from '@TutorShared/utils/types';
import { convertToErrorMessage } from '@TutorShared/utils/util';

export interface ImportExportHistory {
  title: string;
  type: 'import' | 'export';
  isSetting?: boolean;
  isActive?: boolean;
  author: WPUser;
  date: string;
}

export interface ExportFormData {
  courses: boolean;
  'course-bundle': boolean;
  settings: boolean;
  courses__ids: number[];
  'course-bundle__ids': number[];
  courses__lesson: boolean;
  courses__tutor_quiz: boolean;
  courses__tutor_assignments: boolean;
  courses__attachments: boolean;
  keep_media_files: boolean;
}

export const defaultExportFormData: ExportFormData = {
  courses: false,
  'course-bundle': false,
  settings: false,
  courses__ids: [],
  'course-bundle__ids': [],
  courses__lesson: false,
  courses__tutor_quiz: false,
  courses__tutor_assignments: false,
  courses__attachments: false,
  keep_media_files: false,
};

export const convertExportFormDataToPayload = (data: ExportFormData): ExportContentPayload => {
  const payload: ExportContentPayload = {
    export_contents: [],
    keep_media_files: data.keep_media_files ? '1' : '0',
  };

  // Get all unique content type prefixes
  const contentTypes = new Set<string>();

  // Add direct content types (those without '__')
  Object.keys(data).forEach((key) => {
    if (!key.includes('__') && data[key as keyof ExportFormData] && key !== 'keep_media_files') {
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
        if (suffix && suffix !== 'ids' && suffix !== 'keep_media_files') {
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

export type ExportableContentType = 'courses' | 'course-bundle' | 'settings' | 'keep_media_files';
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
  return useQuery({
    queryKey: ['ExportableContent'],
    queryFn: () => getExportableContent().then((res) => res.data),
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

export interface ExportContentResponse {
  job_id: string;
  job_progress: number;
  job_status: string;
  job_requirements: {
    type: string;
    ids: string[];
    sub_contents: string[];
  }[];
  exported_data: {
    schema_version: string;
    data: {
      content_type: string;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      data: Record<string, any>;
    }[];
  };
  completed_contents: {
    courses: string[];
    'course-bundle': string[];
    settings: boolean;
  };
  failed_course_ids: [];
  failed_bundle_ids: [];
}
const exportContents = async (payload: ExportContentPayload) => {
  return wpAjaxInstance
    .post<ExportContentResponse>(
      endpoints.EXPORT_CONTENTS,
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
  const { showToast } = useToast();
  return useMutation({
    mutationFn: exportContents,
    mutationKey: ['ExportContents'],
    onError: (error: ErrorResponse) => {
      showToast({
        message: convertToErrorMessage(error),
        type: 'danger',
      });
    },
  });
};

interface ImportContentPayload {
  data: string;
  job_id?: string | number; // need to send back the job id to get the status
}

const importContents = (payload: ImportContentPayload) => {
  return wpAjaxInstance.post<ExportContentResponse>(endpoints.IMPORT_CONTENTS, payload);
};

export const useImportContentsMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: importContents,
    mutationKey: ['ImportContents'],
    onError: (error: ErrorResponse) => {
      showToast({
        message: convertToErrorMessage(error),
        type: 'danger',
      });
    },
  });
};
