import { useQuery } from '@tanstack/react-query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type WPUser } from '@TutorShared/utils/types';

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
  'courses[lesson]': boolean;
  'courses[tutor_quiz]': boolean;
  'courses[tutor_assignments]': boolean;
  'courses[attachments]': boolean;
  'courses[keepMediaFiles]': boolean;
  courseBundle: boolean;
  settings: boolean;
}

export type ImportExportModalState = 'initial' | 'progress' | 'success' | 'error';

export interface ExportableContent {
  courses: ExportableSectionWithItems;
  courseBundle: ExportableSectionWithoutItems;
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
  ids: number[];
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
