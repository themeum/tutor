import { __ } from '@wordpress/i18n';

import { type MutationState } from '@Core/ts/services/Query';

import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type TutorMutationResponse } from '@TutorShared/utils/types';

interface SettingsFormProps {
  billing_first_name: string;
  billing_last_name: string;
  billing_email: string;
  billing_country: string;
  billing_state: string;
  billing_city: string;
  billing_phone: string;
  billing_zip_code: string;
  billing_address: string;
}

const settings = () => {
  const query = window.TutorCore.query;

  return {
    query,
    $el: null as HTMLElement | null,
    updateProfileMutation: null as MutationState<unknown> | null,
    saveBillingInfoMutation: null as MutationState<unknown> | null,

    init() {
      if (!this.$el) {
        return;
      }

      this.handleSaveBillingInfo = this.handleSaveBillingInfo.bind(this);

      this.updateProfileMutation = this.query.useMutation(this.updateProfile, {
        onSuccess: (data: TutorMutationResponse<string>) => {
          window.TutorCore.toast.success(data?.message ?? __('Success', 'tutor'));
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to update profile', 'tutor'));
        },
      });

      this.saveBillingInfoMutation = this.query.useMutation(this.saveBillingInfo, {
        onSuccess: (data: TutorMutationResponse<string>) => {
          window.TutorCore.toast.success(data?.message ?? __('Success', 'tutor'));
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to save billing info', 'tutor'));
        },
      });
    },

    async handleSaveBillingInfo(data: SettingsFormProps) {
      await this.saveBillingInfoMutation?.mutate(data);
    },

    async saveBillingInfo(payload: SettingsFormProps) {
      return wpAjaxInstance.post(endpoints.SAVE_BILLING_INFO, payload).then((res) => res.data);
    },

    async updateProfile(payload: SettingsFormProps) {
      return wpAjaxInstance.post(endpoints.UPDATE_PROFILE, payload).then((res) => res.data);
    },

    async handleUpdateProfile(data: SettingsFormProps) {
      await this.updateProfileMutation?.mutate(data);
    },
  };
};

const settingsMeta = {
  name: 'settings',
  component: settings,
};

export const initializeSettings = () => {
  window.TutorComponentRegistry.registerAll({
    components: [settingsMeta],
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
