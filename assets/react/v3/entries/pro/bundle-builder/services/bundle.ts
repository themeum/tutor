import { useMutation, useQuery } from '@tanstack/react-query';
import { format, isBefore, parseISO } from 'date-fns';

import { useToast } from '@Atoms/Toast';
import { DateFormats } from '@Config/constants';
import { type WPMedia } from '@Hooks/useWpMedia';
import { type ErrorResponse } from '@Utils/form';
import { convertToErrorMessage } from '@Utils/util';

export interface Course {
  id: number;
  title: string;
  image: string;
  is_purchasable: boolean;
  regular_price: string;
  sale_price: string | null;
  total_course?: number;
  course_duration: string;
  last_updated: string;
  total_enrolled: number;
  plan_start_price?: string;
}

export interface Bundle {
  post_id: number;
  post_name: string;
  post_title: string;
  post_date: string;
  post_content: string;
  post_status: string;
  post_password: string;
  post_modified: string;
  course_benefits: string;
  thumbnail: WPMedia;
  ribbon: string;
  bundle_price: string;
  bundle_sale_price: string;
  course_selling_option: string;
  courses: Course[];
}

export interface BundleFormData {
  post_name: string;
  post_title: string;
  post_content: string;
  post_date: string;
  post_status: string;
  post_password: string;
  post_modified: string;
  course_benefits: string;
  visibility: string;
  thumbnail: WPMedia;
  ribbon: string;
  schedule_date: string;
  schedule_time: string;
  showScheduleForm: boolean;
  isScheduleEnabled: boolean;
  bundle_price: string;
  bundle_sale_price: string;
  course_selling_option: string;
  courses: Course[];
}

export interface BundlePayload {
  bundle_id?: number;
  post_name: string;
  post_title: string;
  post_content: string;
  post_date: string;
  post_status: string;
  post_password: string;
  post_modified: string;
  course_benefits: string;
  thumbnail_id: number;
  ribbon: string;
  bundle_price: string;
  bundle_sale_price: string;
  course_selling_option: string;
  courses: Course[];
}

export const defaultCourseBundleData: BundleFormData = {
  post_name: '',
  post_title: '',
  post_date: '',
  post_content: '',
  post_status: '',
  post_password: '',
  post_modified: '',
  course_benefits: '',
  visibility: 'public',
  thumbnail: {
    id: 0,
    url: '',
    title: '',
  },
  ribbon: '',
  schedule_date: '',
  schedule_time: '',
  showScheduleForm: false,
  isScheduleEnabled: false,
  bundle_price: '',
  bundle_sale_price: '',
  course_selling_option: 'both',
  courses: [
    {
      id: 1,
      title: 'Tutor LMS For Beginners Part II: Progress Your Webflow Skills With This Course',
      image: 'https://placehold.co/600x400',
      is_purchasable: true,
      regular_price: '100',
      sale_price: null,
      course_duration: '1 hour',
      last_updated: '2021-09-01',
      total_enrolled: 100,
    },
    {
      id: 2,
      title: 'Tutor LMS For Beginners Part II: Progress Your Webflow Skills With This Course',
      image: 'https://placehold.co/600x400',
      is_purchasable: true,
      regular_price: '200',
      sale_price: '150',
      course_duration: '2 hours',
      last_updated: '2021-09-02',
      total_enrolled: 200,
    },
    {
      id: 3,
      title: 'Course 3',
      image: 'https://placehold.co/600x400',
      is_purchasable: true,
      regular_price: '300',
      sale_price: null,
      course_duration: '3 hours',
      last_updated: '2021-09-03',
      total_enrolled: 300,
    },
  ],
};

export const convertBundleToFormData = (courseBundle: Bundle): BundleFormData => {
  return {
    post_name: courseBundle.post_name,
    post_title: courseBundle.post_title,
    post_date: courseBundle.post_date,
    post_content: courseBundle.post_content,
    post_status: courseBundle.post_status,
    post_password: courseBundle.post_password,
    post_modified: courseBundle.post_modified,
    course_benefits: courseBundle.course_benefits,
    visibility: (() => {
      if (courseBundle.post_password.length) {
        return 'password_protected';
      }
      if (courseBundle.post_status === 'private') {
        return 'private';
      }
      return 'publish';
    })(),
    thumbnail: courseBundle.thumbnail,
    ribbon: courseBundle.ribbon,
    isScheduleEnabled: isBefore(new Date(), new Date(courseBundle.post_date)),
    showScheduleForm: !isBefore(new Date(), new Date(courseBundle.post_date)),
    schedule_date: !isBefore(parseISO(courseBundle.post_date), new Date())
      ? format(parseISO(courseBundle.post_date), DateFormats.yearMonthDay)
      : '',
    schedule_time: !isBefore(parseISO(courseBundle.post_date), new Date())
      ? format(parseISO(courseBundle.post_date), DateFormats.hoursMinutes)
      : '',
    bundle_price: courseBundle.bundle_price,
    bundle_sale_price: courseBundle.bundle_sale_price,
    course_selling_option: courseBundle.course_selling_option,
    courses: courseBundle.courses,
  };
};

