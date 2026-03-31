import { __, sprintf } from '@wordpress/i18n';

import { type AlpineComponentMeta } from '@Core/ts/types';

const copyToClipboard = () => {
  return {
    toast: window.TutorCore.toast,
    copied: false,
    timer: null as number | null,
    $el: null as HTMLElement | null,

    async copy(text: string) {
      if (!text || !this.$el) {
        return;
      }

      try {
        if (window.isSecureContext && navigator.clipboard) {
          await navigator.clipboard.writeText(text);
        } else {
          throw new Error(__('Clipboard API is not available', 'tutor'));
        }
      } catch {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-9999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
          document.execCommand('copy');
        } catch (error) {
          this.toast.error(sprintf(__('Failed to copy to clipboard: %s', 'tutor'), error));
          return;
        }
        document.body.removeChild(textArea);
      }

      this.toast.success(__('Copied to clipboard', 'tutor'));
      this.copied = true;
      if (this.timer) {
        clearTimeout(this.timer);
      }
      this.timer = window.setTimeout(() => {
        this.copied = false;
      }, 2000);
    },
  };
};

export const copyToClipboardMeta: AlpineComponentMeta = {
  name: 'copyToClipboard',
  component: copyToClipboard,
};
