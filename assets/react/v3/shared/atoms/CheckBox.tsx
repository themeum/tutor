import { colorPalate, colorTokens, shadow, spacing } from '@Config/styles';
import { nanoid } from '@Utils/util';
import { type SerializedStyles, css } from '@emotion/react';
import React, { type ChangeEvent, type FocusEventHandler } from 'react';

interface CheckboxProps {
  id?: string;
  checked?: boolean;
  label?: string;
  value?: string;
  name?: string;
  disabled?: boolean;
  'aria-invalid'?: 'true' | 'false';
  labelCss?: SerializedStyles;
  inputCss?: SerializedStyles[];
  onChange?: (checked: boolean, event: ChangeEvent<HTMLInputElement>) => void;
  onBlur?: FocusEventHandler<HTMLInputElement> | undefined;
  isIndeterminate?: boolean;
}

const Checkbox = React.forwardRef<HTMLInputElement, CheckboxProps>((props: CheckboxProps, ref) => {
  const {
    id = nanoid(),
    name,
    labelCss,
    inputCss,
    label = '',
    checked,
    value,
    disabled = false,
    onChange,
    onBlur,
    isIndeterminate = false,
  } = props;

  const handleChange = (event: ChangeEvent<HTMLInputElement>) => {
    onChange?.(!isIndeterminate ? event.target.checked : true, event);
  };

  return (
    <label htmlFor={id} css={[styles.container, labelCss]}>
      <input
        ref={ref}
        id={id}
        name={name}
        type="checkbox"
        value={value}
        checked={!!checked}
        disabled={disabled}
        aria-invalid={props['aria-invalid']}
        onChange={handleChange}
        onBlur={onBlur}
        css={[inputCss, styles.checkbox({ label, isIndeterminate, disabled })]}
      />
      <span />
      {label}
    </label>
  );
});

const styles = {
  container: css`
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
    color: ${colorTokens.text.title};
  `,
  checkbox: ({
    label,
    isIndeterminate,
    disabled,
  }: {
    label: string;
    isIndeterminate: boolean;
    disabled: boolean;
  }) => css`
    position: absolute;
    opacity: 0;
    height: 0;
    width: 0;

    & + span {
      position: relative;
      cursor: pointer;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      ${
        label &&
        css`
        margin-right: ${spacing[10]};
      `
      }
    }
    & + span::before {
      content: '';
      background-color: ${colorPalate.basic.white};
      border: 0.5px solid ${colorPalate.interactive.disabled};
      border-radius: 3px;
      box-shadow: ${shadow.button};

      width: 18px;
      height: 18px;
    }

    &:checked + span::before {
      background-image: url("data:image/svg+xml,%3Csvg width='10' height='10' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M3.99 9.241a.685.685 0 0 0 .6-.317l4.59-7.149a.793.793 0 0 0 .146-.43C9.326 1 9.082.76 8.73.76c-.239 0-.385.088-.532.317L3.965 7.791 1.792 5.003c-.146-.19-.298-.269-.513-.269-.351 0-.605.25-.605.591 0 .152.054.298.18.45l2.53 3.154c.17.215.351.312.605.312Z' fill='%23fff'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-size: 10px 10px;
      background-position: center center;

      background-color: ${colorPalate.basic.primary.default};
      border: 0.5px solid ${colorPalate.basic.surface};

      ${
        disabled &&
        css`
        background-color: ${colorPalate.icon.disabled};
      `
      }
    }

    ${
      isIndeterminate &&
      css`
      & + span::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='2' fill='none'%3E%3Crect width='10' height='1.5' y='.25' fill='%23fff' rx='.75'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-size: 10px;
        background-position: center center;
        background-color: ${colorPalate.basic.primary.default};
        border: 0.5px solid ${colorPalate.basic.surface};
      }
    `
    }
  `,
};

export default Checkbox;
