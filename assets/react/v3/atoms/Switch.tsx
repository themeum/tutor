import { borderRadius, colorPalate, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css, SerializedStyles } from '@emotion/react';
import { nanoid } from '@Utils/util';
import React, { ChangeEvent } from 'react';

type labelPositionType = 'left' | 'right';

const styles = {
  switchStyles: css`
    appearance: none;
    border: 0;
    width: 28px;
    height: 12px;
    background: ${colorPalate.border.disabled};
    box-shadow: ${shadow.switch};
    border-radius: ${borderRadius[10]};
    position: relative;
    display: inline-block;
    vertical-align: middle;
    cursor: pointer;
    transition: background-color 0.1s cubic-bezier(0.785, 0.135, 0.15, 0.86);

    &:after {
      content: '';
      position: absolute;
      top: -${spacing[2]};
      left: -${spacing[2]};
      width: 16px;
      height: 16px;
      background: ${colorPalate.icon.neutral};
      border-radius: ${borderRadius.circle};
      transition: left 0.1s cubic-bezier(0.785, 0.135, 0.15, 0.86);
    }

    &:checked {
      background: ${colorPalate.surface.selected.pressed};
      &:after {
        background: ${colorPalate.basic.primary.default};
        left: ${spacing[16]};
      }
    }

    &:disabled {
      pointer-events: none;
      filter: none;
      opacity: 0.5;
    }
  `,

  labelStyles: (isEnabled: boolean) => css`
    ${typography.caption()};
    color: ${isEnabled ? colorPalate.text.dark : colorPalate.text.disabled};
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
    onChange && onChange(event.target.checked, event);
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
      />
    </div>
  );
});

export default Switch;
