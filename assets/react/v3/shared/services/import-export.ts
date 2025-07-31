import { useQuery } from '@tanstack/react-query';
import { tutorConfig } from '@TutorShared/config/config';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';

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

interface ExportableContentParams {
  course_ids?: number[];
}

const getExportableContent = ({ course_ids }: ExportableContentParams) => {
  return wpAjaxInstance.get<ExportableContent[]>(endpoints.GET_EXPORTABLE_CONTENT, {
    params: { course_ids },
  });
};

export const useExportableContentQuery = ({ course_ids }: ExportableContentParams) => {
  const isTutorPro = !!tutorConfig.tutor_pro_url;

  return useQuery({
    queryKey: ['ExportableContent', course_ids],
    queryFn: () => getExportableContent({ course_ids }).then((res) => res.data),
    enabled: isTutorPro,
  });
};
