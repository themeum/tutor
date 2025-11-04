import { type AlpineComponentMeta } from '@Core/types';

export interface FileUploaderProps {
  multiple?: boolean;
  accept?: string;
  maxSize?: number; // in bytes
  onFileSelect?: (files: File[]) => void;
  onError?: (error: string) => void;
  disabled?: boolean;
}

export const fileUploader = (props: FileUploaderProps = {}) => ({
  isDragOver: false,
  isDisabled: props.disabled || false,
  multiple: props.multiple || false,
  accept: props.accept || '.pdf,.doc,.docx,.jpg,.jpeg,.png',
  maxSize: props.maxSize || 50 * 1024 * 1024, // 50MB default
  $refs: {} as { fileInput: HTMLInputElement },

  init() {
    this.$refs.fileInput.addEventListener('change', (e: Event) => this.handleFileSelect(e));
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
    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files || []);
    this.processFiles(files);
    // Reset input value to allow selecting the same file again
    input.value = '';
  },

  processFiles(files: File[]) {
    if (!files.length) return;

    const validFiles: File[] = [];

    for (const file of files) {
      // Check file size
      if (file.size > this.maxSize) {
        this.showError(`File "${file.name}" is too large. Maximum size is ${this.formatFileSize(this.maxSize)}`);
        continue;
      }

      // Check file type if accept is specified
      if (this.accept && !this.isFileTypeAccepted(file)) {
        this.showError(`File type "${file.type}" is not allowed`);
        continue;
      }

      validFiles.push(file);
    }

    // Call the onFileSelect callback if provided
    if (props.onFileSelect && validFiles.length > 0) {
      props.onFileSelect(validFiles);
    }
  },

  openFileDialog() {
    if (!this.isDisabled) {
      this.$refs.fileInput.click();
    }
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

  formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  },

  showError(message: string) {
    if (props.onError) {
      props.onError(message);
    } else {
      // @TODO: Add toast when it's ready.
      // Fallback to console.error if no error handler provided
      // eslint-disable-next-line no-console
      console.error('File Uploader Error:', message);
    }
  },
});

export const fileUploaderMeta: AlpineComponentMeta<FileUploaderProps> = {
  name: 'fileUploader',
  component: fileUploader,
};
