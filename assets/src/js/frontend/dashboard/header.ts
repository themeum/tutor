import { type MutationState } from '@Core/ts/services/Query';
import { wpPost } from '@Core/ts/utils/api';
import { convertToErrorMessage } from '@Core/ts/utils/error';

import { tutorConfig } from '@TutorShared/config/config';

import { type AjaxResponse } from '@FrontendTypes/index';

const header = () => {
  const query = window.TutorCore.query;

  return {
    query,
    profileSwitchMutation: null as MutationState<AjaxResponse> | null,
    init() {
      this.profileSwitchMutation = this.query.useMutation(
        (currentMode: string) => wpPost('tutor_switch_profile', { current_mode: currentMode }),
        {
          onSuccess: (res: AjaxResponse) => {
            window.TutorCore.toast.success(res?.message);
            setTimeout(() => {
              window.location.href = tutorConfig.tutor_frontend_dashboard_url;
            }, 1000);
          },
          onError: (error: Error) => {
            window.TutorCore.toast.error(convertToErrorMessage(error));
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
