import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { nanoid } from '@Utils/util';
import { type SerializedStyles, css } from '@emotion/react';
import React, { type ChangeEvent } from 'react';

type labelPositionType = 'left' | 'right';

const styles = {
  switchStyles: css`
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
      }

      &:checked {
        background: ${colorTokens.primary.main};
        &:after {
          left: 18px;
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
  labelPosition?: labelPositionType;
  labelCss?: SerializedStyles;
}

const Switch = React.forwardRef<HTMLInputElement, SwitchProps>((props: SwitchProps, ref) => {
  const { id = nanoid(), name, label, value, checked, disabled, onChange, labelPosition = 'left', labelCss } = props;

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
        css={styles.switchStyles}
        onChange={handleChange}
        data-input
      />
    </div>
  );
});

export default Switch;
