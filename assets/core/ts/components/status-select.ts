import { type AlpineComponentMeta } from '@Core/ts/types';
import { tutorConfig } from '@TutorShared/config/config';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { __ } from '@wordpress/i18n';

export interface StatusSelectProps {
  selected: string;
  action: string;
  data: Record<string, string>;
  variants: Record<string, string>;
}

export const statusSelect = (props: StatusSelectProps) => {
  return {
    selectedValue: props.selected,
    prevValue: props.selected,
    isLoading: false,
    variants: props.variants,

    get currentVariant() {
      return this.variants[this.selectedValue] || 'default';
    },

    get variantClasses() {
      const classes: Record<string, boolean> = {};
      Object.values(this.variants).forEach((variant) => {
        classes[`tutor-status-select-${variant}`] = variant === this.currentVariant;
      });

      // Handle the default variant if not explicitly in variants list
      if (!Object.values(this.variants).includes('default')) {
        classes['tutor-status-select-default'] = this.currentVariant === 'default';
      }

      return classes;
    },

    async updateStatus() {
      if (this.selectedValue === this.prevValue || !props.action) {
        return;
      }

      this.isLoading = true;

      try {
        const formData = new FormData();

        // Add basic nonce and action
        formData.append(tutorConfig.nonce_key, tutorConfig._tutor_nonce);
        formData.append('action', props.action);
        formData.append('status', this.selectedValue);

        // Add additional data
        for (const [key, value] of Object.entries(props.data)) {
          formData.append(key, value);
        }

        const response = await fetch(tutorConfig.ajaxurl, {
          method: 'POST',
          body: formData,
        });

        const result = await response.json();

        if (result.success) {
          this.prevValue = this.selectedValue;
          window.TutorCore.toast.success(result.data?.message || __('Status updated successfully', 'tutor'));
        } else {
          this.selectedValue = this.prevValue;
          window.TutorCore.toast.error(convertToErrorMessage(result));
        }
      } catch (error) {
        this.selectedValue = this.prevValue;
        // eslint-disable-next-line no-console
        console.error('Status update error:', error);
      } finally {
        this.isLoading = false;
      }
    },
  };
};

export const statusSelectMeta: AlpineComponentMeta<StatusSelectProps> = {
  name: 'statusSelect',
  component: statusSelect,
};
