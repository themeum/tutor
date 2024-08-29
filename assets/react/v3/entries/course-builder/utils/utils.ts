import type { UserOption } from '@Components/fields/FormSelectUser';
import { tutorConfig } from '@Config/config';
import { Addons, DateFormats } from '@Config/constants';
import type { AssignmentForm } from '@CourseBuilderComponents/modals/AssignmentModal';
import type { LessonForm } from '@CourseBuilderComponents/modals/LessonModal';
import type { ContentDripType, CourseDetailsResponse, CourseFormData } from '@CourseBuilderServices/course';
import type { AssignmentPayload, ID, LessonPayload } from '@CourseBuilderServices/curriculum';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import { convertToGMT } from '@Utils/util';
import { __ } from '@wordpress/i18n';
import type { UseFormReturn } from 'react-hook-form';

// biome-ignore lint/suspicious/noExplicitAny: <explanation>
export const convertCourseDataToPayload = (data: CourseFormData): any => {
  return {
    post_date: data.post_date,
    post_date_gmt: convertToGMT(new Date(data.post_date), DateFormats.yearMonthDayHourMinuteSecond),
    post_title: data.post_title,
    post_name: data.post_name,
    ...(data.editor_used.name === 'classic' && {
      post_content: data.post_content,
    }),
    post_status: data.post_status,
    post_password: data.visibility === 'password_protected' ? data.post_password : '',
    post_author: data.post_author?.id ?? null,
    'pricing[type]': data.course_pricing_category === 'subscription' ? 'subscription' : data.course_price_type,
    ...(data.course_pricing_category !== 'subscription' &&
      data.course_price_type === 'paid' &&
      data.course_product_id && {
        'pricing[product_id]': data.course_product_id,
      }),

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
    'course_settings[enable_content_drip]': data.contentDripType ? 1 : 0,
    'course_settings[content_drip_type]': data.contentDripType,
    'course_settings[enable_tutor_bp]': data.enable_tutor_bp ? 1 : 0,

    'additional_content[course_benefits]': data.course_benefits ?? '',
    'additional_content[course_target_audience]': data.course_target_audience ?? '',
    'additional_content[course_duration][hours]': data.course_duration_hours ?? 0,
    'additional_content[course_duration][minutes]': data.course_duration_minutes ?? 0,
    'additional_content[course_material_includes]': data.course_material_includes ?? '',
    'additional_content[course_requirements]': data.course_requirements ?? '',
    preview_link: data.preview_link,

    ...(isAddonEnabled(Addons.TUTOR_MULTI_INSTRUCTORS) && {
      course_instructor_ids: [...data.course_instructors.map((item) => item.id), Number(data.post_author?.id)],
    }),

    ...(isAddonEnabled(Addons.TUTOR_PREREQUISITES) && {
      _tutor_prerequisites_main_edit: true,
      _tutor_course_prerequisites_ids: data.course_prerequisites?.map((item) => item.id) ?? [],
    }),
    tutor_course_certificate_template: data.tutor_course_certificate_template,
    _tutor_course_additional_data_edit: true,
    _tutor_attachments_main_edit: true,
    ...(data.video.source && {
      'video[source]': data.video.source,
      'video[source_video_id]': data.video.source_video_id,
      'video[poster]': data.video.poster,
      'video[source_external_url]': data.video.source_external_url,
      'video[source_shortcode]': data.video.source_shortcode,
      'video[source_youtube]': data.video.source_youtube,
      'video[source_vimeo]': data.video.source_vimeo,
      'video[source_embedded]': data.video.source_embedded,
    }),
    tutor_attachments: (data.course_attachments || []).map((item) => item.id) ?? [],
    bp_attached_group_ids: data.bp_attached_group_ids,
  };
};

