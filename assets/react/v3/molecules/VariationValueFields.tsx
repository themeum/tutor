import Button, { ButtonVariant } from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css, SerializedStyles } from '@emotion/react';
import { styleUtils } from '@Utils/style-utils';
import { ProductOptionValue } from '@Utils/types';
import produce from 'immer';
import { useState } from 'react';
import { SketchPicker } from 'react-color';

interface VariationValueFieldsProps {
  inputCss?: SerializedStyles[];
  fieldValue: ProductOptionValue;
  onChange: (value: ProductOptionValue) => void;
  onClear: () => void;
  placeholder?: string;
}

export const ListField = ({ inputCss, fieldValue, onChange, onClear, placeholder }: VariationValueFieldsProps) => {
  return (
    <div css={styles.outerContainer}>
      <input
        css={[inputCss]}
        value={fieldValue.name}
        placeholder={placeholder}
        onChange={(event) => {
          if (!event.target.value) {
            return onClear();
          }

          onChange(
            produce(fieldValue, (draft) => {
              draft.name = event.target.value;
            }),
          );
        }}
        autoComplete="off"
      />

      {fieldValue.name && (
        <div css={styles.clearButton}>
          <Button
            variant={ButtonVariant.plain}
            onClick={() => {
              onClear();
            }}
            tabIndex={-1}
          >
            <SVGIcon name="timesAlt" width={14} height={14} />
          </Button>
        </div>
      )}
    </div>
  );
};

export const ColorField = ({ inputCss, fieldValue, onChange, onClear, placeholder }: VariationValueFieldsProps) => {
  const [isColorPickerOpen, setIsColorPickerOpen] = useState(false);
  return (
    <div css={styles.outerContainer}>
      <div css={styles.colorPickerContainer}>
        <button
          type="button"
          css={styles.colorPickerPlaceholder(fieldValue.color)}
          onClick={() => setIsColorPickerOpen(true)}
          tabIndex={-1}
        />
        {isColorPickerOpen && (
          <>
            <button type="button" onClick={() => setIsColorPickerOpen(false)} css={styles.backdropStyle}></button>
            <SketchPicker
              color={fieldValue.color}
              onChange={(color) => {
                onChange(
                  produce(fieldValue, (draft) => {
                    draft.color = color.hex;
                  }),
                );
              }}
            />
          </>
        )}
      </div>

      <input
        type="text"
        value={fieldValue.name}
        placeholder={placeholder}
        css={[inputCss, styles.input]}
        onChange={(event) => {
          const { value } = event.target;
          onChange(
            produce(fieldValue, (draft) => {
              draft.name = value;
            }),
          );

          if (!value) {
            onClear();
          }
        }}
        autoComplete="off"
      />

      {(fieldValue.color || fieldValue.name) && (
        <div css={styles.clearButton}>
          <Button
            variant={ButtonVariant.plain}
            onClick={() => {
              onClear();
            }}
            tabIndex={-1}
          >
            <SVGIcon name="timesAlt" width={14} height={14} />
          </Button>
        </div>
      )}
    </div>
  );
};

const styles = {
  outerContainer: css`
    position: relative;
    display: flex;
  `,
  input: css`
    ${typography.body()}
    width: 100%;
    padding: 0 ${spacing[36]};

    :hover {
      & ~ div {
        visibility: visible;
      }
    }
  `,

  colorPickerContainer: css`
    position: absolute;
    left: ${spacing[12]};
    top: 0;
    height: 100%;
    display: flex;
    align-items: center;
    z-index: ${zIndex.positive};
  `,
  colorPickerPlaceholder: (backgroundColor = 'transparent') => css`
    height: 16px;
    width: 16px;
    background: ${backgroundColor};
    border: 1px solid ${colorPalate.border.disabled};
    border-radius: ${borderRadius.circle};
    outline: none;
  `,
  clearButton: css`
    position: absolute;
    top: 50%;
    right: ${spacing[4]};
    transform: translateY(-50%);
    width: 26px;
    height: 26px;
    border-radius: ${borderRadius[2]};
    visibility: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: ${colorPalate.surface.hover};

    :hover {
      visibility: visible;
    }
  `,
  backdropStyle: css`
    ${styleUtils.resetButton}
    position: fixed;
    inset: 0;
    background: transparent;
  `,
};
