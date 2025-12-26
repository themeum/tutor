import { type SerializedStyles, css } from '@emotion/react';
import { type FocusEvent, type KeyboardEvent, useEffect, useId, useRef } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { parseNumberOnly } from '@TutorShared/utils/util';

type Variant = 'regular' | 'search';
type Size = 'small' | 'regular';

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
  autoFocus?: boolean;
  size?: Size;
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
  autoFocus = false,
  size = 'regular',
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
          type="text"
          css={[styles.input(variant, size), inputCss]}
          value={value || ''}
          autoFocus={autoFocus}
          onChange={(event) => {
            const { value } = event.target;

            const fieldValue: string | number = type === 'number' ? parseNumberOnly(value) : value;
            onChange(fieldValue);
          }}
          onKeyDown={(event) => {
            onKeyDown?.(event.key, event);
          }}
          onBlur={(event) => {
            const { value } = event.target;

            const fieldValue: string | number = type === 'number' ? parseNumberOnly(value) : value;
            onBlur?.(fieldValue);
          }}
          onFocus={(event) => {
            onFocus?.(event);
          }}
          placeholder={placeholder}
          readOnly={readOnly}
          disabled={disabled}
          autoComplete="off"
          data-input
        />

        {variant === 'search' && (
          <span css={styles.searchIcon}>
            <SVGIcon name="search" width={24} height={24} />
          </span>
        )}

        {isClearable && !!value && (
          <div css={styles.rightIconButton}>
            <Button variant="text" size="small" onClick={() => onChange('')}>
              <SVGIcon name="cross" width={24} height={24} />
            </Button>
          </div>
        )}

        {!!handleMediaIconClick && variant !== 'search' && !value && (
          <div css={styles.rightIconButton}>
            <Button variant="text" size="small" onClick={handleMediaIconClick}>
              <SVGIcon name="upload" />
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
      color: ${colorTokens.text.primary};
    `}
  `,
  inputWrapper: css`
    position: relative;
  `,
  input: (variant: Variant, size: Size) => css`
    /** Increasing the css specificity */
    &[data-input] {
      ${typography.body()}

      width: 100%;
      height: 40px;
      min-height: auto;
      border-radius: ${borderRadius[5]};
      border: 1px solid ${colorTokens.stroke.default};
      padding: 0 ${spacing[32]} 0 ${spacing[12]};
      color: ${colorTokens.text.primary};
      appearance: textfield;

      :focus {
        ${styleUtils.inputFocus};
      }

      ::-webkit-outer-spin-button,
      ::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
      }

      ::placeholder {
        color: ${colorTokens.text.subdued};
      }

      ${variant === 'search' &&
      css`
        padding-left: ${spacing[36]};
      `}

      ${size === 'small' &&
      css`
        height: 32px;
        min-height: 32px;
      `}
    }
  `,
  rightIconButton: css`
    position: absolute;
    right: ${spacing[4]};
    top: ${spacing[4]};

    button {
      padding: ${spacing[4]};
      border-radius: ${borderRadius[2]};
    }
  `,
  searchIcon: css`
    position: absolute;
    top: 50%;
    left: ${spacing[8]};
    transform: translateY(-50%);
    color: ${colorTokens.icon.default};
    line-height: 0;
  `,
};
