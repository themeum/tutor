import { useState } from 'react';
import { type FieldValues, type UseFormProps, type UseFormReturn, useForm } from 'react-hook-form';

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
