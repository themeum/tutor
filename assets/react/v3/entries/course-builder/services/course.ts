import { useToast } from '@Atoms/Toast';
import { Media } from '@Components/fields/FormImageInput';
import { Tag } from '@Services/tags';
import { User } from '@Services/users';
import { authApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { useMutation } from '@tanstack/react-query';
import { AxiosResponse } from 'axios';

const currentUser = window._tutorobject.current_user.data;

export const courseDefaultData: CourseFormData = {
  post_date: '',
  post_name: '',
  post_title: '',
  post_content: '',
  post_status: 'publish',
  post_password: '',
  post_author: {
    id: Number(currentUser.ID),
    name: currentUser.display_name,
    email: currentUser.user_email,
    avatar_url: '',
  },
  thumbnail_id: null,
  video: {
    source_type: '',
    source: '',
  },
  course_price_type: 'free',
  course_price: '',
  course_sale_price: '',
  course_categories: [],
  course_tags: [],
  course_instructors: [],
  enable_qna: false,
  is_public_course: false,
  course_level: 'beginner',
  maximum_student: null,
  enrollment_expiration: '',
};

export const convertCourseDataToPayload = (data: CourseFormData): CoursePayload => {
  return {
    action: 'tutor_create_course',
    post_date: data.post_date,
    post_title: data.post_title,
    post_name: data.post_name,
    post_content: data.post_content,
    post_status: data.post_password.length ? 'public' : data.post_status,
    post_password: data.post_password,
    post_author: data.post_author?.id ?? null,
    ...(data.video && {
      source_type: '',
      source: '',
    }),
    course_categories: data.course_categories,
    course_tags: data.course_tags.map((item) => item.id),
    thumbnail_id: data.thumbnail_id?.id ?? null,
    enable_qna: data.enable_qna ? 'yes' : 'no',
    is_public_course: data.is_public_course ? 'yes' : 'no',
    course_level: data.course_level,
    _tutor_course_settings: {
      maximum_students: Number(data.maximum_student),
      // enable_content_drip: data.enable_content_drip,
      // content_drip_type: data.content_drip_type,
    },
  };
};

export interface CourseFormData {
  post_date: string;
  post_title: string;
  post_name: string;
  post_content: string;
  post_status: string;
  post_password: string;
  post_author: User | null;
  thumbnail_id: Media | null;
  video: {
    source_type: string;
    source: string;
  };
  course_price_type: string;
  course_price: string;
  course_sale_price: string;
  course_categories: number[];
  course_tags: Tag[];
  course_instructors: User[];
  enable_qna: boolean;
  is_public_course: boolean;
  course_level: string;
  maximum_student: number | null;
  enrollment_expiration: string;
}

export interface CoursePayload {
  action: string;
  post_date: string;
  post_title: string;
  post_name: string;
  post_content: string;
  post_status: string;
  post_password: string;
  post_author: number | null;
  thumbnail_id: number | null;
  video?: {
    source_type: string;
    source: string;
  };
  course_price_type?: string;
  course_price?: string;
  course_sale_price?: string;
  course_categories: number[];
  course_tags: number[];
  course_instructors?: number[];
  enable_qna: string;
  is_public_course: string;
  course_level: string;
  _tutor_course_settings: {
    maximum_students: number;
    enable_content_drip?: number;
    content_drip_type?: string;
  };
}

interface CreateCourseResponse {
  data: number;
  message: string;
  status_code: number;
}

const createCourse = (payload: CoursePayload) => {
  return authApiInstance.post<CoursePayload, CreateCourseResponse>(endpoints.ADMIN_AJAX, payload);
};

export const useCreateCourseMutation = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: createCourse,
    onSuccess: (response) => {
      showToast({ type: 'success', message: response.message });
    },
    onError: (error: any) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};
