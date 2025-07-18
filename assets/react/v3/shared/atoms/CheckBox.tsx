import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { nanoid } from '@TutorShared/utils/util';
import { css, type SerializedStyles } from '@emotion/react';
import React, { type ChangeEvent, type FocusEventHandler, type ReactNode } from 'react';

interface CheckboxProps {
  id?: string;
  checked?: boolean;
  label?: string | ReactNode;
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

  const extractLabelText = (label: string | ReactNode): string => {
    if (typeof label === 'string') {
      return label;
    }

    if (typeof label === 'number' || typeof label === 'boolean' || label === null) {
      return String(label);
    }

    if (label === undefined) {
      return '';
    }

    if (React.isValidElement(label)) {
      const children = label.props?.children;

      if (typeof children === 'string') {
        return children;
      }

      if (Array.isArray(children)) {
        return children
          .map((child) => (typeof child === 'string' ? child : ''))
          .filter(Boolean)
          .join(' ');
      }
    }

    return '';
  };

  return (
    <label htmlFor={id} css={[styles.container({ disabled }), labelCss]}>
      <input
        {...props}
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
        css={[inputCss, styles.checkbox({ label: !!label, isIndeterminate, disabled })]}
      />
      <span />
      <span css={[styles.label({ isDisabled: disabled }), labelCss]} title={extractLabelText(label)}>
        {label}
      </span>
    </label>
  );
});

const styles = {
  container: ({ disabled = false }) => css`
    position: relative;
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
    color: ${colorTokens.text.title};

    ${disabled &&
    css`
      cursor: not-allowed;
    `}
  `,
  label: ({ isDisabled = false }) => css`
    ${typography.caption()};
    color: ${colorTokens.text.title};

    ${isDisabled &&
    css`
      color: ${colorTokens.text.disable};
    `}
  `,
  checkbox: ({
    label,
    isIndeterminate,
    disabled,
  }: {
    label: boolean;
    isIndeterminate: boolean;
    disabled: boolean;
  }) => css`
    position: absolute;
    opacity: 0 !important;
    height: 0;
    width: 0;

    & + span {
      position: relative;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      ${label &&
      css`
        margin-right: ${spacing[10]};
      `}
    }
    & + span::before {
      content: '';
      background-color: ${colorTokens.background.white};
      border: 1px solid ${colorTokens.stroke.default};
      border-radius: 3px;

      width: 20px;
      height: 20px;
    }

    &:checked + span::before {
      background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIiIGhlaWdodD0iOSIgdmlld0JveD0iMCAwIDEyIDkiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0wLjE2NTM0NCA0Ljg5OTQ2QzAuMTEzMjM1IDQuODQ0OTcgMC4wNzE3MzQ2IDQuNzgxMTUgMC4wNDI5ODg3IDQuNzExM0MtMC4wMTQzMjk2IDQuNTU1NjQgLTAuMDE0MzI5NiA0LjM4NDQ5IDAuMDQyOTg4NyA0LjIyODg0QzAuMDcxMTU0OSA0LjE1ODY4IDAuMTEyNzIzIDQuMDk0NzUgMC4xNjUzNDQgNC4wNDA2OEwxLjAzMzgyIDMuMjAzNkMxLjA4NDkzIDMuMTQzNCAxLjE0ODkgMy4wOTU1NyAxLjIyMDk2IDMuMDYzNjlDMS4yOTAzMiAzLjAzMjEzIDEuMzY1NTQgMy4wMTU2OSAxLjQ0MTY3IDMuMDE1NDRDMS41MjQxOCAzLjAxMzgzIDEuNjA2MDUgMy4wMzAyOSAxLjY4MTU5IDMuMDYzNjlDMS43NTYyNiAzLjA5NzA3IDEuODIzODYgMy4xNDQ1NyAxLjg4MDcxIDMuMjAzNkw0LjUwMDU1IDUuODQyNjhMMTAuMTI0MSAwLjE4ODIwNUMxMC4xNzk0IDAuMTI5NTQ0IDEwLjI0NTQgMC4wODIwNTQyIDEwLjMxODQgMC4wNDgyOTA4QzEwLjM5NDEgMC4wMTU0NjYxIDEwLjQ3NTkgLTAuMDAwOTcyMDU3IDEwLjU1ODMgNC40NDIyOGUtMDVDMTAuNjM1NyAwLjAwMDQ3NTMxOCAxMC43MTIxIDAuMDE3NDc5NSAxMC43ODI0IDAuMDQ5OTI0MkMxMC44NTI3IDAuMDgyMzY4OSAxMC45MTU0IDAuMTI5NTA5IDEwLjk2NjIgMC4xODgyMDVMMTEuODM0NyAxLjAzNzM0QzExLjg4NzMgMS4wOTE0MiAxMS45Mjg4IDEuMTU1MzQgMTEuOTU3IDEuMjI1NUMxMi4wMTQzIDEuMzgxMTYgMTIuMDE0MyAxLjU1MjMxIDExLjk1NyAxLjcwNzk2QzExLjkyODMgMS43Nzc4MSAxMS44ODY4IDEuODQxNjMgMTEuODM0NyAxLjg5NjEzTDQuOTIyOCA4LjgwOTgyQzQuODcxMjkgOC44NzAyMSA0LjgwNzQ3IDguOTE4NzUgNC43MzU2NiA4Ljk1MjE1QzQuNTgyMDIgOS4wMTU5NSA0LjQwOTQ5IDkuMDE1OTUgNC4yNTU4NCA4Ljk1MjE1QzQuMTg0MDQgOC45MTg3NSA0LjEyMDIyIDguODcwMjEgNC4wNjg3MSA4LjgwOTgyTDAuMTY1MzQ0IDQuODk5NDZaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K');
      background-repeat: no-repeat;
      background-size: 10px 10px;
      background-position: center center;
      border-color: transparent;

      background-color: ${colorTokens.icon.brand};
      border-radius: ${borderRadius[4]};

      ${disabled &&
      css`
        background-color: ${colorTokens.icon.disable.default};
      `}
    }

    ${isIndeterminate &&
    css`
      & + span::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='2' fill='none'%3E%3Crect width='10' height='1.5' y='.25' fill='%23fff' rx='.75'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-size: 10px;
        background-position: center center;
        background-color: ${colorTokens.brand.blue};
        border: 0.5px solid ${colorTokens.stroke.white};
      }
    `}

    ${disabled &&
    css`
      & + span {
        cursor: not-allowed;

        &::before {
          border-color: ${colorTokens.stroke.disable};
        }
      }
    `}

    &:focus-visible {
      & + span {
        border-radius: ${borderRadius[2]};
        outline: 2px solid ${colorTokens.stroke.brand};
        outline-offset: 1px;
      }
    }
  `,
};

export default Checkbox;
