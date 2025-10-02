import { type SettingsField } from '@Settings/contexts/SettingsContext';
import Button from '@TutorShared/atoms/Button';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { css } from '@emotion/react';
import React, { useRef, useState } from 'react';
import { fieldStyles } from './fieldStyles';

interface UploadFieldProps {
  field: SettingsField;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  value: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onChange: (value: any) => void;
}

const styles = {
  container: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,

  uploadArea: css`
    display: flex;
    align-items: center;
    gap: ${spacing[16]};
    padding: ${spacing[16]};
    border: 2px dashed ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    background-color: ${colorTokens.color.black[2]};
    transition: all 0.2s ease;

    &:hover {
      border-color: ${colorTokens.stroke.brand};
      background-color: ${colorTokens.color.black[3]};
    }
  `,

  preview: css`
    width: 80px;
    height: 80px;
    border-radius: ${borderRadius[8]};
    object-fit: cover;
    border: 1px solid ${colorTokens.stroke.default};
  `,

  uploadContent: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
    flex: 1;
  `,

  uploadText: css`
    ${typography.body()};
    color: ${colorTokens.text.title};
    margin: 0;
  `,

  uploadHint: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,

  hiddenInput: css`
    display: none;
  `,

  fileName: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
    margin: 0;
    word-break: break-all;
  `,

  actions: css`
    display: flex;
    gap: ${spacing[8]};
  `,
};

const UploadField: React.FC<UploadFieldProps> = ({ field, value, onChange }) => {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [preview, setPreview] = useState<string | null>(null);
  const [fileName, setFileName] = useState<string>('');

  // Initialize preview if value exists
  React.useEffect(() => {
    if (value && typeof value === 'string') {
      setPreview(value);
      // Extract filename from URL
      const urlParts = value.split('/');
      setFileName(urlParts[urlParts.length - 1]);
    }
  }, [value]);

  const handleFileSelect = () => {
    fileInputRef.current?.click();
  };

  const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    setFileName(file.name);

    // Create preview for images
    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = (e) => {
        const result = e.target?.result as string;
        setPreview(result);
      };
      reader.readAsDataURL(file);
    } else {
      setPreview(null);
    }

    // For now, we'll store the file name as the value
    // In a real implementation, you'd upload the file and get a URL
    onChange(file.name);
  };

  // Use field for accept attribute if provided
  const acceptTypes = field.accept || 'image/*,.pdf,.doc,.docx';

  const handleRemove = () => {
    setPreview(null);
    setFileName('');
    onChange('');
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  const isImage = preview && (preview.startsWith('data:image') || preview.match(/\.(jpg|jpeg|png|gif|webp)$/i));

  return (
    <div css={fieldStyles.fieldRow}>
      <div css={fieldStyles.labelContainer}>
        <label css={fieldStyles.label}>{field.label}</label>
        {field.label_title && <div css={fieldStyles.labelTitle}>{field.label_title}</div>}
        {field.desc && (
          <div css={fieldStyles.description}>
            <div dangerouslySetInnerHTML={{ __html: field.desc }} />
          </div>
        )}
      </div>

      <div css={fieldStyles.inputContainer}>
        <div css={styles.uploadArea}>
          {isImage && <img src={preview} alt="Preview" css={styles.preview} />}

          <div css={styles.uploadContent}>
            <p css={styles.uploadText}>{fileName ? 'File selected' : 'Choose a file to upload'}</p>
            {fileName && <p css={styles.fileName}>{fileName}</p>}
            <p css={styles.uploadHint}>Supported formats: JPG, PNG, GIF, PDF (Max size: 2MB)</p>
          </div>

          <div css={styles.actions}>
            <Button variant="primary" isOutlined={true} size="small" onClick={handleFileSelect}>
              {fileName ? 'Change' : 'Browse'}
            </Button>

            {fileName && (
              <Button variant="text" size="small" onClick={handleRemove}>
                Remove
              </Button>
            )}
          </div>
        </div>

        <input
          ref={fileInputRef}
          type="file"
          css={styles.hiddenInput}
          onChange={handleFileChange}
          accept={acceptTypes}
        />
      </div>
    </div>
  );
};

export default UploadField;
