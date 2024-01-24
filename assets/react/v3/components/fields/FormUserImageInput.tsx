import Button, { ButtonVariant } from '@Atoms/Button';
import { useToast } from '@Atoms/Toast';
import { MAX_FILE_SIZE } from '@Config/constants';
import { borderRadius, colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { useTranslation } from '@Hooks/useTranslation';
import customerAvatar from '@Public/images/avatar.png';
import { FormControllerProps } from '@Utils/form';
import { isDefined, isString } from '@Utils/types';
import { formatBytes } from '@Utils/util';
import { useEffect, useRef, useState } from 'react';

import FormFieldWrapper from './FormFieldWrapper';

interface FormUserImageInputProps extends FormControllerProps<string | File | null> {
  label?: string;
  disabled?: boolean;
  loading?: boolean;
  onChange?: (value: File | null) => void;
}

const ACCEPTED_FILE_FORMATS = ['.gif', '.jpg', '.png', '.jpeg'];

const FormUserImageInput = ({ label, field, fieldState, disabled, loading, onChange }: FormUserImageInputProps) => {
  const t = useTranslation();
  const { showToast } = useToast();
  const fileInputRef = useRef<HTMLInputElement>(null);

  const [imagePreview, setImagePreview] = useState<string | null>(null);

  useEffect(() => {
    if (!isDefined(field.value)) {
      setImagePreview(null);
      return;
    }

    if (isString(field.value)) {
      setImagePreview(field.value);
    } else {
      setImagePreview(URL.createObjectURL(field.value));
    }
  }, [field.value]);

  return (
    <FormFieldWrapper label={label} field={field} fieldState={fieldState} disabled={disabled} loading={loading}>
      {(inputProps) => {
        return (
          <div css={styles.wrapper}>
            <div css={styles.imageCard}>
              <img src={imagePreview ? imagePreview : customerAvatar} alt={t('COM_SPPAGEBUILDER_STORE_USER_AVATAR')} />
            </div>

            <div>
              <div css={styles.buttonWrapper}>
                <Button
                  variant={ButtonVariant.secondary}
                  onClick={() => fileInputRef.current?.click()}
                  disabled={disabled}
                >
                  {t('COM_SPPAGEBUILDER_STORE_SELECT_FILE')}
                  <input
                    {...inputProps}
                    ref={fileInputRef}
                    type="file"
                    css={styles.fileInput}
                    accept={ACCEPTED_FILE_FORMATS.join(',')}
                    onChange={(event) => {
                      if (!isDefined(event.target.files)) {
                        return;
                      }

                      const file = event.target.files[0];

                      if (file.size > MAX_FILE_SIZE) {
                        showToast({
                          message: t('COM_SPPAGEBUILDER_STORE_IMAGE_UPLOAD_ERROR_SIZE', {
                            name: file.name,
                            size: formatBytes(MAX_FILE_SIZE),
                          }),
                          type: 'danger',
                        });
                        return;
                      }

                      field.onChange(file);

                      if (onChange) {
                        onChange(file);
                      }
                    }}
                    disabled={disabled}
                  />
                </Button>
                {isString(field.value) ? (
                  field.value.length > 0 && (
                    <Button
                      variant={ButtonVariant.plainMonochrome}
                      onClick={() => {
                        field.onChange(null);

                        if (onChange) {
                          onChange(null);
                        }
                      }}
                    >
                      {t('COM_SPPAGEBUILDER_STORE_DELETE')}
                    </Button>
                  )
                ) : (
                  <p css={styles.fileName}>{field.value?.name}</p>
                )}
              </div>
              <p css={styles.imageTypeAndSize}>{t('COM_SPPAGEBUILDER_STORE_USER_AVATAR_TYPE_AND_SIZE')}</p>
            </div>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormUserImageInput;

const styles = {
  wrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[24]};
  `,
  imageCard: css`
    width: 80px;
    height: 80px;
    border: 1px solid ${colorPalate.border.neutral};
    border-radius: ${borderRadius.circle};
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  `,
  buttonWrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
    margin-bottom: ${spacing[12]};
  `,
  fileInput: css`
    display: none;
  `,
  fileName: css`
    ${typography.body()};
    color: ${colorPalate.text.neutral};
  `,
  imageTypeAndSize: css`
    ${typography.body()};
    color: ${colorPalate.text.disabled};
  `,
};
