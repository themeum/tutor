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

const getExportableContent = () => {
  return wpAjaxInstance.get(endpoints.GET_EXPORTABLE_CONTENT);
};

export const useExportableContentQuery = () => {
  return useQuery({
    queryKey: ['ExportableContent'],
    queryFn: () => getExportableContent(),
  });
};
