import { __ } from '@wordpress/i18n';

import { type MutationState } from '@Core/ts/services/Query';
import { type WPMedia } from '@Core/ts/services/WPMedia';
import { type AjaxResponse } from '@Core/ts/types';

interface UserPhotoFormProps {
  photo_file: File;
  photo_type: 'profile_photo' | 'cover_photo';
}

interface UserPhotoUploadResponse {
  status?: string;
}

interface RemoveUserPhotoProps {
  photo_type: UserPhotoFormProps['photo_type'];
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
  profile_photo: string;
  cover_photo: string;
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
  learning_mood: 'modern' | 'kids';
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
  const { query, form, toast, endpoints } = window.TutorCore;
  const { wpPost } = window.TutorCore.api;
  const { convertToErrorMessage } = window.TutorCore.error;

  return {
    $el: null as HTMLElement | null,
    uploadProfilePhotoMutation: null as MutationState<UserPhotoUploadResponse> | null,
    removeProfilePhotoMutation: null as MutationState<AjaxResponse<string>> | null,
    uploadCoverPhotoMutation: null as MutationState<UserPhotoUploadResponse> | null,
    removeCoverPhotoMutation: null as MutationState<AjaxResponse<string>> | null,
    updateProfileMutation: null as MutationState<AjaxResponse<string>> | null,
    saveSocialProfileMutation: null as MutationState<AjaxResponse<string>> | null,
    saveBillingInfoMutation: null as MutationState<AjaxResponse<string>> | null,
    saveWithdrawMethodMutation: null as MutationState<AjaxResponse<string>> | null,
    resetPasswordMutation: null as MutationState<ResetPasswordResponse> | null,
    handleUpdateNotification: null as MutationState<unknown, unknown> | null,
    savePreferencesMutation: null as MutationState<AjaxResponse<PreferencesFormProps>> | null,
    resetPreferencesMutation: null as MutationState<AjaxResponse<PreferencesFormProps>> | null,

    init() {
      if (!this.$el) {
        return;
      }

      this.handleUpdateProfile = this.handleUpdateProfile.bind(this);
      this.handleUploadProfilePhoto = this.handleUploadProfilePhoto.bind(this);
      this.handleRemoveProfilePhoto = this.handleRemoveProfilePhoto.bind(this);
      this.handleUploadCoverPhoto = this.handleUploadCoverPhoto.bind(this);
      this.handleRemoveCoverPhoto = this.handleRemoveCoverPhoto.bind(this);
      this.handleSaveSocialProfile = this.handleSaveSocialProfile.bind(this);
      this.handleSaveBillingInfo = this.handleSaveBillingInfo.bind(this);
      this.handleSaveWithdrawMethod = this.handleSaveWithdrawMethod.bind(this);
      this.handleResetPassword = this.handleResetPassword.bind(this);
      this.handleResetPreferences = this.handleResetPreferences.bind(this);

      this.handleUpdateNotification = query.useMutation(this.updateNotification, {
        onSuccess: (data, payload) => {
          form.reset(payload?.formId as string, payload as unknown as Record<string, unknown>);
          toast.success(data?.message ?? __('Notification settings updated', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error) || __('Failed to update notification settings', 'tutor'));
        },
      });

      this.uploadProfilePhotoMutation = query.useMutation(this.uploadUserPhoto, {
        onSuccess: () => {
          toast.success(__('Successfully updated profile photo.', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.removeProfilePhotoMutation = query.useMutation(this.removeUserPhoto, {
        onSuccess: () => {
          toast.success(__('Successfully removed profile photo.', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.uploadCoverPhotoMutation = query.useMutation(this.uploadUserPhoto, {
        onSuccess: () => {
          toast.success(__('Successfully updated cover photo.', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.removeCoverPhotoMutation = query.useMutation(this.removeUserPhoto, {
        onSuccess: () => {
          toast.success(__('Successfully removed cover photo.', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.updateProfileMutation = query.useMutation(this.updateProfile, {
        onSuccess: (data) => {
          toast.success(data?.message ?? __('Successfully updated profile', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.saveSocialProfileMutation = query.useMutation(this.saveSocialProfile, {
        onSuccess: (data) => {
          toast.success(data?.message ?? __('Success successfully saved social profile', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.saveBillingInfoMutation = query.useMutation(this.saveBillingInfo, {
        onSuccess: (data) => {
          toast.success(data?.message ?? __('Success successfully saved billing info', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.saveWithdrawMethodMutation = query.useMutation(this.saveWithdrawMethod, {
        onSuccess: (data) => {
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
        onSuccess: (data: AjaxResponse<PreferencesFormProps>, payload: PreferencesFormProps) => {
          const learningMoodChanged = Boolean(form.getFormState(payload?.formId || '').dirtyFields.learning_mood);

          form.reset(payload?.formId || '', payload as unknown as Record<string, unknown>);
          toast.success(data?.message ?? __('Preferences saved successfully', 'tutor'));

          if (learningMoodChanged) {
            window.location.reload();
          }
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.resetPreferencesMutation = query.useMutation(this.resetPreferences, {
        onSuccess: (data: AjaxResponse<PreferencesFormProps>) => {
          toast.success(data?.message ?? __('Preferences reset successfully', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });
    },

    async updatePreferences(payload: PreferencesFormProps) {
      return wpPost(endpoints.UPDATE_USER_PREFERENCES, payload) as unknown as Promise<
        AjaxResponse<PreferencesFormProps>
      >;
    },

    async resetPreferences(payload: ResetPreferencesPayload) {
      return wpPost(endpoints.RESET_USER_PREFERENCES, payload) as unknown as Promise<
        AjaxResponse<PreferencesFormProps>
      >;
    },

    handleResetPreferences(formId: string, modalId: string) {
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

          let stringValue = '';

          // disable_all=true means turning everything OFF
          if (key === 'disable_all') {
            stringValue = value ? 'off' : 'on';
          } else {
            stringValue = value ? 'on' : 'off';
          }

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

      return wpPost<AjaxResponse<string>>(endpoints.UPDATE_PROFILE_NOTIFICATION, transformedPayload);
    },

    async uploadUserPhoto(payload: UserPhotoFormProps) {
      const response = await wpPost<UserPhotoUploadResponse>(endpoints.UPLOAD_PROFILE_PHOTO, payload);

      if (response?.status !== 'success') {
        throw new Error(__('Image upload failed. Please try again.', 'tutor'));
      }

      return response;
    },

    async handleUploadProfilePhoto(files: File[]) {
      if (files.length === 0) {
        return;
      }
      const data = {
        photo_file: files[0],
        photo_type: 'profile_photo',
      } satisfies UserPhotoFormProps;
      await this.uploadProfilePhotoMutation?.mutate(data);
    },

    async handleUploadCoverPhoto(files: File[]) {
      if (files.length === 0) {
        return;
      }
      const data = {
        photo_file: files[0],
        photo_type: 'cover_photo',
      } satisfies UserPhotoFormProps;
      await this.uploadCoverPhotoMutation?.mutate(data);
    },

    async removeUserPhoto(payload: RemoveUserPhotoProps) {
      return wpPost<AjaxResponse<string>>(endpoints.REMOVE_PROFILE_PHOTO, payload);
    },

    async handleRemoveProfilePhoto() {
      await this.removeProfilePhotoMutation?.mutate({ photo_type: 'profile_photo' });
    },

    async handleRemoveCoverPhoto() {
      await this.removeCoverPhotoMutation?.mutate({ photo_type: 'cover_photo' });
    },

    async updateProfile(payload: AccountFormProps) {
      return wpPost<AjaxResponse<string>>(endpoints.UPDATE_PROFILE, payload);
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
      return wpPost<AjaxResponse<string>>(endpoints.SAVE_SOCIAL_PROFILE, payload);
    },

    async handleSaveSocialProfile(data: SocialFormProps, formId: string) {
      await this.saveSocialProfileMutation?.mutate(data);
      form.reset(formId, data as unknown as Record<string, unknown>);
    },

    async saveBillingInfo(payload: SettingsFormProps) {
      return wpPost<AjaxResponse<string>>(endpoints.SAVE_BILLING_INFO, payload);
    },

    async handleSaveBillingInfo(data: SettingsFormProps, formId: string) {
      await this.saveBillingInfoMutation?.mutate(data);
      if (form.hasForm(formId)) {
        form.reset(formId, data as unknown as Record<string, unknown>);
      }
    },

    async saveWithdrawMethod(payload: Record<string, unknown>) {
      return wpPost<AjaxResponse<string>>(endpoints.SAVE_WITHDRAW_METHOD, payload);
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
      return wpPost(endpoints.RESET_PASSWORD, payload) as unknown as Promise<ResetPasswordResponse>;
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
