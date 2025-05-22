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
  courses__keep_media_files: boolean;
  'course-bundle__keep_media_files': boolean;
  courses__lesson: boolean;
  courses__tutor_quiz: boolean;
  courses__tutor_assignments: boolean;
  courses__attachments: boolean;
}

export const defaultExportFormData: ExportFormData = {
  courses: false,
  'course-bundle': false,
  settings: false,
  courses__ids: [],
  'course-bundle__ids': [],
  courses__keep_media_files: false,
  'course-bundle__keep_media_files': false,
  courses__lesson: false,
  courses__tutor_quiz: false,
  courses__tutor_assignments: false,
  courses__attachments: false,
};

export const convertExportFormDataToPayload = (data: ExportFormData): ExportContentPayload => {
  const payload: ExportContentPayload = {
    export_contents: [],
  };

  // Get all unique prefixes before "__"
  const prefixes = new Set<string>();

  Object.keys(data).forEach((key) => {
    if (key.includes('__')) {
      prefixes.add(key.split('__')[0]);
    }
  });

  // Process each content type
  prefixes.forEach((prefix) => {
    // Skip processing if the main type is not enabled
    if (!data[prefix as keyof ExportFormData]) {
      return;
    }

    const contentItem: ExportContentItem = {
      type: prefix as ExportableContentType,
    };

    // Process ids
    const idsKey = `${prefix}__ids` as keyof ExportFormData;
    if (data[idsKey] && Array.isArray(data[idsKey]) && data[idsKey].length > 0) {
      contentItem.ids = data[idsKey] as number[];
    }

    // // Process keep_media_files
    // const keepMediaKey = `${prefix}__keep_media_files` as keyof ExportFormData;
    // if (data[keepMediaKey]) {
    //   contentItem.keep_media_files = true;
    // }

    // Process sub_contents
    const subContents: Array<ExportableCourseContentType> = [];

    Object.entries(data).forEach(([key, value]) => {
      if (key.startsWith(`${prefix}__`) && value === true) {
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

  // Special case for settings that doesn't follow the pattern
  if (data.settings) {
    payload.export_contents?.push({
      type: 'settings',
    });
  }

  // If no contents were added, set export_contents to undefined
  if (payload.export_contents?.length === 0) {
    payload.export_contents = undefined;
  }

  return payload;
};

export type ImportExportModalState = 'initial' | 'progress' | 'success' | 'error';

export type ExportableContentType = 'courses' | 'course-bundle' | 'settings';
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
  keep_media_files?: boolean;
}

export interface ExportContentPayload {
  export_contents?: ExportContentItem[];
  job_id?: string | number; // need to send back the job id to get the status
}

interface ExportContentResponse {
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
    bundles: string[];
    settings: boolean;
  };
}
const exportContents = async (payload: ExportContentPayload) => {
  return wpAjaxInstance
    .post<ExportContentResponse>(
      endpoints.EXPORT_CONTENTS,
      payload.job_id
        ? { job_id: payload.job_id }
        : {
            export_contents: payload.export_contents,
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
