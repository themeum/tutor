import Button, { type ButtonIconPosition, type ButtonSize, type ButtonVariant } from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { MAX_FILE_SIZE } from '@Config/constants';
import { borderRadius, colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import { getFileExtensionFromName } from '@Utils/util';
import { css } from '@emotion/react';
import type React from 'react';
import { useRef } from 'react';

interface UploaderProps {
	onUpload: (files: File[]) => void;
	onError: (errorMessage: string[]) => void;
	acceptedTypes: string[];
	multiple?: boolean;
	fullWidth?: boolean;
	disabled?: boolean;
}

interface FileUploaderProps extends UploaderProps {
	label: string;
}

interface UseFileUploaderProps {
	acceptedTypes: string[];
	onUpload: (files: File[]) => void;
	onError: (errorMessage: string[]) => void;
}
const useFileUploader = ({ acceptedTypes, onUpload, onError }: UseFileUploaderProps) => {
	const fileInputRef = useRef<HTMLInputElement>(null);

	const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
		const { files } = event.target;

		if (!files) {
			return;
		}

		const errorMessages: string[] = [];
		const validFiles: File[] = [];

		for (const file of [...files]) {
			if (!acceptedTypes.includes(getFileExtensionFromName(file.name))) {
				errorMessages.push('Invalid image type');
			} else if (file.size > MAX_FILE_SIZE) {
				errorMessages.push('Maximum upload size exceeded');
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
}: FileUploaderProps) => {
	const { fileInputRef, handleChange } = useFileUploader({ acceptedTypes, onUpload, onError });

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
    background: ${colorPalate.surface.neutral.default};
    border: 1px dashed ${colorPalate.border.neutral};
    border-radius: ${borderRadius[8]};
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    flex: 1;
    gap: ${spacing[12]}; ;
  `,
	fileInput: css`
    display: none;
  `,
	text: css`
    color: ${colorPalate.interactive.default};
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
	...buttonProps
}: UploadButtonProps) => {
	const { fileInputRef, handleChange } = useFileUploader({ acceptedTypes, onUpload, onError });

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
