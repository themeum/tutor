import { __ } from '@wordpress/i18n';

import ImageInput from '@Atoms/ImageInput';

import type { FormControllerProps } from '@Utils/form';
import FormFieldWrapper from './FormFieldWrapper';

type MediaSize = {
  url: string;
  width: number;
  height: number;
  orientation?: string;
};

export type Media = {
  id: number;
  url: string;
  title: string;
  date?: string;
  filesizeInBytes?: number;
  subtype?: string;
  duration?: {
    hour: number;
    minute: number;
    second: number;
  };
  sizes?: {
    thumbnail: MediaSize;
    medium: MediaSize;
    large: MediaSize;
    full: MediaSize;
  };
};

type FormImageInputProps = {
  label?: string;
  onChange?: (media: Media | null) => void;
  helpText?: string;
  buttonText?: string;
  infoText?: string;
} & FormControllerProps<Media | null>;

const FormImageInput = ({
  field,
  fieldState,
  label,
  helpText,
  buttonText = __('Upload Media', 'tutor'),
  infoText,
  onChange,
}: FormImageInputProps) => {
  const wpMedia = window.wp.media({
    library: { type: 'image' },
  });

  const fieldValue = field.value;

  const uploadHandler = () => {
    wpMedia.open();
  };

  wpMedia.on('select', () => {
    const attachment = wpMedia.state().get('selection').first().toJSON();
    const { id, url, title } = attachment;

    field.onChange({ id, url, title });

    if (onChange) {
      onChange({ id, url, title });
    }
  });

  const clearHandler = () => {
    field.onChange(null);

    if (onChange) {
      onChange(null);
    }
  };

  return (
    <FormFieldWrapper label={label} field={field} fieldState={fieldState} helpText={helpText}>
      {() => {
        return (
          <div>
            <ImageInput
              value={fieldValue}
              uploadHandler={uploadHandler}
              clearHandler={clearHandler}
              buttonText={buttonText}
              infoText={infoText}
            />
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormImageInput;