export const convertCourseDataToFormData = (courseDetails: CourseDetailsResponse): CourseFormData => {
  return {
    post_date: courseDetails.post_date,
    post_title: courseDetails.post_title,
    post_name: courseDetails.post_name,
    post_content: courseDetails.post_content,
    post_status: courseDetails.post_status,
    visibility: (() => {
      if (courseDetails.post_password.length) {
        return 'password_protected';
      }
      if (courseDetails.post_status === 'private') {
        return 'private';
      }
      return 'publish';
    })(),
    post_password: courseDetails.post_password,
    post_author: {
      id: Number(courseDetails.post_author.ID),
      name: courseDetails.post_author.display_name,
      email: courseDetails.post_author.user_email,
      avatar_url: courseDetails.post_author.tutor_profile_photo_url,
    },
    thumbnail: {
      id: courseDetails.thumbnail_id ? Number(courseDetails.thumbnail_id) : 0,
      title: '',
      url: courseDetails.thumbnail,
    },
    video: {
      source: courseDetails.video.source ?? '',
      source_video_id: courseDetails.video.source_video_id ?? '',
      poster: courseDetails.video.poster ?? '',
      poster_url: courseDetails.video.poster_url ?? '',
      source_external_url: courseDetails.video.source_external_url ?? '',
      source_shortcode: courseDetails.video.source_shortcode ?? '',
      source_youtube: courseDetails.video.source_youtube ?? '',
      source_vimeo: courseDetails.video.source_vimeo ?? '',
      source_embedded: courseDetails.video.source_embedded ?? '',
    },
    course_product_name: courseDetails.course_pricing.product_name,
    course_pricing_category: (() => {
      if (
        isAddonEnabled(Addons.SUBSCRIPTION) &&
        tutorConfig.settings.monetize_by === 'tutor' &&
        courseDetails.course_pricing.type === 'subscription'
      ) {
        return 'subscription';
      }
      return 'regular';
    })(),
    course_price_type:
      courseDetails.course_pricing.type === 'subscription' || !courseDetails.course_pricing.type
        ? 'free'
        : courseDetails.course_pricing.type,
    course_price: courseDetails.course_pricing.price,
    course_sale_price: courseDetails.course_pricing.sale_price,
    course_categories: courseDetails.course_categories.map((item) => item.term_id),
    course_tags: courseDetails.course_tags.map((item) => {
      return {
        id: item.term_id,
        name: item.name,
      };
    }),
    enable_qna: courseDetails.enable_qna === 'yes' ? true : false,
    is_public_course: courseDetails.is_public_course === 'yes' ? true : false,
    course_level: courseDetails.course_level || 'intermediate',
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
    course_product_id:
      String(courseDetails.course_pricing.product_id) === '0' ? '' : String(courseDetails.course_pricing.product_id),
    course_instructors:
      courseDetails.course_instructors?.reduce((instructors, item) => {
        if (String(item.id) !== String(courseDetails.post_author.ID)) {
          instructors.push({
            id: item.id,
            name: item.display_name,
            email: item.user_email,
            avatar_url: item.avatar_url,
            isRemoveAble: false,
          });
        }
        return instructors;
      }, [] as UserOption[]) ?? [],
    preview_link: courseDetails.preview_link ?? '',
    course_prerequisites: courseDetails.course_prerequisites ?? [],
    tutor_course_certificate_template: courseDetails.course_certificate_template ?? '',
    course_attachments: courseDetails.course_attachments ?? [],
    enable_tutor_bp: courseDetails.course_settings.enable_tutor_bp === 1 ? true : false,
    bp_attached_group_ids: courseDetails.bp_attached_groups ?? [],
    editor_used: courseDetails.editor_used,
  };
};

export const convertLessonDataToPayload = (
  data: LessonForm,
  lessonId: ID,
  topicId: ID,
  contentDripType: ContentDripType,
): LessonPayload => {
  return {
    ...(lessonId && { lesson_id: lessonId }),
    topic_id: topicId,
    title: data.title,
    description: data.description,
    thumbnail_id: data.thumbnail?.id ?? null,

    'video[source]': data.video?.source || '-1',
    'video[source_video_id]': data.video?.source_video_id || '',
    'video[poster]': data.video?.poster || '',
    'video[source_external_url]': data.video?.source_external_url || '',
    'video[source_shortcode]': data.video?.source_shortcode || '',
    'video[source_youtube]': data.video?.source_youtube || '',
    'video[source_vimeo]': data.video?.source_vimeo || '',
    'video[source_embedded]': data.video?.source_embedded || '',

    'video[runtime][hours]': data.duration.hour || 0,
    'video[runtime][minutes]': data.duration.minute || 0,
    'video[runtime][seconds]': data.duration.second || 0,
    ...(isAddonEnabled(Addons.TUTOR_COURSE_PREVIEW) && { _is_preview: data.lesson_preview ? 1 : 0 }),
    tutor_attachments: (data.tutor_attachments || []).map((attachment) => attachment.id),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'unlock_by_date' && {
        'content_drip_settings[unlock_date]': data.content_drip_settings.unlock_date || '',
      }),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'specific_days' && {
        'content_drip_settings[after_xdays_of_enroll]': data.content_drip_settings.after_xdays_of_enroll || '0',
      }),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'after_finishing_prerequisites' && {
        'content_drip_settings[prerequisites]': data.content_drip_settings.prerequisites || [],
      }),
  };
};

