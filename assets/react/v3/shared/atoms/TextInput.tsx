import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css, SerializedStyles } from '@emotion/react';
import { parseNumberOnly } from '@Utils/util';
import { FocusEvent, KeyboardEvent, useEffect, useId, useRef } from 'react';

type Variant = 'regular' | 'search';

type TextInputProps = {
  label?: string;
  isInlineLabel?: boolean;
  type?: 'number' | 'text';
  value?: string | number;
  disabled?: boolean;
  readOnly?: boolean;
  placeholder?: string;
  onChange: (value: string) => void;
  onBlur?: (value: string) => void;
  onKeyDown?: (keyName: string, event: KeyboardEvent<HTMLInputElement>) => void;
  onFocus?: (event: FocusEvent<HTMLInputElement>) => void;
  isClearable?: boolean;
  handleMediaIconClick?: () => void;
  variant?: Variant;
  focusOnMount?: boolean;
  inputCss?: SerializedStyles;
};

const TextInput = ({
  label,
  isInlineLabel,
  type = 'text',
  value,
  disabled,
  readOnly,
  placeholder,
  onChange,
  onBlur,
  onKeyDown,
  onFocus,
  isClearable,
  handleMediaIconClick,
  variant = 'regular',
  focusOnMount = false,
  inputCss,
}: TextInputProps) => {
  const id = useId();

  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (!focusOnMount || !inputRef.current) {
      return;
    }

    inputRef.current.focus();
  }, [focusOnMount]);

  return (
    <div css={styles.inputContainer(isInlineLabel)}>
      {!!label && (
        <label htmlFor={id} css={styles.label(isInlineLabel)}>
          {label}
        </label>
      )}

      <div css={styles.inputWrapper}>
        <input
          ref={inputRef}
          id={id}
          type='text'
          css={[styles.input(variant), inputCss]}
          value={value || ''}
          onChange={(event) => {
            const { value } = event.target;

            const fieldValue: string | number = type === 'number' ? parseNumberOnly(value) : value;
            onChange(fieldValue);
          }}
          onKeyDown={(event) => {
            onKeyDown && onKeyDown(event.key, event);
          }}
          onBlur={(event) => {
            const { value } = event.target;

            const fieldValue: string | number = type === 'number' ? parseNumberOnly(value) : value;
            onBlur && onBlur(fieldValue);
          }}
          onFocus={(event) => {
            onFocus && onFocus(event);
          }}
          placeholder={placeholder}
          readOnly={readOnly}
          disabled={disabled}
          autoComplete='off'
        />

        {variant === 'search' && (
          <span css={styles.searchIcon}>
            <SVGIcon name='search' width={20} height={20} />
          </span>
        )}

        {isClearable && !!value && (
          <div css={styles.rightIconButton}>
            <Button variant='text' onClick={() => onChange('')}>
              <SVGIcon name='cross' />
            </Button>
          </div>
        )}

        {!!handleMediaIconClick && variant !== 'search' && !value && (
          <div css={styles.rightIconButton}>
            <Button variant='text' onClick={handleMediaIconClick}>
              <SVGIcon name='upload' />
            </Button>
          </div>
        )}
      </div>
    </div>
  );
};

export default TextInput;

const styles = {
  inputContainer: (isInlineLabel = false) => css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
    width: 100%;

    ${isInlineLabel &&
    css`
      flex-direction: row;
      align-items: center;
      gap: ${spacing[12]};
      justify-content: space-between;
      height: 32px;
    `}
  `,
  label: (isInlineLabel = false) => css`
    ${typography.caption()}

    ${isInlineLabel &&
    css`
      color: ${colorPalate.text.default};
    `}
  `,
  inputWrapper: css`
    position: relative;
  `,
  input: (variant: Variant) => css`
    ${typography.body()}

    width: 100%;
    height: 32px;
    border-radius: ${borderRadius[5]};
    border: 1px solid ${colorTokens.stroke.neutral};
    box-shadow: ${shadow.input};
    padding: 0 ${spacing[32]} 0 ${spacing[12]};
    color: ${colorPalate.text.default};
    appearance: textfield;

    :focus {
      outline: none;
      box-shadow: none;
    }

    ::-webkit-outer-spin-button,
    ::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    ::placeholder {
      color: ${colorTokens.text.hints};
    }

    ${variant === 'search' &&
    css`
      padding-left: ${spacing[36]};
    `}
  `,
  rightIconButton: css`
    position: absolute;
    right: 0;
    top: 0;
    button {
      padding: ${spacing[8]};
    }
  `,
  searchIcon: css`
    position: absolute;
    top: 50%;
    left: ${spacing[8]};
    transform: translateY(-50%);
    color: ${colorPalate.icon.default};
    line-height: 0;
  `,
};
