import { useQuery } from '@tanstack/react-query';
import { authWPApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';

export interface Instructor {
  id: number;
  name: string;
  email: string;
  avatar_urls: {
    24: string;
    48: string;
    96: string;
  };
}

const getInstructorList = (search: string, exclude?: number[]) => {
  const params: { [key: string]: number | string | string[] } = {
    context: 'edit',
    roles: ['tutor_instructor'],
    search: search,
  };

  exclude?.forEach((item, idx) => {
    params[`exclude[${idx}]`] = item;
  });

  return authWPApiInstance.get<Instructor[]>(endpoints.INSTRUCTORS, { params });
};

export const useInstructorListQuery = (search: string, exclude?: number[]) => {
  return useQuery({
    queryKey: ['InstructorList', search, exclude],
    queryFn: () => getInstructorList(search, exclude).then((res) => res.data),
  });
};
