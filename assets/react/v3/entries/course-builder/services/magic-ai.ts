import { useToast } from '@Atoms/Toast';
import type { StyleType } from '@Components/magic-ai-image/ImageContext';
import type { ChatFormat, ChatLanguage, ChatTone } from '@Config/magic-ai';
import { wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { Prettify, WPResponse } from '@Utils/types';
import { useMutation } from '@tanstack/react-query';

interface ImagePayload {
  prompt: string;
  style: StyleType;
}

interface ImageResponse {
  created: number;
  data: { url: string; b64_json: string }[];
}

const generateImage = (payload: ImagePayload) => {
  const promises = Array.from({ length: 4 }).map(() => {
    return wpAjaxInstance.get<WPResponse<ImageResponse>>(endpoints.GENERATE_AI_IMAGE, {
      params: payload,
    });
  });

  return Promise.all(promises).then((response) => {
    return response.flatMap((item) => item.data.data) as unknown as { url: string; b64_json: string }[];
  });
};

export const useMagicImageGenerationMutation = () => {
  const { showToast } = useToast();
  return useMutation({
    mutationFn: generateImage,
    onError(error: Error) {
      showToast({ type: 'danger', message: error.message });
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
    onError(error: Error) {
      showToast({ type: 'danger', message: error.message });
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
    onError(error: Error) {
      showToast({ type: 'danger', message: error.message });
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
    onError(error: Error) {
      showToast({ type: 'danger', message: error.message });
    },
  });
};

interface UseImagePayload {
  image: string;
  course_id: number;
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
    onError(error: Error) {
      showToast({ type: 'danger', message: error.message });
    },
  });
};

interface CourseGenerationPayload {
  prompt: string;
}

const generateCourseContent = (payload: CourseGenerationPayload) => {
  return wpAjaxInstance.post<CourseGenerationPayload, WPResponse<{ title: string; description: string }>>(
    endpoints.GENERATE_COURSE_CONTENT,
    payload,
  );
};

export const useGenerateCourseContentMutation = () => {
  return useMutation({
    mutationFn: generateCourseContent,
  });
};
