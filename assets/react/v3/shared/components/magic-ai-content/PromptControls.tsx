import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import type { GenerateTextFieldProps } from '@Components/modals/AITextModal';
import { formatOptions, languageOptions, toneOptions } from '@Config/magic-ai';
import { spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, type UseFormReturn } from 'react-hook-form';

export const PromptControls = ({ form }: { form: UseFormReturn<GenerateTextFieldProps> }) => {
  return (
    <div css={controlStyles.wrapper}>
      <Controller
        control={form.control}
        name="characters"
        render={(props) => <FormInput {...props} isMagicAi label={__('Character Limit', 'tutor')} type="number" />}
      />
      <Controller
        control={form.control}
        name="language"
        render={(props) => (
          <FormSelectInput {...props} isMagicAi label={__('Language', 'tutor')} options={languageOptions} />
        )}
      />
      <Controller
        control={form.control}
        name="tone"
        render={(props) => <FormSelectInput {...props} isMagicAi options={toneOptions} label={__('Tone', 'tutor')} />}
      />
      <Controller
        control={form.control}
        name="format"
        render={(props) => (
          <FormSelectInput {...props} isMagicAi label={__('Format', 'tutor')} options={formatOptions} />
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
