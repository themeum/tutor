import type { SerializedStyles } from '@emotion/react';
import { __ } from '@wordpress/i18n';

import ImageInput from '@TutorShared/atoms/ImageInput';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import AIImageModal from '@TutorShared/components/modals/AiImageModal';
import { useModal } from '@TutorShared/components/modals/Modal';
import ProIdentifierModal from '@TutorShared/components/modals/ProIdentifierModal';
import SetupOpenAiModal from '@TutorShared/components/modals/SetupOpenAiModal';

import { tutorConfig } from '@TutorShared/config/config';
import { withVisibilityControl } from '@TutorShared/hoc/withVisibilityControl';
import useWPMedia, { type WPMedia } from '@TutorShared/hooks/useWpMedia';
import type { FormControllerProps } from '@TutorShared/utils/form';

import generateImage2x from '@SharedImages/pro-placeholders/generate-image-2x.webp';
import generateImage from '@SharedImages/pro-placeholders/generate-image.webp';

import FormFieldWrapper from './FormFieldWrapper';

type FormImageInputProps = {
  label?: string;
  size?: 'large' | 'regular' | 'small';
  onChange?: (media: WPMedia | null) => void;
  helpText?: string;
  buttonText?: string;
  infoText?: string;
  generateWithAi?: boolean;
  previewImageCss?: SerializedStyles;
  loading?: boolean;
  onClickAiButton?: () => void;
} & FormControllerProps<WPMedia | null>;

const isTutorPro = !!tutorConfig.tutor_pro_url;
const hasOpenAiAPIKey = tutorConfig.settings?.chatgpt_key_exist;

const FormImageInput = ({
  field,
  fieldState,
  label,
  size,
  helpText,
  buttonText = __('Upload Media', __TUTOR_TEXT_DOMAIN__),
  infoText,
  onChange,
  generateWithAi = false,
  previewImageCss,
  loading,
  onClickAiButton,
}: FormImageInputProps) => {
  const { showModal } = useModal();

  const { openMediaLibrary, resetFiles } = useWPMedia({
    options: {
      type: 'image',
      multiple: false,
    },
    onChange: (file) => {
      if (file && !Array.isArray(file)) {
        const { id, url, title } = file;

        field.onChange({ id, url, title });

        if (onChange) {
          onChange({ id, url, title });
        }
      }
    },
    initialFiles: field.value,
  });

  const fieldValue = field.value;

  const handleMediaButtonClick = () => {
    openMediaLibrary();
  };

  const clearHandler = () => {
    resetFiles();
    field.onChange(null);

    if (onChange) {
      onChange(null);
    }
  };

  const handleAiButtonClick = () => {
    if (!isTutorPro) {
      showModal({
        component: ProIdentifierModal,
        props: {
          image: generateImage,
          image2x: generateImage2x,
        },
      });
    } else if (!hasOpenAiAPIKey) {
      showModal({
        component: SetupOpenAiModal,
        props: {
          image: generateImage,
          image2x: generateImage2x,
        },
      });
    } else {
      showModal({
        component: AIImageModal,
        isMagicAi: true,
        props: {
          title: __('AI Studio', __TUTOR_TEXT_DOMAIN__),
          icon: <SVGIcon name="magicAiColorize" width={24} height={24} />,
          field,
          fieldState,
        },
      });
      onClickAiButton?.();
    }
  };

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      helpText={helpText}
      onClickAiButton={handleAiButtonClick}
      generateWithAi={generateWithAi}
    >
      {() => {
        return (
          <div>
            <ImageInput
              size={size}
              value={fieldValue}
              uploadHandler={handleMediaButtonClick}
              clearHandler={clearHandler}
              buttonText={buttonText}
              infoText={infoText}
              previewImageCss={previewImageCss}
              loading={loading}
            />
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default withVisibilityControl(FormImageInput);
