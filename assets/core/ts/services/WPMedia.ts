import { __, sprintf } from '@wordpress/i18n';

import { type ServiceMeta } from '@Core/ts/types';

/**
 * Represents a file selected from WordPress media library.
 */
export interface WPMedia {
  id: number;
  title: string;
  url: string;
  name?: string;
  size?: string;
  size_bytes?: number;
  ext?: string;
  mime?: string;
}

export type WPMediaType = 'image' | 'video' | 'audio' | 'application' | string;

/**
 * Configuration options for wp.media modal.
 */
export interface WPMediaOptions {
  title?: string;
  button?: {
    text?: string;
  };
  multiple?: boolean | 'add';
  library?: {
    type?: WPMediaType;
  };
  maxFileSize?: number;
  maxFiles?: number;
}

/**
 * Internal representation of a wp.media attachment object.
 */
export interface WPMediaAttachment {
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

export type WPMediaOnSelectCallback = (files: WPMedia[]) => void;

/**
 * WPMediaService: Provides a clean API for WordPress media library interactions.
 * This service wraps the native wp.media to provide type-safe, consistent behavior.
 */
export class WPMediaService {
  private toastService = window.TutorCore?.toast;

  /**
   * Check if WordPress media library is available.
   */
  isAvailable(): boolean {
    return typeof window.wp?.media === 'function';
  }

  /**
   * Opens the WordPress media library modal.
   * @param options - Configuration for the media modal
   * @param onSelect - Callback invoked when files are selected
   * @param existingIds - Optional array of attachment IDs to pre-select
   * @returns Cleanup function to destroy the modal frame, or null if wp.media unavailable
   */
  open(options: WPMediaOptions, onSelect: WPMediaOnSelectCallback, existingIds: number[] = []): (() => void) | null {
    if (!this.isAvailable()) {
      // eslint-disable-next-line no-console
      console.error(__('WordPress media library is not available', 'tutor'));
      return null;
    }

    const mediaOptions = {
      title: options.title ?? __('Select File', 'tutor'),
      button: {
        text: options.button?.text ?? __('Use this file', 'tutor'),
      },
      multiple: options.multiple ? 'add' : false,
      library: options.library ?? {},
    };

    const wpMedia = window.wp.media(mediaOptions);

    const handleOpen = () => {
      const selection = wpMedia.state().get('selection');
      wpMedia.$el?.attr('data-focus-trap', 'true');
      selection.reset();

      // Pre-select existing attachments
      existingIds.forEach((id) => {
        const attachment = window.wp.media.attachment(id);
        if (attachment) {
          attachment.fetch();
          selection.add(attachment);
        }
      });
    };

    const handleSelect = () => {
      const selected: WPMediaAttachment[] = wpMedia.state().get('selection').toJSON();

      const files = selected.reduce((result, attachment) => {
        // File size validation
        if (options.maxFileSize && attachment.filesizeInBytes > options.maxFileSize) {
          this.toastService?.error(
            sprintf(
              // translators: %s is the file title
              __('%s exceeds the maximum allowed file size', 'tutor'),
              attachment.title,
            ),
          );
          return result;
        }

        const file: WPMedia = {
          id: attachment.id,
          title: attachment.title,
          url: attachment.url,
          name: attachment.filename,
          size: attachment.filesizeHumanReadable,
          size_bytes: attachment.filesizeInBytes,
          ext: attachment.filename.split('.').pop() || '',
          mime: attachment.mime,
        };

        result.push(file);
        return result;
      }, [] as WPMedia[]);

      // Max files validation
      if (options.maxFiles && files.length > options.maxFiles) {
        this.toastService?.warning(
          sprintf(
            // translators: %d is the maximum number of files allowed
            __('Cannot select more than %d files', 'tutor'),
            options.maxFiles,
          ),
        );
        return;
      }

      onSelect(files);
      wpMedia.close();
    };

    const handleClose = () => {
      if (wpMedia.$el) {
        wpMedia.$el.parent().parent().remove();
      }
    };

    wpMedia.on('open', handleOpen);
    wpMedia.on('select', handleSelect);
    wpMedia.on('close', handleClose);

    wpMedia.open();

    // Return cleanup function
    return () => {
      wpMedia.off('open', handleOpen);
      wpMedia.off('select', handleSelect);
      wpMedia.off('close', handleClose);
    };
  }
}

export const wpMediaServiceMeta: ServiceMeta<WPMediaService> = {
  name: 'wpMedia',
  instance: new WPMediaService(),
};
