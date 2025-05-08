import { borderRadius, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { nanoid } from '@TutorShared/utils/util';
import { type SerializedStyles, css } from '@emotion/react';
import React, { type ChangeEvent } from 'react';
import LoadingSpinner from './LoadingSpinner';

type labelPositionType = 'left' | 'right';
type SwitchSize = 'large' | 'regular' | 'small';

const styles = {
  switchStyles: (size: SwitchSize) => css`
    /** Increasing the css specificity */
    &[data-input] {
      all: unset;
      appearance: none;
      border: 0;
      width: 40px;
      height: 24px;
      background: ${colorTokens.color.black[10]};
      border-radius: 12px;
      position: relative;
      display: inline-block;
      vertical-align: middle;
      cursor: pointer;
      transition: background-color 0.25s cubic-bezier(0.785, 0.135, 0.15, 0.86);

      ${size === 'small' &&
      css`
        width: 26px;
        height: 16px;
      `}

      &::before {
        display: none !important;
      }

      &:focus {
        border: none;
        outline: none;
        box-shadow: none;
      }

      &:focus-visible {
        outline: 2px solid ${colorTokens.stroke.brand};
        outline-offset: 1px;
      }

      &:after {
        content: '';
        position: absolute;
        top: 3px;
        left: ${spacing[4]};
        width: 18px;
        height: 18px;
        background: ${colorTokens.background.white};
        border-radius: ${borderRadius.circle};
        box-shadow: ${shadow.switch};
        transition: left 0.25s cubic-bezier(0.785, 0.135, 0.15, 0.86);

        ${size === 'small' &&
        css`
          top: 2px;
          left: 3px;
          width: 12px;
          height: 12px;
        `}
      }

      &:checked {
        background: ${colorTokens.primary.main};
        &:after {
          left: 18px;

          ${size === 'small' &&
          css`
            left: 11px;
          `}
        }
      }

      &:disabled {
        pointer-events: none;
        filter: none;
        opacity: 0.5;
      }
    }
  `,

  labelStyles: (isEnabled: boolean) => css`
    ${typography.caption()};
    color: ${isEnabled ? colorTokens.text.title : colorTokens.text.subdued};
  `,

  wrapperStyle: (labelPosition: labelPositionType) => css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: fit-content;
    flex-direction: ${labelPosition === 'left' ? 'row' : 'row-reverse'};
    column-gap: ${spacing[12]};
    position: relative;
  `,
  spinner: (checked: boolean) => css`
    display: flex;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);

    ${checked &&
    css`
      right: 3px;
    `}

    ${!checked &&
    css`
      left: 3px;
    `}
  `,
};

interface SwitchProps {
  id?: string;
  name?: string;
  label?: string;
  value?: boolean;
  checked?: boolean;
  onChange?: (checked: boolean, event: ChangeEvent<HTMLInputElement>) => void;
  disabled?: boolean;
  loading?: boolean;
  labelPosition?: labelPositionType;
  labelCss?: SerializedStyles;
  size?: SwitchSize;
}

const Switch = React.forwardRef<HTMLInputElement, SwitchProps>((props: SwitchProps, ref) => {
  const {
    id = nanoid(),
    name,
    label,
    value,
    checked,
    disabled,
    loading,
    onChange,
    labelPosition = 'left',
    labelCss,
    size = 'regular',
  } = props;

  const handleChange = (event: ChangeEvent<HTMLInputElement>) => {
    onChange?.(event.target.checked, event);
  };

  return (
    <div css={styles.wrapperStyle(labelPosition)}>
      {label && (
        <label css={[styles.labelStyles(checked || false), labelCss]} htmlFor={id}>
          {label}
        </label>
      )}
      <input
        ref={ref}
        value={value ? String(value) : undefined}
        type="checkbox"
        name={name}
        id={id}
        checked={!!checked}
        disabled={disabled}
        css={styles.switchStyles(size)}
        onChange={handleChange}
        data-input
      />
      <Show when={loading}>
        <span css={styles.spinner(!!checked)}>
          <LoadingSpinner size={size === 'small' ? 12 : 20} />
        </span>
      </Show>
    </div>
  );
});

export default Switch;
