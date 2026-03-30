import { __ } from '@wordpress/i18n';

import { type MutationState } from '@Core/ts/services/Query';
import { type WPMedia } from '@Core/ts/services/WPMedia';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type TutorMutationResponse } from '@TutorShared/utils/types';
import { convertToErrorMessage } from '@TutorShared/utils/util';

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

interface PreferencesFormProps {
  auto_play_next: boolean;
  theme: string;
  font_scale: number;
  learning_mood: boolean;
  formId?: string;
}

interface ResetPreferencesPayload {
  formId: string;
  modalId: string;
}

interface UpdateNotificationProps {
  [key: string]: boolean | string;
}

interface ResetPasswordResponse {
  success: boolean;
  data: {
    message: string;
  };
}

const settings = () => {
  const query = window.TutorCore.query;
  const form = window.TutorCore.form;
  const modal = window.TutorCore.modal;
  const toast = window.TutorCore.toast;

  return {
    $el: null as HTMLElement | null,
    uploadProfilePhotoMutation: null as MutationState<TutorMutationResponse<string>> | null,
    removeProfilePhotoMutation: null as MutationState<TutorMutationResponse<string>> | null,
    updateProfileMutation: null as MutationState<TutorMutationResponse<string>> | null,
    saveSocialProfileMutation: null as MutationState<TutorMutationResponse<string>> | null,
    saveBillingInfoMutation: null as MutationState<TutorMutationResponse<string>> | null,
    saveWithdrawMethodMutation: null as MutationState<TutorMutationResponse<string>> | null,
    resetPasswordMutation: null as MutationState<ResetPasswordResponse> | null,
    handleUpdateNotification: null as MutationState<unknown, unknown> | null,
    savePreferencesMutation: null as MutationState<TutorMutationResponse<PreferencesFormProps>> | null,
    resetPreferencesMutation: null as MutationState<TutorMutationResponse<PreferencesFormProps>> | null,

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
      this.resetToDefault = this.resetToDefault.bind(this);

      this.handleUpdateNotification = query.useMutation(this.updateNotification, {
        onSuccess: (data: TutorMutationResponse<string>, payload: UpdateNotificationProps) => {
          form.reset(payload?.formId as string, payload as unknown as Record<string, unknown>);
          toast.success(data?.message ?? __('Notification settings updated', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error) || __('Failed to update notification settings', 'tutor'));
        },
      });

      this.uploadProfilePhotoMutation = query.useMutation(this.uploadProfilePhoto, {
        onSuccess: () => {
          toast.success(__('Successfully updated profile photo.', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.removeProfilePhotoMutation = query.useMutation(this.removeProfilePhoto, {
        onSuccess: () => {
          toast.success(__('Successfully removed profile photo.', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.updateProfileMutation = query.useMutation(this.updateProfile, {
        onSuccess: (data: TutorMutationResponse<string>) => {
          toast.success(data?.message ?? __('Successfully updated profile', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.saveSocialProfileMutation = query.useMutation(this.saveSocialProfile, {
        onSuccess: (data: TutorMutationResponse<string>) => {
          toast.success(data?.message ?? __('Success successfully saved social profile', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.saveBillingInfoMutation = query.useMutation(this.saveBillingInfo, {
        onSuccess: (data: TutorMutationResponse<string>) => {
          toast.success(data?.message ?? __('Success successfully saved billing info', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.saveWithdrawMethodMutation = query.useMutation(this.saveWithdrawMethod, {
        onSuccess: (data: TutorMutationResponse<string>) => {
          toast.success(data?.message ?? __('Withdrawal method saved successfully', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.resetPasswordMutation = query.useMutation(this.resetPassword, {
        onSuccess: (response: ResetPasswordResponse) => {
          if (response?.success) {
            toast.success(response.data?.message ?? __('Password updated successfully', 'tutor'));
          } else {
            toast.error(response.data?.message ?? __('Password update failed', 'tutor'));
          }
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.savePreferencesMutation = query.useMutation(this.updatePreferences, {
        onSuccess: (data: TutorMutationResponse<PreferencesFormProps>, payload: PreferencesFormProps) => {
          form.reset(payload?.formId || '', payload as unknown as Record<string, unknown>);
          toast.success(data?.message ?? __('Preferences saved successfully', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.resetPreferencesMutation = query.useMutation(this.resetPreferences, {
        onSuccess: (data: TutorMutationResponse<PreferencesFormProps>, payload: ResetPreferencesPayload) => {
          form.reset(payload?.formId || '', (data?.data || {}) as unknown as Record<string, unknown>);
          toast.success(data?.message ?? __('Preferences reset successfully', 'tutor'));
          if (payload?.modalId) {
            modal.closeModal(payload.modalId);
          }
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });
    },

    async updatePreferences(payload: PreferencesFormProps) {
      return wpAjaxInstance.post(endpoints.UPDATE_USER_PREFERENCES, payload) as unknown as Promise<
        TutorMutationResponse<PreferencesFormProps>
      >;
    },

    async resetPreferences(payload: ResetPreferencesPayload) {
      // Endpoint doesn't need payload data; we use payload only to pass form/modal ids to onSuccess.
      void payload;
      return wpAjaxInstance.post(endpoints.RESET_USER_PREFERENCES, {}) as unknown as Promise<
        TutorMutationResponse<PreferencesFormProps>
      >;
    },

    resetToDefault(formId: string, modalId: string) {
      this.resetPreferencesMutation?.mutate({ formId, modalId });
    },

    async updateNotification(payload: UpdateNotificationProps) {
      const transformedPayload = Object.keys(payload).reduce(
        (formattedPayload, key) => {
          const value = payload[key];

          if (typeof value !== 'boolean') {
            formattedPayload[`${key}`] = value;
            return formattedPayload;
          }

          const stringValue = typeof value === 'boolean' ? (value ? 'on' : 'off') : value;

          if (!key.includes('__')) {
            formattedPayload[`tutor_notification_preference[${key}]`] = stringValue;
            return formattedPayload;
          }

          const [firstPart, secondPart] = key.split('__');
          if (!firstPart && !secondPart) {
            return formattedPayload;
          }

          formattedPayload[`tutor_notification_preference[email][${firstPart}][${secondPart}]`] = stringValue;

          return formattedPayload;
        },
        {} as Record<string, string>,
      );

      return wpAjaxInstance.post(endpoints.UPDATE_PROFILE_NOTIFICATION, transformedPayload).then((res) => res.data);
    },

    async uploadProfilePhoto(payload: ProfilePhotoFormProps) {
      return wpAjaxInstance.post(endpoints.UPLOAD_PROFILE_PHOTO, payload).then((res) => res.data);
    },

    async handleUploadProfilePhoto(files: File[]) {
      if (files.length === 0) {
        return;
      }
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

    async handleUpdateProfile(data: AccountFormProps, formId: string) {
      const payload = {
        ...data,
        tutor_pro_custom_signature_id: data.tutor_pro_custom_signature_id?.id || '',
      };
      await this.updateProfileMutation?.mutate(payload);
      form.reset(formId, data as unknown as Record<string, unknown>);
    },

    async saveSocialProfile(payload: SocialFormProps) {
      return wpAjaxInstance.post(endpoints.SAVE_SOCIAL_PROFILE, payload).then((res) => res.data);
    },

    async handleSaveSocialProfile(data: SocialFormProps, formId: string) {
      await this.saveSocialProfileMutation?.mutate(data);
      form.reset(formId, data as unknown as Record<string, unknown>);
    },

    async saveBillingInfo(payload: SettingsFormProps) {
      return wpAjaxInstance.post(endpoints.SAVE_BILLING_INFO, payload).then((res) => res.data);
    },

    async handleSaveBillingInfo(data: SettingsFormProps, formId: string) {
      await this.saveBillingInfoMutation?.mutate(data);
      if (form.hasForm(formId)) {
        form.reset(formId, data as unknown as Record<string, unknown>);
      }
    },

    async saveWithdrawMethod(payload: Record<string, unknown>) {
      return wpAjaxInstance.post(endpoints.SAVE_WITHDRAW_METHOD, payload).then((res) => res.data);
    },

    async handleSaveWithdrawMethod(data: WithdrawMethodFormProps, formId: string) {
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
      form.reset(formId, data as unknown as Record<string, unknown>);
    },

    async resetPassword(payload: ResetPasswordFormProps) {
      return wpAjaxInstance.post(endpoints.RESET_PASSWORD, payload) as unknown as Promise<ResetPasswordResponse>;
    },

    async handleResetPassword(data: ResetPasswordFormProps, formId: string) {
      if (data.new_password !== data.confirm_new_password) {
        toast.error(__('Passwords do not match', 'tutor'));
        return;
      }

      await this.resetPasswordMutation?.mutate(data);
      form.reset(formId, data as unknown as Record<string, unknown>);
      window.location.reload();
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
