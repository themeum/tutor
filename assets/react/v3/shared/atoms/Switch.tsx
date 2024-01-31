import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css, SerializedStyles } from '@emotion/react';
import { nanoid } from '@Utils/util';
import React, { ChangeEvent } from 'react';

type labelPositionType = 'left' | 'right';

const styles = {
  switchStyles: css`
    appearance: none;
    border: 0;
    width: 32px;
    height: 20px;
    background: ${colorTokens.color.black[8]};
    box-shadow: inset 0px 0px 0px 1px ${colorTokens.stroke.hover};
    border-radius: ${borderRadius[10]};
    position: relative;
    display: inline-block;
    vertical-align: middle;
    cursor: pointer;
    transition: background-color 0.25s cubic-bezier(0.785, 0.135, 0.15, 0.86);

    &:after {
      content: '';
      position: absolute;
      top: ${spacing[4]};
      left: ${spacing[4]};
      width: 12px;
      height: 12px;
      background: ${colorTokens.color.black[50]};
      border-radius: ${borderRadius.circle};
      transition: 0.25s cubic-bezier(0.785, 0.135, 0.15, 0.86);
    }

    &:checked {
      background: ${colorTokens.color.success[80]};
      box-shadow: inset 0px 0px 0px 1px ${colorTokens.color.success[80]};
      &:after {
        background: ${colorTokens.background.white};
        top: ${spacing[2]};
        left: ${spacing[14]};
        width: 16px;
        height: 16px;
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
