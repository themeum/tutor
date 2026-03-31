import { useCallback } from 'react';
import type { FieldValues } from 'react-hook-form/dist/types';

import type { FormWithGlobalErrorType } from './useFormWithGlobalError';

export const useResetForm = <T extends FieldValues>(form: FormWithGlobalErrorType<T>, defaultValue: T) => {
  const formResetToDefault = useCallback(
    (localDefault?: T) => {
      form.reset.call(null, localDefault ? localDefault : defaultValue);
    },
    [form.reset, defaultValue],
  );

  return {
    formResetToDefault,
  };
};
