import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { nanoid } from '@Utils/util';
import { type SerializedStyles, css } from '@emotion/react';
import React, { type ChangeEventHandler, type FocusEventHandler, type ReactNode } from 'react';

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
        css={[styles.radio(label)]}
      />
      <span />
      {icon}
      {label}
    </label>
  );
});

const styles = {
  container: (disabled: boolean) => css`
    ${typography.caption()};
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;

    ${
      disabled &&
      css`
      color: ${colorTokens.text.disable};
    `
    }
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
      height: 18px;
      width: 18px;
      background-color: ${colorTokens.background.white};
      border: 2px solid ${colorTokens.stroke.default};
      border-radius: 100%;
      ${
        label &&
        css`
        margin-right: ${spacing[10]};
      `
      }
    }
    & + span::before {
      content: '';
      position: absolute;
      left: 3px;
      top: 3px;
      background-color: ${colorTokens.background.white};
      width: 8px;
      height: 8px;
      border-radius: 100%;
    }
    &:checked + span {
      border-color: ${colorTokens.action.primary.default};
    }
    &:checked + span::before {
      background-color: ${colorTokens.action.primary.default};
    }
  `,
};

export default Radio;
