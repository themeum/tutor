import { __ } from '@wordpress/i18n';
import axios from 'axios';

import { type MutationState, type QueryState } from '@Core/ts/services/Query';

import { tutorConfig } from '@TutorShared/config/config';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type TutorMutationResponse } from '@TutorShared/utils/types';

interface SocialFormProps {
  _tutor_profile_facebook: string;
  _tutor_profile_twitter: string;
  _tutor_profile_linkedin: string;
  _tutor_profile_github: string;
  _tutor_profile_website: string;
  [key: string]: string;
}

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
  const form = window.TutorCore.form;

  return {
    query,
    form,
    $el: null as HTMLElement | null,
    fetchCountriesQuery: null as QueryState<unknown> | null,
    updateProfileMutation: null as MutationState<unknown> | null,
    saveSocialProfileMutation: null as MutationState<unknown> | null,
    saveBillingInfoMutation: null as MutationState<unknown> | null,

    init() {
      if (!this.$el) {
        return;
      }

      this.handleUpdateProfile = this.handleUpdateProfile.bind(this);
      this.handleSaveSocialProfile = this.handleSaveSocialProfile.bind(this);
      this.handleSaveBillingInfo = this.handleSaveBillingInfo.bind(this);

      this.fetchCountriesQuery = this.query.useQuery('fetch-countries', () => this.fetchCountries());

      this.updateProfileMutation = this.query.useMutation(this.updateProfile, {
        onSuccess: (data: TutorMutationResponse<string>) => {
          window.TutorCore.toast.success(data?.message ?? __('Successfully updated profile', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to update profile', 'tutor'));
        },
      });

      this.saveSocialProfileMutation = this.query.useMutation(this.saveSocialProfile, {
        onSuccess: (data: TutorMutationResponse<string>) => {
          window.TutorCore.toast.success(data?.message ?? __('Success successfully saved social profile', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to save social profile', 'tutor'));
        },
      });

      this.saveBillingInfoMutation = this.query.useMutation(this.saveBillingInfo, {
        onSuccess: (data: TutorMutationResponse<string>) => {
          window.TutorCore.toast.success(data?.message ?? __('Success successfully saved billing info', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to save billing info', 'tutor'));
        },
      });
    },

    async fetchCountries() {
      return await axios.get(`${tutorConfig.tutor_url}${endpoints.FETCH_COUNTRIES}`).then((res) => res.data);
    },

    async updateProfile(payload: SettingsFormProps) {
      return wpAjaxInstance.post(endpoints.UPDATE_PROFILE, payload).then((res) => res.data);
    },

    async handleUpdateProfile(data: SettingsFormProps) {
      await this.updateProfileMutation?.mutate(data);
    },

    async saveSocialProfile(payload: SocialFormProps) {
      return wpAjaxInstance.post(endpoints.SAVE_SOCIAL_PROFILE, payload).then((res) => res.data);
    },

    async handleSaveSocialProfile(data: SocialFormProps) {
      await this.saveSocialProfileMutation?.mutate(data);
    },

    async saveBillingInfo(payload: SettingsFormProps) {
      return wpAjaxInstance.post(endpoints.SAVE_BILLING_INFO, payload).then((res) => res.data);
    },

    async handleSaveBillingInfo(data: SettingsFormProps) {
      await this.saveBillingInfoMutation?.mutate(data);
    },
  };
};

const settingsMeta = {
  name: 'settings',
  component: settings,
};

export const initializeSettings = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: settingsMeta,
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
