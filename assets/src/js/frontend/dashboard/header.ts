import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import { __ } from '@wordpress/i18n';
import { type AjaxResponse } from '@FrontendTypes/index';

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
            window.location.reload();
          },
          onError: (error: Error) => {
            window.TutorCore.toast.error(error.message || __('Failed to switch the mode', 'tutor'));
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
