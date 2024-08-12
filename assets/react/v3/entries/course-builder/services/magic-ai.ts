import { useToast } from '@Atoms/Toast';
import type { StyleType } from '@Components/magic-ai-image/ImageContext';
import { wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { WPResponse } from '@Utils/types';
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
  const promises = [1, 2, 3, 4].map(() => {
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
  return useMutation({
    mutationFn: magicFillImage,
  });
};
