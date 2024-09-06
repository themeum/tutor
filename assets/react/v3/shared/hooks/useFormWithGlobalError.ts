import { useState } from 'react';
import { type UseFormReturn, useForm } from 'react-hook-form';
import type { FieldValues, UseFormProps } from 'react-hook-form/dist/types';

export type FormWithGlobalErrorType<T extends FieldValues> = UseFormReturn<T> & {
  setSubmitError: (error: string | undefined) => void;
  submitError?: string;
};

export const useFormWithGlobalError = <FormValues extends FieldValues>(
  formOptions?: UseFormProps<FormValues>,
): FormWithGlobalErrorType<FormValues> => {
  const [submitError, setSubmitError] = useState<string>();
  const form = useForm<FormValues>(formOptions);
  return { ...form, submitError, setSubmitError };
};
