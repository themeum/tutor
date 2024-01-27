import { colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css, SerializedStyles } from '@emotion/react';
import { nanoid } from '@Utils/util';
import React, { ChangeEventHandler, FocusEventHandler, ReactNode } from 'react';

interface RadioProps {
  id?: string;
  label?: string;
  icon?: ReactNode;
  value?: string | number;
  name?: string;
  checked?: boolean;
  readOnly?: boolean;
  disabled?: boolean;
  labelCss?: SerializedStyles;
  inputCss?: SerializedStyles[];
  onChange?: ChangeEventHandler<HTMLInputElement> | undefined;
  onBlur?: FocusEventHandler<HTMLInputElement> | undefined;
}

const Radio = React.forwardRef<HTMLInputElement, RadioProps>((props: RadioProps, ref) => {
  const { name, checked, readOnly, disabled = false, labelCss, inputCss, label, icon, value, onChange, onBlur } = props;
  const id = nanoid();

  return (
    <label htmlFor={id} css={[styles.container(disabled), labelCss]}>
      <input
        ref={ref}
        id={id}
        name={name}
        type="radio"
        checked={checked}
        readOnly={readOnly}
        value={value}
        disabled={disabled}
        onChange={onChange}
        onBlur={onBlur}
        css={[styles.radio(label), inputCss]}
      />
      <span />
      {icon}
      {label}
    </label>
  );
});

const styles = {
  container: (disabled: boolean) => css`
    ${typography.body()};
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;

    ${disabled &&
    css`
      color: ${colorPalate.text.disabled};
    `}
  `,
  radio: (label = '') => css`
    position: absolute;
    opacity: 0;
    height: 0;
    width: 0;
    cursor: pointer;

    & + span {
      position: relative;
      cursor: pointer;
      height: 15px;
      width: 15px;
      background-color: ${colorPalate.basic.white};
      border: 2px solid ${colorPalate.icon.disabled};
      border-radius: 100%;
      ${label &&
      css`
        margin-right: ${spacing[10]};
      `}
    }
    & + span::before {
      content: '';
      position: absolute;
      left: ${spacing[2]};
      top: ${spacing[2]};
      background-color: ${colorPalate.basic.white};
      width: 7px;
      height: 7px;
      border-radius: 100%;
    }
    &:checked + span {
      border-color: ${colorPalate.basic.primary.default};
    }
    &:checked + span::before {
      background-color: ${colorPalate.basic.primary.default};
    }
  `,
};

export default Radio;
