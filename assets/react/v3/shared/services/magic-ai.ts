import { useMutation, useQueryClient } from '@tanstack/react-query';

import { useToast } from '@TutorShared/atoms/Toast';
import type { StyleType } from '@TutorShared/components/magic-ai-image/ImageContext';

import type { TopicContent } from '@CourseBuilderComponents/ai-course-modal/ContentGenerationContext';
import type { ChatFormat, ChatLanguage, ChatTone } from '@TutorShared/config/magic-ai';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import type { ErrorResponse } from '@TutorShared/utils/form';
import type { Prettify, TutorMutationResponse } from '@TutorShared/utils/types';
import { convertToErrorMessage } from '@TutorShared/utils/util';

interface ImagePayload {
  prompt: string;
  style: StyleType;
}

interface ImageResponse {
  created: number;
  data: { url: string; b64_json: string }[];
}

const generateImage = (payload: ImagePayload) => {
  return wpAjaxInstance.post<ImagePayload, TutorMutationResponse<ImageResponse>>(endpoints.GENERATE_AI_IMAGE, payload);
};

export const useMagicImageGenerationMutation = () => {
  return useMutation({
    mutationFn: generateImage,
  });
};

interface FillPayload {
  image: string;
  prompt: string;
}
const magicFillImage = (payload: FillPayload) => {
  return wpAjaxInstance
    .post<FillPayload, TutorMutationResponse<ImageResponse>>(endpoints.MAGIC_FILL_AI_IMAGE, payload)
    .then((response) => response.data.data[0].b64_json);
};

export const useMagicFillImageMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: magicFillImage,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
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
  return wpAjaxInstance.post<TextGenerationPayload, TutorMutationResponse<string>>(
    endpoints.MAGIC_TEXT_GENERATION,
    payload,
  );
};

export const useMagicTextGenerationMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: generateText,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
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
  return wpAjaxInstance.post<ModificationPayload, TutorMutationResponse<string>>(
    endpoints.MAGIC_AI_MODIFY_CONTENT,
    payload,
  );
};

export const useModifyContentMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: modifyContent,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

interface UseImagePayload {
  image: string;
}

const storeImage = (payload: UseImagePayload) => {
  return wpAjaxInstance.post<UseImagePayload, TutorMutationResponse<{ id: number; url: string; title: string }>>(
    endpoints.USE_AI_GENERATED_IMAGE,
    payload,
  );
};

export const useStoreAIGeneratedImageMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: storeImage,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
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

const generateCourseContent = (
  payload: CourseGenerationPayload & {
    signal?: AbortSignal;
  },
) => {
  return wpAjaxInstance.post<CourseGenerationPayload, TutorMutationResponse<string>>(
    endpoints.GENERATE_COURSE_CONTENT,
    payload,
    {
      signal: payload.signal,
    },
  );
};

export const useGenerateCourseContentMutation = (type: ContentType) => {
  const { showToast } = useToast();
  return useMutation({
    mutationKey: ['GenerateCourseContent', type],
    mutationFn: generateCourseContent,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

interface CourseTopicPayload {
  type: ContentType;
  title: string;
}

const generateCourseTopicNames = (payload: CourseTopicPayload & { signal?: AbortSignal }) => {
  return wpAjaxInstance.post<CourseGenerationPayload, TutorMutationResponse<{ title: string }[]>>(
    endpoints.GENERATE_COURSE_CONTENT,
    payload,
    {
      signal: payload.signal,
    },
  );
};

export const useGenerateCourseTopicNamesMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: generateCourseTopicNames,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

interface TopicContentPayload {
  title: string;
  topic_name: string;
  index: number;
}

const generateCourseTopicContent = (payload: TopicContentPayload & { signal?: AbortSignal }) => {
  return wpAjaxInstance.post<
    TopicContentPayload,
    TutorMutationResponse<{ topic_contents: TopicContent[]; index: number }>
  >(endpoints.GENERATE_COURSE_TOPIC_CONTENT, payload, {
    signal: payload.signal,
  });
};

export const useGenerateCourseTopicContentMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: generateCourseTopicContent,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
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
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
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

const generateQuizQuestions = (payload: QuizQuestionsPayload & { signal?: AbortSignal }) => {
  return wpAjaxInstance.post<QuizQuestionsPayload, TutorMutationResponse<QuizContent[]>>(
    endpoints.GENERATE_QUIZ_QUESTIONS,
    payload,
    {
      signal: payload.signal,
    },
  );
};

export const useGenerateQuizQuestionsMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: generateQuizQuestions,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const saveOpenAiSettingsKey = (payload: { chatgpt_api_key: string; chatgpt_enable: 1 | 0 }) => {
  return wpAjaxInstance.post<
    {
      chatgpt_api_key: string;
      chatgpt_enable: 'on' | 'off';
    },
    TutorMutationResponse<null>
  >(endpoints.OPEN_AI_SAVE_SETTINGS, {
    ...payload,
  });
};

export const useSaveOpenAiSettingsMutation = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: saveOpenAiSettingsKey,
    onSuccess: (response) => {
      showToast({ type: 'success', message: response.message });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};
