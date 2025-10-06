import Button, { type ButtonIconPosition, type ButtonSize, type ButtonVariant } from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { tutorConfig } from '@TutorShared/config/config';
import { MAX_FILE_SIZE } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { formatReadAbleBytesToBytes, getFileExtensionFromName } from '@TutorShared/utils/util';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import type React from 'react';
import { useRef } from 'react';

interface UploaderProps {
  onUpload: (files: File[]) => void;
  onError: (errorMessage: string[]) => void;
  acceptedTypes: string[];
  multiple?: boolean;
  fullWidth?: boolean;
  disabled?: boolean;
  maxFileSize?: number; // in bytes
}

interface FileUploaderProps extends UploaderProps {
  label: string;
}

interface UseFileUploaderProps {
  acceptedTypes: string[];
  onUpload: (files: File[]) => void;
  onError: (errorMessage: string[]) => void;
  maxFileSize?: number; // in bytes
}
export const useFileUploader = ({
  acceptedTypes,
  onUpload,
  onError,
  maxFileSize = Number(tutorConfig?.max_upload_size || '') || MAX_FILE_SIZE,
}: UseFileUploaderProps) => {
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { files } = event.target;

    if (!files || files.length === 0) {
      onError([__('No files selected', __TUTOR_TEXT_DOMAIN__)]);
      return;
    }

    const errorMessages: string[] = [];
    const validFiles: File[] = [];

    for (const file of [...files]) {
      if (!acceptedTypes.includes(getFileExtensionFromName(file.name))) {
        errorMessages.push(__('Invalid file type', __TUTOR_TEXT_DOMAIN__));
      } else if (file.size > maxFileSize) {
        errorMessages.push(__('Maximum upload size exceeded', __TUTOR_TEXT_DOMAIN__));
      } else {
        validFiles.push(file);
      }
    }

    if (validFiles.length) {
      onUpload(validFiles);
    }

    if (errorMessages.length) {
      onError(errorMessages);
    }

    event.target.value = '';
  };

  return { fileInputRef, handleChange };
};

const FileUploader = ({
  onUpload,
  onError,
  acceptedTypes,
  label,
  multiple = false,
  disabled = false,
  maxFileSize = formatReadAbleBytesToBytes(tutorConfig?.max_upload_size || '') || MAX_FILE_SIZE,
}: FileUploaderProps) => {
  const { fileInputRef, handleChange } = useFileUploader({ acceptedTypes, onUpload, onError, maxFileSize });

  return (
    <button type="button" css={styles.uploadButton} onClick={() => fileInputRef.current?.click()} disabled={disabled}>
      <input
        ref={fileInputRef}
        type="file"
        css={styles.fileInput}
        accept={acceptedTypes.join(',')}
        onChange={handleChange}
        multiple={multiple}
        disabled={disabled}
      />
      <SVGIcon name="storeImage" width={26} height={20} />
      <span css={styles.text}>{label}</span>
    </button>
  );
};

const styles = {
  uploadButton: css`
    ${styleUtils.resetButton}
    background: ${colorTokens.background.default};
    border: 1px dashed ${colorTokens.stroke.border};
    border-radius: ${borderRadius[8]};
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    flex: 1;
    gap: ${spacing[12]};
  `,
  fileInput: css`
    display: none;
  `,
  text: css`
    color: ${colorTokens.text.subdued};
    ${typography.body()}
  `,
};

interface UploadButtonProps extends UploaderProps {
  children?: React.ReactNode;
  variant?: ButtonVariant;
  type?: 'submit' | 'button';
  size?: ButtonSize;
  icon?: React.ReactNode;
  iconPosition?: ButtonIconPosition;
  disabled?: boolean;
  loading?: boolean;
  tabIndex?: number;
}

export const UploadButton = ({
  onUpload,
  onError,
  acceptedTypes,
  multiple = false,
  disabled = false,
  children,
  maxFileSize = Number(tutorConfig?.max_upload_size || '') || MAX_FILE_SIZE,
  ...buttonProps
}: UploadButtonProps) => {
  const { fileInputRef, handleChange } = useFileUploader({ acceptedTypes, onUpload, onError, maxFileSize });

  return (
    <Button {...buttonProps} onClick={() => fileInputRef.current?.click()} disabled={disabled}>
      <input
        ref={fileInputRef}
        type="file"
        css={styles.fileInput}
        accept={acceptedTypes.join(',')}
        onChange={handleChange}
        multiple={multiple}
        disabled={disabled}
      />
      {children}
    </Button>
  );
};

export default FileUploader;
