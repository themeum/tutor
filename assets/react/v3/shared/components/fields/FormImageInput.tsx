import { __ } from '@wordpress/i18n';

import type { Media } from '@Atoms/ImageInput';
import ImageInput from '@Atoms/ImageInput';

import type { FormControllerProps } from '@Utils/form';

import FormFieldWrapper from '@Components/fields/FormFieldWrapper';

type FormImageInputProps = {
  label?: string;
  onChange?: () => void;
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
  });

  const clearHandler = () => {
    field.onChange({ id: null, url: '', title: '' });
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
