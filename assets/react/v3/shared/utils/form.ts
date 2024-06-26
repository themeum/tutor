import type { FormWithGlobalErrorType } from '@Hooks/useFormWithGlobalError';
import { isFileOrBlob } from '@Utils/util';
import type { AxiosResponse, Method } from 'axios';
import type { FocusEvent } from 'react';
import type { ControllerFieldState, Path } from 'react-hook-form';

import { translateBeErrorMessage } from './errors';
import { isAxiosError, isBoolean, isDefined, isNumber, isPrimitivesArray, isString, isStringArray } from './types';

export interface FormControllerProps<ValueType> {
  field: {
    onChange: (value: ValueType) => void;
    onBlur: (e?: FocusEvent) => void;
    value: ValueType;
    name: string;
  };
  fieldState: ControllerFieldState;
}

export interface AnyObject {
  // biome-ignore lint/suspicious/noExplicitAny: <explanation>
  [x: string]: any;
}

export type ErrorResponse = { non_field_errors?: string[] } & AnyObject;

interface MappedErrors {
  fieldErrors?: { [x: string]: string[] };
  nonFieldErrors?: string[];
}

const flattenObject = (obj: AnyObject, base = ''): AnyObject => {
  return Object.keys(obj).reduce<AnyObject>((acc, key) => {
    const value = obj[key];

    if (typeof value === 'object' && !isPrimitivesArray(value) && !isFileOrBlob(value)) {
      return { ...acc, ...flattenObject({ ...value }, `${base}${key}.`) };
    }

    return { ...acc, [`${base}${key}`]: value };
  }, {});
};

export const handleFormErrors = <T extends AnyObject>(err: AxiosResponse<ErrorResponse>, values: T): MappedErrors => {
  const response = err;

  if (response.status === 404 || response.status === 403 || response.status === 500) {
    return {
      nonFieldErrors: ['Unexpected error!'],
    };
  }

  const flatValues = flattenObject(values);
  const flatData = flattenObject(response.data);

  const { non_field_errors, ...responseWithoutNonFieldErrors } = flatData;
  const nonFieldErrors = isStringArray(non_field_errors) ? non_field_errors : [];

  for (const objectKey of Object.keys(responseWithoutNonFieldErrors)) {
    if (!(objectKey in flatValues)) {
      const value = flatData[objectKey];
      if (isStringArray(value)) {
        nonFieldErrors.push(...value);
      }
    }
  }

  return {
    nonFieldErrors: nonFieldErrors.map(translateBeErrorMessage),
    fieldErrors: Object.keys(flatData)
      .filter((objectKey) => objectKey in flatValues)
      .reduce((acc, field) => {
        const errors = flatData[field];
        if (isStringArray(errors)) {
          return { ...acc, [field]: errors.map(translateBeErrorMessage) };
        }
        return acc;
      }, {}),
  };
};

export const mapErrorResponseToForm = <T extends AnyObject>(
  err: unknown,
  form: FormWithGlobalErrorType<T>,
  values: T
) => {
  if (!isAxiosError<ErrorResponse>(err) || !err.response) {
    throw err;
  }

  const { fieldErrors, nonFieldErrors } = handleFormErrors<T>(err.response, values);

  if (nonFieldErrors?.length) {
    form.setSubmitError(nonFieldErrors[0]);
  }

  if (fieldErrors) {
    for (const fieldName of Object.keys(fieldErrors)) {
      const filedError = fieldErrors[fieldName];
      if (filedError.length > 0) {
        form.setError(fieldName as Path<T>, { message: filedError[0] });
      }
    }
  }
};

export const submitHandler = <T extends AnyObject>(
  form: FormWithGlobalErrorType<T>,
  submitFn: (values: T) => Promise<unknown> | undefined
) => {
  return async (values: T) => {
    form.setSubmitError(undefined);
    try {
      await submitFn(values);
    } catch (err) {
      mapErrorResponseToForm(err, form, values);
    }
  };
};

export const convertToFormData = (values: AnyObject, method: Method) => {
  const formData = new FormData();

  for (const key of Object.keys(values)) {
    const value = values[key];

    if (Array.isArray(value)) {
      value.forEach((item, index) => {
        if (isFileOrBlob(item) || isString(item)) {
          formData.append(`${key}[${index}]`, item);
        } else if (isBoolean(item) || isNumber(item)) {
          formData.append(`${key}[${index}]`, item.toString());
        } else if (typeof item === 'object' && item !== null) {
          formData.append(`${key}[${index}]`, JSON.stringify(item));
        } else {
          formData.append(`${key}[${index}]`, item);
        }
      });
    } else {
      if (isFileOrBlob(value) || isString(value)) {
        formData.append(key, value);
      } else if (isBoolean(value)) {
        formData.append(key, value.toString());
      } else if (isNumber(value)) {
        formData.append(key, `${value}`);
      } else if (typeof value === 'object' && value !== null) {
        formData.append(key, JSON.stringify(value));
      } else {
        formData.append(key, value);
      }
    }
  }

  formData.append('_method', method.toUpperCase());

  return formData;
};

export const serializeParams = <T>(params: T) => {
  const serialized: Record<string, unknown> = {};

  for (const key in params) {
    const value = params[key];

    if (!isDefined(value)) {
      serialized[key] = 'null';
    } else if (isBoolean(value)) {
      serialized[key] = value === true ? 'true' : 'false';
    } else {
      serialized[key] = value;
    }
  }

  return serialized;
};
