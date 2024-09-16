import { useToast } from '@Atoms/Toast';
import type { StyleType } from '@Components/magic-ai-image/ImageContext';
import type { ChatFormat, ChatLanguage, ChatTone } from '@Config/magic-ai';
import type { TopicContent } from '@CourseBuilderComponents/ai-course-modal/ContentGenerationContext';
import { wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { ErrorResponse } from '@Utils/form';
import type { Prettify, WPResponse } from '@Utils/types';
import { useMutation, useQueryClient } from '@tanstack/react-query';

interface ImagePayload {
  prompt: string;
  style: StyleType;
}

interface ImageResponse {
  created: number;
  data: { url: string; b64_json: string }[];
}

const generateImage = (payload: ImagePayload) => {
  return wpAjaxInstance.post<ImagePayload, WPResponse<ImageResponse>>(endpoints.GENERATE_AI_IMAGE, payload);
};

export const useMagicImageGenerationMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: generateImage,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

interface FillPayload {
  image: string;
  prompt: string;
}
const magicFillImage = (payload: FillPayload) => {
  return wpAjaxInstance
    .post<FillPayload, WPResponse<ImageResponse>>(endpoints.MAGIC_FILL_AI_IMAGE, payload)
    .then((response) => response.data.data[0].b64_json);
};

export const useMagicFillImageMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: magicFillImage,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

interface TextGenerationPayload {
  prompt: string;
  characters: number;
  language: ChatLanguage;
  tone: ChatTone;
  format: ChatFormat;
  is_html: boolean;
}

const generateText = (payload: TextGenerationPayload) => {
  return wpAjaxInstance.post<TextGenerationPayload, WPResponse<string>>(endpoints.MAGIC_TEXT_GENERATION, payload);
};

export const useMagicTextGenerationMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: generateText,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

export type ModificationType = 'rephrase' | 'make_shorter' | 'write_as_bullets' | 'make_longer' | 'simplify_language';
interface ModifyPayloadBase {
  content: string;
  is_html: boolean;
}

interface TranslationPayload extends ModifyPayloadBase {
  type: 'translation';
  language: ChatLanguage;
}

interface ChangeTonePayload extends ModifyPayloadBase {
  type: 'change_tone';
  tone: ChatTone;
}

interface GeneralPayload extends ModifyPayloadBase {
  type: ModificationType;
}

export type ModificationPayload = Prettify<TranslationPayload | ChangeTonePayload | GeneralPayload>;

const modifyContent = (payload: ModificationPayload) => {
  return wpAjaxInstance.post<ModificationPayload, WPResponse<string>>(endpoints.MAGIC_AI_MODIFY_CONTENT, payload);
};

export const useModifyContentMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: modifyContent,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

interface UseImagePayload {
  image: string;
}

const storeImage = (payload: UseImagePayload) => {
  return wpAjaxInstance.post<UseImagePayload, WPResponse<{ id: number; url: string; title: string }>>(
    endpoints.USE_AI_GENERATED_IMAGE,
    payload,
  );
};

export const useStoreAIGeneratedImageMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: storeImage,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

export type ContentType = 'title' | 'image' | 'description' | 'topic_names';

interface CourseGenerationTitle {
  type: Extract<ContentType, 'title'>;
  prompt: string;
}

interface CourseGenerationOther {
  type: Omit<ContentType, 'title'>;
  title: string;
}

type CourseGenerationPayload = Prettify<CourseGenerationTitle | CourseGenerationOther>;

const generateCourseContent = (payload: CourseGenerationPayload) => {
  return wpAjaxInstance.post<CourseGenerationPayload, WPResponse<string>>(endpoints.GENERATE_COURSE_CONTENT, payload);
};

export const useGenerateCourseContentMutation = (type: ContentType) => {
  const { showToast } = useToast();
  return useMutation({
    mutationKey: ['GenerateCourseContent', type],
    mutationFn: generateCourseContent,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

interface CourseTopicPayload {
  type: ContentType;
  title: string;
}

const generateCourseTopicNames = (payload: CourseTopicPayload) => {
  return wpAjaxInstance.post<CourseGenerationPayload, WPResponse<{ title: string }[]>>(
    endpoints.GENERATE_COURSE_CONTENT,
    payload,
  );
};

export const useGenerateCourseTopicNamesMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: generateCourseTopicNames,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

interface TopicContentPayload {
  title: string;
  topic_name: string;
  index: number;
}

const generateCourseTopicContent = (payload: TopicContentPayload) => {
  return wpAjaxInstance.post<TopicContentPayload, WPResponse<{ topic_contents: TopicContent[]; index: number }>>(
    endpoints.GENERATE_COURSE_TOPIC_CONTENT,
    payload,
  );
};

export const useGenerateCourseTopicContentMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: generateCourseTopicContent,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

interface SaveContentPayload {
  course_id: number;
  payload: string;
}

const saveAIGeneratedCourseContent = (payload: SaveContentPayload) => {
  return wpAjaxInstance.post(endpoints.SAVE_AI_GENERATED_COURSE_CONTENT, payload);
};

export const useSaveAIGeneratedCourseContentMutation = () => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: saveAIGeneratedCourseContent,
    onSuccess() {
      queryClient.invalidateQueries({ queryKey: ['CourseDetails'] });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};

interface QuizQuestionsPayload {
  title: string;
  topic_name: string;
  quiz_title: string;
}

export interface QuizContent {
  title: string;
  type: 'true_false' | 'multiple_choice' | 'open_ended';
  description: string;
  options?: { name: string; is_correct: boolean }[];
}

const generateQuizQuestions = (payload: QuizQuestionsPayload) => {
  return wpAjaxInstance.post<QuizQuestionsPayload, WPResponse<QuizContent[]>>(
    endpoints.GENERATE_QUIZ_QUESTIONS,
    payload,
  );
};

export const useGenerateQuizQuestionsMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: generateQuizQuestions,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: error.response.data.message });
    },
  });
};
