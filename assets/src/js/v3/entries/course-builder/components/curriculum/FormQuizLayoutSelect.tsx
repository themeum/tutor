import { css } from '@emotion/react';
import { type KeyboardEvent, type ReactNode, useCallback, useMemo, useRef } from 'react';

import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import type { OptionWithImage } from '@TutorShared/utils/types';

type LayoutOption<T> = Omit<OptionWithImage<T>, 'image'> & {
  image: ReactNode;
};

interface FormQuizLayoutSelectProps<T> extends FormControllerProps<T> {
  label: string;
  description?: string;
  options: LayoutOption<T>[];
  disabled?: boolean;
}

const FormQuizLayoutSelect = <T,>({
  field,
  label,
  description,
  options = [],
  disabled = false,
}: FormQuizLayoutSelectProps<T>) => {
  const buttonRefs = useRef<Array<HTMLButtonElement | null>>([]);
  const selectedIndex = useMemo(
    () => options.findIndex((option) => option.value === field.value),
    [field.value, options],
  );
  const focusableIndex = selectedIndex >= 0 ? selectedIndex : 0;

  const getNextEnabledIndex = useCallback(
    (startIndex: number, direction: 1 | -1) => {
      if (!options.length) {
        return -1;
      }

      let nextIndex = startIndex;

      for (let i = 0; i < options.length; i++) {
        nextIndex = (nextIndex + direction + options.length) % options.length;
        if (!options[nextIndex]?.disabled) {
          return nextIndex;
        }
      }

      return -1;
    },
    [options],
  );

  const focusAndSelect = useCallback(
    (index: number) => {
      if (index < 0 || options[index]?.disabled) {
        return;
      }

      field.onChange(options[index].value);
      buttonRefs.current[index]?.focus();
    },
    [field, options],
  );

  const handleKeyDown = useCallback(
    (event: KeyboardEvent<HTMLButtonElement>, index: number) => {
      if (disabled) {
        return;
      }

      if (event.key === ' ' || event.key === 'Enter') {
        event.preventDefault();
        focusAndSelect(index);
        return;
      }

      if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
        event.preventDefault();
        const nextIndex = getNextEnabledIndex(index, 1);
        focusAndSelect(nextIndex);
        return;
      }

      if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
        event.preventDefault();
        const nextIndex = getNextEnabledIndex(index, -1);
        focusAndSelect(nextIndex);
        return;
      }
    },
    [disabled, focusAndSelect, getNextEnabledIndex],
  );

  return (
    <section css={styles.wrapper(disabled)}>
      <div css={styles.content}>
        <div data-title>{label}</div>
        {description && <div data-subtitle>{description}</div>}
      </div>

      <div css={styles.options} role="radiogroup" aria-label={label}>
        {options.map((option, index) => {
          const isActive = field.value === option.value;

          return (
            <button
              type="button"
              key={String(option.value)}
              ref={(element) => {
                buttonRefs.current[index] = element;
              }}
              role="radio"
              aria-checked={isActive}
              tabIndex={index === focusableIndex ? 0 : -1}
              css={styles.item(isActive)}
              onClick={() => field.onChange(option.value)}
              onKeyDown={(event) => handleKeyDown(event, index)}
              disabled={disabled || option.disabled}
            >
              <div css={styles.preview(isActive)} data-preview>
                {option.image}
              </div>
              <span>{option.label}</span>
            </button>
          );
        })}
      </div>
    </section>
  );
};

export default FormQuizLayoutSelect;

const styles = {
  wrapper: (disabled: boolean) => css`
    ${styleUtils.display.flex('row')};
    justify-content: space-between;
    align-items: flex-start;
    gap: ${spacing[16]};
    width: 100%;

    ${disabled &&
    css`
      opacity: 0.5;
    `}

    ${Breakpoint.smallMobile} {
      flex-direction: column;
      align-items: stretch;
    }
  `,
  content: css`
    max-width: 220px;

    [data-title] {
      ${typography.caption('regular')};
      color: ${colorTokens.text.title};
      margin: 0;
    }

    [data-subtitle] {
      ${typography.small()};
      color: ${colorTokens.text.subdued};
      margin-top: ${spacing[2]};
    }
  `,
  options: css`
    ${styleUtils.display.flex('row')};
    gap: ${spacing[12]};

    ${Breakpoint.smallMobile} {
      width: 100%;
      justify-content: space-between;
    }
  `,
  item: (isActive: boolean) => css`
    ${styleUtils.resetButton};
    ${styleUtils.display.flex('column')};
    align-items: center;
    gap: ${spacing[8]};
    cursor: pointer;
    color: ${colorTokens.icon.default};
    transition: color 0.2s ease;

    span {
      ${typography.small('regular')};
      color: ${isActive ? colorTokens.text.title : colorTokens.text.subdued};
      text-align: center;
    }

    &:focus-visible {
      [data-preview] {
        outline: 2px solid ${colorTokens.stroke.brand};
        color: #dae3fa;
      }
    }

    &:hover [data-preview] {
      outline: 2px solid ${isActive ? colorTokens.stroke.brand : '#DAE3FA'};
      color: #dae3fa;
    }
  `,
  preview: (isActive: boolean) => css`
    width: 72px;
    height: 92px;
    border-radius: ${borderRadius[8]};
    outline: 2px solid ${isActive ? colorTokens.stroke.brand : '#DAE3FA'};
    color: ${isActive ? '#DAE3FA' : colorTokens.stroke.default};
    background-color: ${colorTokens.background.white};
    overflow: hidden;
  `,
};
