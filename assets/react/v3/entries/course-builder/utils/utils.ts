import type { CourseDetailsResponse, CourseFormData } from '@CourseBuilderServices/course';

// biome-ignore lint/suspicious/noExplicitAny: <explanation>
export const convertCourseDataToPayload = (data: CourseFormData): any => {
  return {
    post_date: data.post_date,
    post_title: data.post_title,
    post_name: data.post_name,
    post_content: data.post_content,
    post_status: data.post_password.length ? 'publish' : (data.post_status as 'publish' | 'private'),
    post_password: data.post_password,
    post_author: data.post_author?.id ?? null,
    ...(data.video && {
      source_type: '',
      source: '',
    }),
    'pricing[type]': data.course_price_type,
    'pricing[product_id]': data.course_product_id,

    course_price: data.course_price ?? 0,
    course_sale_price: data.course_sale_price ?? 0,

    course_categories: data.course_categories,
    course_tags: data.course_tags.map((item) => item.id),
    thumbnail_id: data.thumbnail?.id ?? null,
    enable_qna: data.enable_qna ? 'yes' : 'no',
    is_public_course: data.is_public_course ? 'yes' : 'no',
    course_level: data.course_level,
    'course_settings[maximum_students]': data.maximum_students,
    'course_settings[enrollment_expiry]': data.enrollment_expiry ?? '',
    'course_settings[enable_content_drip]': data.isContentDripEnabled ? 1 : 0,
    'course_settings[content_drip_type]': data.contentDripType,

    'additional_content[course_benefits]': data.course_benefits ?? '',
    'additional_content[course_target_audience]': data.course_target_audience ?? '',
    'additional_content[course_duration][hours]': data.course_duration_hours ?? 0,
    'additional_content[course_duration][minutes]': data.course_duration_minutes ?? 0,
    'additional_content[course_material_includes]': data.course_material_includes ?? '',
    'additional_content[course_requirements]': data.course_requirements ?? '',
  };
};

export const convertCourseDataToFormData = (courseDetails: CourseDetailsResponse): CourseFormData => {
  console.log(courseDetails.course_pricing);
  return {
    post_date: courseDetails.post_date,
    post_title: courseDetails.post_title,
    post_name: courseDetails.post_name,
    post_content: courseDetails.post_content,
    post_status: courseDetails.post_status as 'publish' | 'private' | 'password_protected',
    post_password: courseDetails.post_password,
    post_author: {
      id: Number(courseDetails.post_author.ID),
      name: courseDetails.post_author.display_name,
      email: courseDetails.post_author.user_email,
      avatar_url: courseDetails.post_author.tutor_profile_photo_url,
    },
    thumbnail: {
      id: null,
      url: courseDetails.thumbnail,
    },
    video: {
      source_type: '',
      source: '',
    },
    course_price_type: courseDetails.course_pricing.type,
    course_price: courseDetails.course_pricing.price,
    course_sale_price: courseDetails.course_pricing.sale_price,
    course_categories: courseDetails.course_categories.map((item) => item.term_id),
    course_tags: courseDetails.course_tags.map((item) => {
      return {
        id: item.term_id,
        name: item.name,
      };
    }),
    course_instructors: [],
    enable_qna: courseDetails.enable_qna === 'yes' ? true : false,
    is_public_course: courseDetails.is_public_course === 'yes' ? true : false,
    course_level: courseDetails.course_level,
    maximum_students: courseDetails.course_settings.maximum_students,
    enrollment_expiry: courseDetails.course_settings.enrollment_expiry,
    course_benefits: courseDetails.course_benefits,
    course_duration_hours: courseDetails.course_duration.hours,
    course_duration_minutes: courseDetails.course_duration.minutes,
    course_material_includes: courseDetails.course_material_includes,
    course_requirements: courseDetails.course_requirements,
    course_target_audience: courseDetails.course_target_audience,
    isContentDripEnabled: courseDetails.course_settings.enable_content_drip === 1 ? true : false,
    contentDripType: courseDetails.course_settings.content_drip_type ?? '',
    course_product_id: String(courseDetails.course_pricing.product_id),
  };
};

export const getCourseId = () => {
  const params = new URLSearchParams(window.location.search);
  const courseId = params.get('course_id');
  return Number(courseId);
};