export const convertAssignmentDataToPayload = (
  data: AssignmentForm,
  assignmentId: ID,
  topicId: ID,
  contentDripType: ContentDripType,
): AssignmentPayload => {
  return {
    ...(assignmentId && { assignment_id: assignmentId }),
    topic_id: topicId,
    title: data.title,
    summary: data.summary,
    attachments: (data.attachments || []).map((attachment) => attachment.id),
    'assignment_option[time_duration][time]': data.time_duration.time,
    'assignment_option[time_duration][value]': data.time_duration.value,
    'assignment_option[total_mark]': data.total_mark,
    'assignment_option[pass_mark]': data.pass_mark,
    'assignment_option[upload_files_limit]': data.upload_files_limit,
    'assignment_option[upload_file_size_limit]': data.upload_file_size_limit,

    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'unlock_by_date' && {
        'content_drip_settings[unlock_date]': data.content_drip_settings.unlock_date || '',
      }),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'specific_days' && {
        'content_drip_settings[after_xdays_of_enroll]': data.content_drip_settings.after_xdays_of_enroll || '0',
      }),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'after_finishing_prerequisites' && {
        'content_drip_settings[prerequisites]': data.content_drip_settings.prerequisites || [],
      }),
  };
};

export const getCourseId = () => {
  const params = new URLSearchParams(window.location.search);
  const courseId = params.get('course_id');
  return Number(courseId);
};

type Addon = `${Addons}`;

export const isAddonEnabled = (addon: Addon) => {
  return !!tutorConfig.addons_data.find((item) => item.name === addon)?.is_enabled;
};

export async function getVimeoVideoDuration(videoUrl: string): Promise<number | null> {
  const videoId = Number.parseInt(videoUrl.split('/').pop() || '', 10);
  const jsonUrl = `https://vimeo.com/api/v2/video/${videoId}.xml`;

  try {
    const response = await fetch(jsonUrl);
    if (!response.ok) {
      throw new Error('Failed to fetch the video data');
    }

    const textData = await response.text();

    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(textData, 'application/xml');

    const durationElement = xmlDoc.getElementsByTagName('duration')[0];
    if (!durationElement || !durationElement.textContent) {
      return null;
    }

    const duration = Number.parseInt(durationElement.textContent, 10);
    return duration; // in seconds
  } catch (error) {
    console.error('Error fetching video duration:', error);
    return null;
  }
}

export const getExternalVideoDuration = async (videoUrl: string): Promise<number | null> => {
  const video = document.createElement('video');
  video.src = videoUrl;
  video.preload = 'metadata';

  return new Promise((resolve) => {
    video.onloadedmetadata = () => {
      resolve(video.duration);
    };
  });
};

const convertYouTubeDurationToSeconds = (duration: string) => {
  const matches = duration.match(/PT(\d+H)?(\d+M)?(\d+S)?/);

  if (!matches) {
    return 0;
  }

  const hours = matches[1] ? Number(matches[1].replace('H', '')) : 0;
  const minutes = matches[2] ? Number(matches[2].replace('M', '')) : 0;
  const seconds = matches[3] ? Number(matches[3].replace('S', '')) : 0;

  return hours * 3600 + minutes * 60 + seconds;
};

export const getYouTubeVideoDuration = async (videoUrl: string): Promise<number | null> => {
  const apiKey = tutorConfig.settings.lesson_video_duration_youtube_api_key;
  const videoId = videoUrl.split('v=')[1];
  const jsonUrl = `https://www.googleapis.com/youtube/v3/videos?id=${videoId}&part=contentDetails&key=${apiKey}`;

  try {
    const response = await fetch(jsonUrl);
    if (!response.ok) {
      throw new Error('Failed to fetch the video data');
    }

    const data = await response.json();
    const duration = data.items[0].contentDetails.duration;
    const seconds = convertYouTubeDurationToSeconds(duration);
    return seconds;
  } catch (error) {
    console.error('Error fetching video duration:', error);
    return null;
  }
};

export const covertSecondsToHMS = (seconds: number) => {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const sec = seconds % 60;
  return { hours, minutes, seconds: sec };
};

export const validateQuizQuestion = (
  activeQuestionIndex: number,
  form: UseFormReturn<QuizForm>,
):
  | {
      message: string;
      type: 'question' | 'quiz' | 'correct_option' | 'add_option' | 'save_option';
    }
  | true => {
  if (activeQuestionIndex !== -1) {
    const answers =
      form.watch(`questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers') || [];
    const currentQuestionType = form.watch(`questions.${activeQuestionIndex}.question_type`);
    const isAllSaved = answers.every((answer) => answer.is_saved);
    const hasCorrectAnswer = answers.some((answer) => answer.is_correct === '1');

    if (answers.length === 0 && currentQuestionType !== 'open_ended' && currentQuestionType !== 'short_answer') {
      return {
        message: __('Please add an option.', 'tutor'),
        type: 'add_option',
      };
    }

    if (!isAllSaved) {
      return {
        message: __('Please finish editing all newly created options.', 'tutor'),
        type: 'save_option',
      };
    }

    if (['true_false', 'multiple_choice'].includes(currentQuestionType) && !hasCorrectAnswer) {
      return {
        message: __('Please select a correct answer.', 'tutor'),
        type: 'correct_option',
      };
    }
  }

  return true;
};
