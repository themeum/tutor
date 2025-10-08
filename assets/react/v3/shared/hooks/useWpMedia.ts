import { __, sprintf } from '@wordpress/i18n';
import { useCallback, useEffect, useMemo, useState } from 'react';

import { useToast } from '@TutorShared/atoms/Toast';

export interface WPMedia {
  id: number;
  title: string;
  url: string;
  name?: string;
  size?: string;
  size_bytes?: number;
  ext?: string;
}

export type WPMediaType = 'image' | 'video' | 'audio' | 'document' | string;

export type WPMediaMultipleMode = boolean | 'add';

export interface WPMediaState {
  get: (key: string) => unknown;
}

export interface WPMediaOptions {
  title?: string;
  button?: {
    text?: string;
  };
  multiple?: WPMediaMultipleMode;
  library?: {
    type?: WPMediaType;
    date?: boolean;
    search?: string;
    uploadedTo?: number;
    status?: 'inherit' | 'publish' | 'private' | 'trash';
  };
  maxFiles?: number;
  maxFileSize?: number;
  mime?: string | string[];
  frame?: 'select' | 'post' | 'manage' | 'image';
  state?: string;
  content?: 'upload' | 'insert' | 'browse';

  className?: string;
  priority?: number;
}

export type WPMediaOnChangeCallback = (files: WPMedia[] | WPMedia | null) => void;

export interface WPMediaHookReturn {
  openMediaLibrary: () => void;
  existingFiles: WPMedia[];
  resetFiles: () => void;
}

export interface WPMediaHookOptions extends WPMediaOptions {
  type?: WPMediaType;
  multiple?: boolean;
}

export interface WPMediaAttachment {
  id: number;
  title: string;
  filename: string;
  url: string;
  link: string;
  alt: string;
  author: string;
  description: string;
  caption: string;
  name: string;
  status: string;
  uploadedTo: number;
  date: string;
  modified: string;
  menuOrder: number;
  mime: string;
  type: string;
  subtype: string;
  icon: string;
  filesizeHumanReadable: string;
  filesizeInBytes: number;
  height: number;
  width: number;
}

interface UseWPMediaParams {
  options?: WPMediaHookOptions;
  onChange?: WPMediaOnChangeCallback;
  initialFiles?: WPMedia[] | WPMedia | null;
}

const useWPMedia = ({ options = {}, onChange, initialFiles }: UseWPMediaParams): WPMediaHookReturn => {
  const { showToast } = useToast();
  const normalizedInitialFiles = useMemo(
    () => (initialFiles ? (Array.isArray(initialFiles) ? initialFiles : [initialFiles]) : []),
    [initialFiles],
  );

  const mediaOptions: WPMediaOptions = useMemo(
    () => ({
      ...options,
      ...(options.type ? { library: { type: options.type } } : {}),
      multiple: options.multiple ? (options.multiple === true ? 'add' : options.multiple) : false,
    }),
    [options],
  );

  const [existingFiles, setExistingFiles] = useState<WPMedia[]>(normalizedInitialFiles);

  useEffect(() => {
    if (normalizedInitialFiles && !existingFiles.length) {
      setExistingFiles(normalizedInitialFiles);
    }
  }, [existingFiles, normalizedInitialFiles]);

  const openMediaLibrary = useCallback(() => {
    if (!window.wp?.media) {
      // eslint-disable-next-line no-console
      console.error('WordPress media library is not available');
      return;
    }

    const wpMedia = window.wp.media(mediaOptions);

    wpMedia.on('close', () => {
      if (wpMedia.$el) {
        wpMedia.$el.parent().parent().remove();
      }
    });

    wpMedia.on('open', () => {
      const selection = wpMedia.state().get('selection');

      wpMedia.$el.attr('data-focus-trap', 'true');
      selection.reset();

      existingFiles.forEach((file) => {
        const attachment = window.wp.media.attachment(file.id);
        if (attachment) {
          attachment.fetch();
          selection.add(attachment);
        }
      });
    });

    wpMedia.on('select', () => {
      const selected: WPMediaAttachment[] = wpMedia.state().get('selection').toJSON();

      const selectedIds = new Set(selected.map((file) => file.id));

      const remainingFiles = existingFiles.filter((file) => selectedIds.has(file.id));

      const newFiles = selected.reduce((files, file) => {
        if (remainingFiles.some((existing) => existing.id === file.id)) {
          return files;
        }

        if (mediaOptions.maxFileSize && file.filesizeInBytes > mediaOptions.maxFileSize) {
          showToast({
            // translators: %s is the file title
            message: sprintf(__('%s size exceeds the maximum allowed size', __TUTOR_TEXT_DOMAIN__), file.title),
            type: 'danger',
          });
          return files;
        }

        const newFile: WPMedia = {
          id: file.id,
          title: file.title,
          url: file.url,
          name: file.title,
          size: file.filesizeHumanReadable,
          size_bytes: file.filesizeInBytes,
          ext: file.filename.split('.').pop() || '',
        };

        files.push(newFile);
        return files;
      }, [] as WPMedia[]);

      const updatedFiles = mediaOptions.multiple ? [...remainingFiles, ...newFiles] : newFiles.slice(0, 1);

      if (mediaOptions.maxFiles && updatedFiles.length > mediaOptions.maxFiles) {
        showToast({
          // translators: %d is the maximum number of files allowed
          message: sprintf(__('Cannot select more than %d files', __TUTOR_TEXT_DOMAIN__), mediaOptions.maxFiles),
          type: 'warning',
        });
        return;
      }

      setExistingFiles(updatedFiles);
      onChange?.(mediaOptions.multiple ? updatedFiles : updatedFiles[0] || null);
      wpMedia.close();
    });

    wpMedia.open();
  }, [mediaOptions, onChange, existingFiles, showToast]);

  const resetFiles = useCallback(() => {
    setExistingFiles([]);
    onChange?.(mediaOptions.multiple ? [] : null);
  }, [mediaOptions.multiple, onChange]);

  return {
    openMediaLibrary,
    existingFiles,
    resetFiles,
  };
};

export default useWPMedia;
