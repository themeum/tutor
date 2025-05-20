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
  courses: {
    isChecked: boolean;
    lessons: boolean;
    assignments: boolean;
    quizzes: boolean;
    attachments: boolean;
    keepMediaFiles: boolean;
  };
  bundles: {
    isChecked: boolean;
    keepMediaFiles: boolean;
  };
  settings: boolean;
}

export const defaultExportFormData: ExportFormData = {
  courses: {
    isChecked: false,
    lessons: false,
    assignments: false,
    quizzes: false,
    attachments: false,
    keepMediaFiles: false,
  },
  bundles: {
    isChecked: false,
    keepMediaFiles: false,
  },
  settings: false,
};

export const convertExportFormDataToPayload = (data: ExportFormData): ExportContentPayload => {
  const payload: ExportContentPayload = {
    export_contents: [],
  };

  if (data.courses.isChecked) {
    payload.export_contents?.push({
      type: 'courses',
      ids: [],
      sub_contents: [
        data.courses.lessons ? 'lesson' : undefined,
        data.courses.quizzes ? 'quiz' : undefined,
        data.courses.assignments ? 'tutor_assignments' : undefined,
        data.courses.attachments ? 'attachment' : undefined,
      ].filter(Boolean) as ('lesson' | 'tutor_assignments' | 'quiz' | 'attachment')[],
    });
  }

  if (data.bundles.isChecked) {
    payload.export_contents?.push({
      type: 'bundles',
      ids: [],
      sub_contents: [],
    });
  }

  if (data.settings) {
    payload.export_contents?.push({
      type: 'settings',
      ids: [],
      sub_contents: [],
    });
  }

  return payload;
};

export type ImportExportModalState = 'initial' | 'progress' | 'success' | 'error';

export interface ExportableContent {
  courses: ExportableSectionWithItems;
  bundles: ExportableSectionWithoutItems;
  settings: ExportableSectionWithoutItems;
}

interface ExportableSectionBase {
  label: string;
}

interface ExportableSectionWithItems extends ExportableSectionBase {
  contents: {
    lesson: string;
    tutor_quiz: string;
    tutor_assignments: string;
    attachments: string;
  };
  ids?: number[];
}

interface ExportableSectionWithoutItems extends ExportableSectionBase {
  contents: [];
  ids?: number[];
}

const getExportableContent = () => {
  return wpAjaxInstance.get<ExportableContent>(endpoints.GET_EXPORTABLE_CONTENT);
};

export const useExportableContentQuery = () => {
  return useQuery({
    queryKey: ['ExportableContent'],
    queryFn: () => getExportableContent().then((res) => res.data),
  });
};

interface ExportContentItem {
  type: 'courses' | 'bundles' | 'settings';
  ids?: number[];
  sub_contents?: ('lesson' | 'tutor_assignments' | 'quiz' | 'attachment')[];
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
