import { useMutation } from '@tanstack/react-query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type TutorMutationResponse } from '@TutorShared/utils/types';

const getYouTubeVideoDuration = (videoId: string) => {
  return wpAjaxInstance.post<
    { videoId: string },
    TutorMutationResponse<{
      duration: string;
    }>
  >(endpoints.TUTOR_YOUTUBE_VIDEO_DURATION, {
    video_id: videoId,
  });
};

export const useGetYouTubeVideoDuration = () => {
  return useMutation({
    mutationFn: getYouTubeVideoDuration,
  });
};
