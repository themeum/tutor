import FormInput from '@TutorShared/components/fields/FormInput';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import type { GenerateTextFieldProps } from '@TutorShared/components/modals/AITextModal';
import { formatOptions, languageOptions, toneOptions } from '@TutorShared/config/magic-ai';
import { spacing } from '@TutorShared/config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, type UseFormReturn } from 'react-hook-form';

export const PromptControls = ({ form }: { form: UseFormReturn<GenerateTextFieldProps> }) => {
  return (
    <div css={controlStyles.wrapper}>
      <Controller
        control={form.control}
        name="characters"
        render={(props) => (
          <FormInput {...props} isMagicAi label={__('Character Limit', __TUTOR_TEXT_DOMAIN__)} type="number" />
        )}
      />
      <Controller
        control={form.control}
        name="language"
        render={(props) => (
          <FormSelectInput
            {...props}
            isMagicAi
            label={__('Language', __TUTOR_TEXT_DOMAIN__)}
            options={languageOptions}
          />
        )}
      />
      <Controller
        control={form.control}
        name="tone"
        render={(props) => (
          <FormSelectInput {...props} isMagicAi options={toneOptions} label={__('Tone', __TUTOR_TEXT_DOMAIN__)} />
        )}
      />
      <Controller
        control={form.control}
        name="format"
        render={(props) => (
          <FormSelectInput {...props} isMagicAi label={__('Format', __TUTOR_TEXT_DOMAIN__)} options={formatOptions} />
        )}
      />
    </div>
  );
};
const controlStyles = {
  wrapper: css`
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: ${spacing[16]};
  `,
};
