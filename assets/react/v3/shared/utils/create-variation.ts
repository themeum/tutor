import type { SerializedStyles } from '@emotion/react';
import { isDefined } from './types';
import { getObjectKeys } from './util';

type OmitUndefined<T> = T extends undefined ? never : T;
// biome-ignore lint/suspicious/noExplicitAny: <explanation>
export type VariantProps<Component extends (...args: any) => any> = Omit<
  OmitUndefined<Parameters<Component>[0]>,
  'class' | 'className' | 'css'
>;
type ConfigSchema = Record<string, Record<string, SerializedStyles>>;
export type StringToBoolean<T> = T extends 'true' | 'false' ? boolean : T;
type ConfigVariants<T extends ConfigSchema> = {
  [Variant in keyof T]?: keyof T[Variant] | undefined | null;
};

type Config<T extends ConfigSchema> = {
  variants: T;
  defaultVariants: ConfigVariants<T>;
};

type Props<T extends ConfigSchema> = ConfigVariants<T>;

export const createVariation = <T extends ConfigSchema>(config: Config<T>, base?: SerializedStyles) => {
  return (props: Props<T>): SerializedStyles[] => {
    const { variants, defaultVariants } = config;
    const styles: SerializedStyles[] = [];

    if (isDefined(base)) {
      styles.push(base);
    }

    const variantStyles = getObjectKeys(variants).map((variant) => {
      const variantProp = props[variant];
      const defaultProps = defaultVariants[variant];

      if (variantProp === null) {
        return null;
      }

      const variantKey = (variantProp || defaultProps) as keyof (typeof variants)[typeof variant];
      return variants[variant][variantKey] as SerializedStyles;
    });

    styles.push(...variantStyles.filter(isDefined));
    return styles;
  };
};
