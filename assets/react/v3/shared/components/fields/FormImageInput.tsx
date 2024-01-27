import { useToast } from '@Atoms/Toast';
import { borderRadius, colorPalate, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { useTranslation } from '@Hooks/useTranslation';
import FileUploader from '@Molecules/FileUploader';
import { useAppConfigQuery } from '@Services/app';
import { FormControllerProps } from '@Utils/form';
import { useEffect, useMemo, useState } from 'react';

import FormFieldWrapper from './FormFieldWrapper';

interface FormImageInputProps extends FormControllerProps<string | File> {
  label?: string;
  disabled?: boolean;
  loading?: boolean;
  onChange?: (value: File) => void;
}

const FormImageInput = ({ label, field, fieldState, disabled, loading, onChange }: FormImageInputProps) => {
  const t = useTranslation();
  const { showToast } = useToast();
  const appConfigQuery = useAppConfigQuery();

  const [imagePreview, setImagePreview] = useState<string | null>(null);

  const acceptedImageTypes = useMemo(() => {
    return appConfigQuery.data?.acceptedImageTypes || [];
  }, [appConfigQuery.data]);

  useEffect(() => {
    if (typeof field.value === 'string') {
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
            {imagePreview && (
              <div css={styles.imageCard}>
                <img src={imagePreview} alt={t('COM_SPPAGEBUILDER_STORE_CATEGORY_IMAGE')} />
              </div>
            )}

            <FileUploader
              onUpload={(files) => {
                field.onChange(files[0]);

                if (onChange) {
                  onChange(files[0]);
                }
              }}
              onError={(errorMessages) => {
                errorMessages.map((message) => showToast({ message, type: 'danger' }));
              }}
              acceptedTypes={acceptedImageTypes}
              label={
                imagePreview
                  ? t('COM_SPPAGEBUILDER_STORE_CHANGE_IMAGE')
                  : t('COM_SPPAGEBUILDER_STORE_PRODUCT_PAGE_ADD_IMAGES')
              }
              disabled={inputProps.disabled}
            />
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormImageInput;

const styles = {
  wrapper: css`
    display: flex;
    min-height: 220px;
    gap: ${spacing[24]};
  `,
  imageCard: css`
    border-radius: ${borderRadius[6]};
    overflow: hidden;
    border: 1px solid ${colorPalate.border.neutral};
    flex: 1;

    & img {
      width: 100%;
    }
  `,
};
