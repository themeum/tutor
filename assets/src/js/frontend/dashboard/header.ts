import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import { __ } from '@wordpress/i18n';

const header = () => {
  const query = window.TutorCore.query;

  return {
    query,
    profileSwitchMutation: null as MutationState<unknown, string> | null,
    init() {
      this.profileSwitchMutation = this.query.useMutation(
        (currentMode: string) => wpAjaxInstance.post('tutor_switch_profile', { current_mode: currentMode }),
        {
          onSuccess: () => {
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
