import { __, sprintf } from '@wordpress/i18n';

import { type FormControlMethods, type SetValueOptions, type ValidationRules } from '@Core/ts/components/form';
import { type AlpineComponentMeta } from '@Core/ts/types';
import { tutorConfig } from '@TutorShared/config/config';
import { formatBytes } from '@TutorShared/utils/util';

type FileUploaderVariant = 'file-uploader' | 'image-uploader';

export interface FileUploaderProps {
  multiple?: boolean;
  accept?: string;
  maxSize?: number; // in bytes
  onFileSelect?: (files: (File | string)[]) => void;
  onError?: (error: string) => void;
  disabled?: boolean;
  variant?: FileUploaderVariant;
  value?: (File | string)[];
  name?: string;
  required?: boolean | string;
  imagePreviewPlaceholder?: string;
}

const defaultProps = {
  multiple: false,
  accept: '.pdf,.doc,.docx,.jpg,.jpeg,.png',
  maxSize: Number(tutorConfig.max_upload_size),
  variant: 'file-uploader',
  value: [],
  name: '',
  required: false,
  imagePreviewPlaceholder: '',
} satisfies FileUploaderProps;

export const fileUploader = (props: FileUploaderProps = defaultProps) => ({
  isDragOver: false,
  isDisabled: props.disabled,
  multiple: props.multiple,
  accept: props.accept,
  maxSize: props.maxSize || 52428800,
  variant: props.variant,
  imagePreview:
    props.variant === 'image-uploader' && typeof props.value?.[0] === 'string'
      ? props.value[0]
      : props.imagePreviewPlaceholder,
  selectedFiles: props.value || [],
  name: props.name || '',
  required: props.required || false,
  formatBytes,
  $refs: {} as { fileInput: HTMLInputElement },

  init() {
    this.$refs.fileInput.addEventListener('change', (event: Event) => this.handleFileSelect(event));
    this.setupFormIntegration();
  },

  destroy() {
    this.$refs.fileInput.removeEventListener('change', (event: Event) => this.handleFileSelect(event));
  },

  handleDragOver(event: DragEvent) {
    event.preventDefault();
    if (!this.isDisabled) {
      this.isDragOver = true;
    }
  },

  handleDragLeave(event: DragEvent) {
    event.preventDefault();
    this.isDragOver = false;
  },

  handleDrop(event: DragEvent) {
    event.preventDefault();
    this.isDragOver = false;

    if (this.isDisabled) return;

    const files = Array.from(event.dataTransfer?.files || []);
    this.processFiles(files);
  },

  handleFileSelect(event: Event) {
    event.preventDefault();
    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files || []);
    this.processFiles(files);

    // Reset input value to allow selecting the same file again
    try {
      input.value = '';
    } catch {
      // InvalidStateError can occur in certain browser states, safe to ignore
    }
  },

  openFileDialog() {
    if (!this.isDisabled) {
      this.$refs.fileInput.click();
    }
  },

  processFiles(files: (File | string)[]) {
    if (!files.length) {
      return;
    }

    const validFiles: (File | string)[] = [];

    for (const file of files) {
      if (typeof file === 'string') {
        validFiles.push(file);
        continue;
      }

      if (file.size > this.maxSize) {
        this.showError(
          sprintf(
            // translators: %1$s is the file name, %2$s is the maximum allowed size
            __('File %1$s is too large. Maximum allowed size is %2$s.', 'tutor'),
            file.name,
            formatBytes(this.maxSize),
          ),
        );
        continue;
      }

      if (this.accept && !this.isFileTypeAccepted(file)) {
        this.showError(
          sprintf(
            // translators: %s is the file type
            __('File type %s is not allowed', 'tutor'),
            file.type,
          ),
        );
        continue;
      }

      validFiles.push(file);
    }

    if (validFiles.length > 0) {
      if (!this.isFileListChanged(this.selectedFiles, validFiles)) {
        return;
      }

      if (this.multiple) {
        this.selectedFiles = this.mergeFileLists(this.selectedFiles, validFiles);
      } else {
        this.selectedFiles = [validFiles[0]];
      }

      this.updateFormValue();
      this.syncFileInput();

      // If image uploader, generate preview
      if (
        this.variant === 'image-uploader' &&
        this.selectedFiles.length > 0 &&
        typeof this.selectedFiles[0] !== 'string' &&
        this.selectedFiles[0].type.startsWith('image/')
      ) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.imagePreview = e.target?.result as string;
        };
        reader.readAsDataURL(this.selectedFiles[0] as File);
      }

      // Call the onFileSelect callback if provided
      if (props.onFileSelect) {
        props.onFileSelect(this.selectedFiles);
      }
    }
  },

  removeFile(index?: number) {
    if (this.multiple && typeof index === 'number') {
      this.selectedFiles = this.selectedFiles.filter((_, i) => i !== index);
    } else {
      this.selectedFiles = [];
    }

    // Clear preview if no files left
    if (this.selectedFiles.length === 0) {
      this.imagePreview = '';
    }

    // Reset file input and trigger events to notify form of change
    if (this.$refs.fileInput) {
      try {
        // Only reset the value if the input is in a valid state
        this.$refs.fileInput.value = '';
      } catch {
        // Safe to ignore
      }

      // Dispatch events regardless of whether value reset succeeded
      this.$refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
      this.$refs.fileInput.dispatchEvent(new Event('input', { bubbles: true }));
    }

    this.updateFormValue();
    this.syncFileInput();

    if (props.onFileSelect) {
      props.onFileSelect(this.selectedFiles);
    }
  },

  mergeFileLists(previousList: (File | string)[] | null, newList: (File | string)[] | null): (File | string)[] {
    const seen = new Set<string>();
    const result: (File | string)[] = [];

    const add = (files: (File | string)[] | null) => {
      if (!files) return;
      for (const file of Array.from(files)) {
        const key = this.getFileKey(file);
        if (seen.has(key)) continue;
        seen.add(key);
        result.push(file);
      }
    };

    add(previousList);
    add(newList);
    return result;
  },

  isFileListChanged(previousList: (File | string)[] | null, nextList: (File | string)[] | null): boolean {
    if (previousList === nextList) return false;
    if (!previousList || !nextList) return true;
    if (previousList.length !== nextList.length) return true;

    const prevKeys = new Set(Array.from(previousList, (file) => this.getFileKey(file)));
    return nextList.some((file) => !prevKeys.has(this.getFileKey(file)));
  },

  getFileKey(file: File | string): string {
    if (typeof file === 'string') return file;
    return `${file.name}|${file.size}|${file.lastModified}|${file.type}`;
  },

  isFileTypeAccepted(file: File): boolean {
    if (!this.accept) return true;

    const acceptedTypes = this.accept.split(',').map((type) => type.trim().toLowerCase());
    const fileExtension = '.' + file.name.split('.').pop()?.toLowerCase();
    const fileMimeType = file.type.toLowerCase();

    return acceptedTypes.some((acceptedType) => {
      if (acceptedType.startsWith('.')) {
        return acceptedType === fileExtension;
      }
      return acceptedType === fileMimeType || acceptedType === fileMimeType.split('/')[0] + '/*';
    });
  },

  getFormElement(): HTMLElement | null {
    const $el = (this as unknown as { $el: HTMLElement }).$el;
    return $el.closest('form[x-data*="tutorForm"], form[x-data*="form("]');
  },
  setupFormIntegration() {
    if (!this.name) {
      return;
    }

    const formElement = this.getFormElement();
    if (!formElement) {
      return;
    }

    try {
      const alpine = window.Alpine;
      const alpineData = alpine?.$data(formElement) as FormControlMethods & { values: Record<string, unknown> };

      if (!alpineData || typeof alpineData.register !== 'function') {
        return;
      }

      this.registerFormField(alpineData);
      this.syncInitialValue(alpineData);
      this.watchFormChanges();
    } catch (err) {
      // eslint-disable-next-line no-console
      console.warn(
        sprintf(
          // translators: %s is the error message
          __('Failed to integrate with form: %s', 'tutor'),
          err,
        ),
      );
    }
  },

  registerFormField(alpineData: FormControlMethods & { values: Record<string, unknown> }) {
    const rules: ValidationRules = {
      numberOnly: false,
    };

    if (this.required) {
      rules.required = this.required;
    }

    alpineData.register(this.name, rules);
  },

  syncInitialValue(alpineData: FormControlMethods & { values: Record<string, unknown> }) {
    const formValue = alpineData.values?.[this.name];
    const hasFormValue = formValue !== undefined && formValue !== null && formValue !== '';

    if (hasFormValue) {
      this.selectedFiles = Array.isArray(formValue) ? formValue : [formValue];
      this.syncFileInput();
      return;
    }

    this.updateFormValue({
      shouldValidate: false,
      shouldTouch: false,
      shouldDirty: false,
    });
  },

  watchFormChanges() {
    const component = this as unknown as { $watch: (path: string, cb: (val: unknown) => void) => void };

    if (!component.$watch) {
      return;
    }

    component.$watch(`values.${this.name}`, (newVal: unknown) => {
      const normalized = Array.isArray(newVal) ? newVal : ((newVal ? [newVal] : []) as (File | string)[]);

      if (!this.isFileListChanged(this.selectedFiles, normalized)) {
        return;
      }

      this.selectedFiles = normalized;
      this.syncFileInput();

      if (this.variant === 'image-uploader') {
        this.updateImagePreview();
      }
    });
  },

  updateImagePreview() {
    if (this.selectedFiles.length === 0) {
      this.imagePreview = '';
      return;
    }

    const firstFile = this.selectedFiles[0];

    if (typeof firstFile === 'string') {
      this.imagePreview = firstFile;
      return;
    }

    if (firstFile instanceof File && firstFile.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = (e) => {
        this.imagePreview = e.target?.result as string;
      };
      reader.readAsDataURL(firstFile);
    }
  },

  updateFormValue(options?: SetValueOptions) {
    options = options ?? {
      shouldValidate: true,
      shouldTouch: true,
      shouldDirty: true,
    };

    if (!this.name) {
      return;
    }

    const formElement = this.getFormElement();
    if (!formElement) {
      return;
    }

    try {
      const alpineData = window.Alpine?.$data(formElement) as FormControlMethods & { values: Record<string, unknown> };

      if (alpineData && typeof alpineData.setValue === 'function') {
        const value = this.multiple ? this.selectedFiles : this.selectedFiles[0] || null;
        alpineData.setValue(this.name, value, options);
      }
    } catch (err) {
      // eslint-disable-next-line no-console
      console.warn(
        sprintf(
          // translators: %s is the error message
          __('Failed to update form value: %s', 'tutor'),
          err,
        ),
      );
    }
  },

  syncFileInput() {
    if (this.$refs.fileInput) {
      const dt = new DataTransfer();
      this.selectedFiles.forEach((file) => {
        if (file instanceof File) {
          dt.items.add(file);
        }
      });

      try {
        this.$refs.fileInput.files = dt.files;
      } catch {
        // Safe to ignore
      }

      // Dispatch events to notify listeners (including Alpine's x-model if any)
      this.$refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
      this.$refs.fileInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
  },

  showError(message: string) {
    if (props.onError) {
      props.onError(message);
    } else {
      window.TutorCore.toast.error(message);
    }
  },
});

export const fileUploaderMeta: AlpineComponentMeta<FileUploaderProps> = {
  name: 'fileUploader',
  component: fileUploader,
};
