import { __ } from '@wordpress/i18n';
import axios from 'axios';

import { type MutationState, type QueryState } from '@Core/ts/services/Query';

import { type WPMedia } from '@Core/ts/services/WPMedia';
import { tutorConfig } from '@TutorShared/config/config';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type TutorMutationResponse } from '@TutorShared/utils/types';

interface ProfilePhotoFormProps {
  photo_file: File;
  photo_type: 'profile_photo';
}

interface AccountFormProps {
  first_name: string;
  last_name: string;
  username: string;
  phone_number: string;
  timezone: string;
  occupation: string;
  bio: string;
  display_name: string;
  tutor_pro_custom_signature_id: WPMedia | null;
}

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

interface WithdrawMethodFormProps {
  withdraw_method: string;
  [key: string]: string;
}

interface ResetPasswordFormProps {
  current_password: string;
  new_password: string;
  confirm_new_password: string;
}

const settings = () => {
  const query = window.TutorCore.query;
  const form = window.TutorCore.form;

  return {
    query,
    form,
    $el: null as HTMLElement | null,
    fetchCountriesQuery: null as QueryState<unknown> | null,
    uploadProfilePhotoMutation: null as MutationState<unknown> | null,
    removeProfilePhotoMutation: null as MutationState<unknown> | null,
    updateProfileMutation: null as MutationState<unknown> | null,
    saveSocialProfileMutation: null as MutationState<unknown> | null,
    saveBillingInfoMutation: null as MutationState<unknown> | null,
    saveWithdrawMethodMutation: null as MutationState<unknown> | null,
    resetPasswordMutation: null as MutationState<unknown> | null,

    init() {
      if (!this.$el) {
        return;
      }

      this.handleUpdateProfile = this.handleUpdateProfile.bind(this);
      this.handleUploadProfilePhoto = this.handleUploadProfilePhoto.bind(this);
      this.handleRemoveProfilePhoto = this.handleRemoveProfilePhoto.bind(this);
      this.handleSaveSocialProfile = this.handleSaveSocialProfile.bind(this);
      this.handleSaveBillingInfo = this.handleSaveBillingInfo.bind(this);
      this.handleSaveWithdrawMethod = this.handleSaveWithdrawMethod.bind(this);
      this.handleResetPassword = this.handleResetPassword.bind(this);

      this.fetchCountriesQuery = this.query.useQuery('fetch-countries', () => this.fetchCountries());

      this.uploadProfilePhotoMutation = this.query.useMutation(this.uploadProfilePhoto, {
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to update profile', 'tutor'));
        },
      });

      this.removeProfilePhotoMutation = this.query.useMutation(this.removeProfilePhoto, {
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to update profile', 'tutor'));
        },
      });

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

      this.saveWithdrawMethodMutation = this.query.useMutation(this.saveWithdrawMethod, {
        onSuccess: (data: TutorMutationResponse<string>) => {
          window.TutorCore.toast.success(data?.message ?? __('Withdrawal method saved successfully', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to save withdrawal method', 'tutor'));
        },
      });

      this.resetPasswordMutation = this.query.useMutation(this.resetPassword, {
        onSuccess: (data: TutorMutationResponse<string>) => {
          window.TutorCore.toast.success(data?.message ?? __('Password updated successfully', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to update password', 'tutor'));
        },
      });
    },

    async fetchCountries() {
      return await axios.get(`${tutorConfig.tutor_url}${endpoints.FETCH_COUNTRIES}`).then((res) => res.data);
    },

    async uploadProfilePhoto(payload: ProfilePhotoFormProps) {
      return wpAjaxInstance.post(endpoints.UPLOAD_PROFILE_PHOTO, payload).then((res) => res.data);
    },

    async handleUploadProfilePhoto(files: File[]) {
      const data = {
        photo_file: files[0],
        photo_type: 'profile_photo',
      } satisfies ProfilePhotoFormProps;
      await this.uploadProfilePhotoMutation?.mutate(data);
    },

    async removeProfilePhoto() {
      return wpAjaxInstance.post(endpoints.REMOVE_PROFILE_PHOTO).then((res) => res.data);
    },

    async handleRemoveProfilePhoto() {
      await this.removeProfilePhotoMutation?.mutate({});
    },

    async updateProfile(payload: AccountFormProps) {
      return wpAjaxInstance.post(endpoints.UPDATE_PROFILE, payload).then((res) => res.data);
    },

    async handleUpdateProfile(data: AccountFormProps) {
      const payload = {
        ...data,
        tutor_pro_custom_signature_id: data.tutor_pro_custom_signature_id?.id || '',
      };
      await this.updateProfileMutation?.mutate(payload);
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

    async saveWithdrawMethod(payload: Record<string, unknown>) {
      return wpAjaxInstance.post(endpoints.SAVE_WITHDRAW_METHOD, payload).then((res) => res.data);
    },

    async handleSaveWithdrawMethod(data: WithdrawMethodFormProps) {
      const selectedMethod = data.withdraw_method;

      const payload: Record<string, string> = {
        tutor_selected_withdraw_method: selectedMethod,
      };

      Object.keys(data).forEach((key) => {
        if (key.startsWith(selectedMethod + '_')) {
          const fieldName = key.replace(selectedMethod + '_', '');
          payload[`withdraw_method_field[${selectedMethod}][${fieldName}]`] = data[key] || '';
        }
      });

      await this.saveWithdrawMethodMutation?.mutate(payload);
    },

    async resetPassword(payload: ResetPasswordFormProps) {
      return wpAjaxInstance.post(endpoints.RESET_PASSWORD, payload).then((res) => res.data);
    },

    async handleResetPassword(data: ResetPasswordFormProps) {
      if (data.new_password !== data.confirm_new_password) {
        window.TutorCore.toast.error(__('Passwords do not match', 'tutor'));
        return;
      }

      await this.resetPasswordMutation?.mutate(data);
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
