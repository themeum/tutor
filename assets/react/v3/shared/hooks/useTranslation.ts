import { Joomla } from '@Utils/util';
import { useCallback } from 'react';

const parseOptions = (translation: string, options: Record<string, string | number>) => {
  const regex = /\{\{([^(\}\})]+)/gm;
  const matches = translation.matchAll(regex);

  const variableNames = [...matches].map((match) => match[1]).filter((match) => !!match);

  let finalString = translation;

  variableNames.forEach((name) => {
    const nameRegex = new RegExp(`{{${name}}}`);
    finalString = finalString.replace(nameRegex, String(options[name]));
  });

  return finalString;
};

export const useTranslation = () => {
  return useCallback(<Key extends string>(key: Key, options?: Record<string, string | number>) => {
    const translatedValue = Joomla.Text._(key) || key;

    return !!options ? parseOptions(translatedValue, options) : translatedValue;
  }, []);
};

export const translateText = <Key extends string>(key: Key, options?: Record<string, string | number>) => {
  const translatedValue = Joomla.Text._(key) || key;

  return !!options ? parseOptions(translatedValue, options) : translatedValue;
};