export const convertBundleFormDataToPayload = (formData: BundleFormData): BundlePayload => {
  return {
    bundle_id: 0,
    post_name: formData.post_name,
    post_title: formData.post_title,
    post_date: formData.post_date,
    post_content: formData.post_content,
    post_status: formData.visibility === 'private' ? 'private' : 'publish',
    post_password: formData.visibility === 'password_protected' ? formData.post_password : '',
    post_modified: formData.post_modified,
    course_benefits: formData.course_benefits,
    thumbnail_id: formData.thumbnail.id,
    ribbon: formData.ribbon,
    bundle_price: formData.bundle_price,
    bundle_sale_price: formData.bundle_sale_price,
    course_selling_option: formData.course_selling_option,
    courses: formData.courses,
  };
};

const getBundleDetails = async (id: number): Promise<Bundle> => {
  const response = await Promise.resolve({
    post_id: 0,
    post_name: 'course-bundle',
    post_title: 'Course Bundle',
    post_date: '2021-09-01',
    post_content: 'Course Bundle Content',
    post_status: 'draft',
    post_password: '',
    post_modified: '2021-09-01',
    course_benefits: 'Course Benefits',
    thumbnail: {
      id: 0,
      url: 'https://placehold.co/600x400',
      title: 'Course Bundle Thumbnail',
    },
    ribbon: 'Course Bundle Ribbon',
    bundle_price: '600',
    bundle_sale_price: '550',
    course_selling_option: 'both',
    courses: [
      {
        id: 1,
        title: 'Tutor LMS For Beginners Part II: Progress Your Webflow Skills With This Course',
        image: 'https://placehold.co/600x400',
        is_purchasable: true,
        regular_price: '$100',
        sale_price: null,
        course_duration: '1 hour',
        last_updated: '2021-09-01',
        total_enrolled: 100,
      },
      {
        id: 2,
        title: 'Tutor LMS For Beginners Part II: Progress Your Webflow Skills With This Course',
        image: 'https://placehold.co/600x400',
        is_purchasable: true,
        regular_price: '$200',
        sale_price: '$150',
        course_duration: '2 hours',
        last_updated: '2021-09-02',
        total_enrolled: 200,
      },
      {
        id: 3,
        title: 'Course 3',
        image: 'https://placehold.co/600x400',
        is_purchasable: true,
        regular_price: '$300',
        sale_price: null,
        course_duration: '3 hours',
        last_updated: '2021-09-03',
        total_enrolled: 300,
      },
    ],
  });
  return response;
};

export const useGetBundleDetails = (id: number) => {
  return useQuery({
    queryKey: ['CourseBundle', id],
    queryFn: () => getBundleDetails(id),
  });
};

const saveCourseBundle = async (payload: BundlePayload): Promise<Bundle> => {
  const response = await Promise.resolve({
    post_id: 0,
    post_name: 'course-bundle',
    post_title: 'Course Bundle',
    post_date: '2021-09-01',
    post_content: 'Course Bundle Content',
    post_status: 'draft',
    post_password: '',
    post_modified: '2021-09-01',
    course_benefits: 'Course Benefits',
    thumbnail: {
      id: 0,
      url: 'https://placehold.co/600x400',
      title: 'Course Bundle Thumbnail',
    },
    ribbon: 'Course Bundle Ribbon',
    bundle_price: '600',
    bundle_sale_price: '550',
    course_selling_option: 'both',
    courses: [
      {
        id: 1,
        title: 'Tutor LMS For Beginners Part II: Progress Your Webflow Skills With This Course',
        image: 'https://placehold.co/600x400',
        is_purchasable: true,
        regular_price: '$100',
        sale_price: null,
        course_duration: '1 hour',
        last_updated: '2021-09-01',
        total_enrolled: 100,
      },
      {
        id: 2,
        title: 'Tutor LMS For Beginners Part II: Progress Your Webflow Skills With This Course',
        image: 'https://placehold.co/600x400',
        is_purchasable: true,
        regular_price: '$200',
        sale_price: '$150',
        course_duration: '2 hours',
        last_updated: '2021-09-02',
        total_enrolled: 200,
      },
      {
        id: 3,
        title: 'Course 3',
        image: 'https://placehold.co/600x400',
        is_purchasable: true,
        regular_price: '$300',
        sale_price: null,
        course_duration: '3 hours',
        last_updated: '2021-09-03',
        total_enrolled: 300,
      },
    ],
  });
  return response;
};

export const useSaveCourseBundle = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: saveCourseBundle,
    onSuccess: (response) => {
      console.log('Course Bundle Saved:', response);
      showToast({
        message: 'Course Bundle Saved',
        type: 'success',
      });
    },
    onError: (error: ErrorResponse) => {
      console.error('Error Saving Course Bundle:', error);
      showToast({
        message: convertToErrorMessage(error),
        type: 'danger',
      });
    },
  });
};
