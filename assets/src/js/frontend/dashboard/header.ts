import { type MutationState } from '@Core/ts/services/Query';
import { type AjaxResponse } from '@FrontendTypes/index';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import { convertToErrorMessage } from '@TutorShared/utils/util';

const header = () => {
  const query = window.TutorCore.query;

  return {
    query,
    profileSwitchMutation: null as MutationState<AjaxResponse> | null,
    init() {
      this.profileSwitchMutation = this.query.useMutation(
        (currentMode: string) => wpAjaxInstance.post('tutor_switch_profile', { current_mode: currentMode }),
        {
          onSuccess: (res: AjaxResponse) => {
            window.TutorCore.toast.success(res?.message);
            setTimeout(() => {
              window.location.reload();
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
