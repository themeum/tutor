import { type MutationState } from '@Core/ts/services/Query';
import { type AjaxResponse } from '@Core/ts/types';

const header = () => {
  const { query, toast } = window.TutorCore;
  const { tutorConfig } = window.TutorCore.config;
  const { wpPost } = window.TutorCore.api;
  const { convertToErrorMessage } = window.TutorCore.error;

  return {
    query,
    profileSwitchMutation: null as MutationState<AjaxResponse> | null,
    init() {
      this.profileSwitchMutation = this.query.useMutation(
        (currentMode: string) => wpPost('tutor_switch_profile', { current_mode: currentMode }),
        {
          onSuccess: (res: AjaxResponse) => {
            toast.success(res?.message);
            setTimeout(() => {
              window.location.href = tutorConfig.tutor_frontend_dashboard_url;
            }, 1000);
          },
          onError: (error: Error) => {
            toast.error(convertToErrorMessage(error));
          },
        },
      );
    },
  };
};

export const initializeHeader = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: {
      name: 'header',
      component: header,
    },
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
