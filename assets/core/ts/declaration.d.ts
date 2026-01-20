import { type Alpine as AlpineType } from 'alpinejs';

import { type TutorComponentRegistry } from '@Core/ts/ComponentRegistry';
import { type ToastService } from '@Core/ts/services/Toast';
import { type TutorCore } from '@Core/ts/types';
import { type ToastType } from '@Core/ts/types/toast';

interface WPMediaFrameOptions {
  title?: string;
  button?: { text?: string };
  multiple?: boolean | 'add';
  library?: { type?: string };
}

interface WPMediaState {
  get: (key: string) => WPMediaSelection;
}

interface WPMediaSelection {
  reset: () => void;
  add: (attachment: WPMediaAttachmentModel) => void;
  toJSON: () => WPMediaAttachmentJSON[];
}

interface WPMediaAttachmentJSON {
  id: number;
  title: string;
  filename: string;
  url: string;
  mime: string;
  type: string;
  subtype: string;
  filesizeHumanReadable: string;
  filesizeInBytes: number;
}

interface WPMediaAttachmentModel {
  fetch: () => void;
}

interface WPMediaFrame {
  on: (event: string, callback: () => void) => void;
  off: (event: string, callback: () => void) => void;
  open: () => void;
  close: () => void;
  state: () => WPMediaState;
  $el?: {
    attr: (key: string, value: string) => void;
    parent: () => { parent: () => { remove: () => void } };
  };
}

declare global {
  interface Window {
    Alpine: AlpineType;
    TutorComponentRegistry: typeof TutorComponentRegistry;
    TutorCore: TutorCore & {
      toast?: ToastService;
      security?: {
        escapeHtml: (text: string) => string;
        escapeAttr: (text: string) => string;
      };
      nonce?: {
        getNonceData: (sendKeyValue?: boolean) => Record<string, string> | { key: string; value: string };
      };
    };

    // Legacy functions (deprecated)
    tutor_get_nonce_data: (sendKeyValue?: boolean) => Record<string, string> | { key: string; value: string };
    tutor_toast: (title: string, description?: string, type?: ToastType, autoClose?: boolean) => void;
    tutor_esc_html: (text: string) => string;
    tutor_esc_attr: (text: string) => string;
    defaultErrorMessage: string;

    // WordPress i18n and media
    wp?: {
      i18n?: {
        __?: (text: string, domain: string) => string;
      };

      media?: ((options: WPMediaFrameOptions) => WPMediaFrame) & {
        attachment: (id: number) => WPMediaAttachmentModel;
      };
    };

    // Tutor object from PHP (extend existing type, don't redeclare)
    _tutorobject?: Record<string, unknown> & {
      nonce_key?: string;
      ajaxurl?: string;
      tutor_url?: string;
      wp_date_format?: string;
    };
  }
}

declare const __TUTOR_TEXT_DOMAIN__: string;
