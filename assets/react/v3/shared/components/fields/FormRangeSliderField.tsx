import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useDebounce } from '@Hooks/useDebounce';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { noop } from '@Utils/util';
import { css } from '@emotion/react';
import { useEffect, useMemo, useRef, useState } from 'react';
import FormFieldWrapper from './FormFieldWrapper';

interface FormRangeSliderFieldProps extends FormControllerProps<number> {
  label?: string;
  min?: number;
  max?: number;
  isMagicAi?: boolean;
  hasBorder?: boolean;
}

function updateThumbPosition(clientX: number, sliderRef: React.RefObject<HTMLDivElement>, min: number, max: number) {
  if (!sliderRef.current) {
    return 0;
  }
  const rect = sliderRef.current.getBoundingClientRect();
  const sliderWidth = rect.width;
  const offsetX = clientX - rect.left;

  const clampedX = Math.max(0, Math.min(offsetX, sliderWidth));
  const percentage = (clampedX / sliderWidth) * 100;
  const currentValue = Math.floor(min + (percentage / 100) * (max - min));
  return currentValue;
}

const FormRangeSliderField = ({
  field,
  fieldState,
  label,
  min = 0,
  max = 100,
  isMagicAi = false,
  hasBorder = false,
}: FormRangeSliderFieldProps) => {
  const ref = useRef<HTMLInputElement>(null);
  const [value, setValue] = useState<number>(field.value);
  const sliderRef = useRef<HTMLDivElement>(null);
  const thumbRef = useRef<HTMLDivElement>(null);
  const debounceValue = useDebounce(value);

  useEffect(() => {
    field.onChange(debounceValue);
  }, [debounceValue, field.onChange]);

  useEffect(() => {
    let isDragging = false;

    const handleMouseDown = (event: MouseEvent) => {
      if (event.target !== thumbRef.current) {
        return;
      }
      isDragging = true;
      document.body.style.userSelect = 'none';
    };

    const handleMouseMove = (event: MouseEvent) => {
      if (!isDragging || !sliderRef.current) {
        return;
      }

      setValue(updateThumbPosition(event.clientX, sliderRef, min, max));
    };

    const handleMouseUp = () => {
      isDragging = false;
      document.body.style.userSelect = 'auto';
    };

    window.addEventListener('mousedown', handleMouseDown);
    window.addEventListener('mousemove', handleMouseMove);
    window.addEventListener('mouseup', handleMouseUp);

    return () => {
      window.removeEventListener('mousedown', handleMouseDown);
      window.removeEventListener('mousemove', handleMouseMove);
      window.removeEventListener('mouseup', handleMouseUp);
    };
  }, [min, max]);

  const percentage = useMemo(() => {
    return Math.floor(((value - min) / (max - min)) * 100);
  }, [value, min, max]);

  return (
    <FormFieldWrapper field={field} fieldState={fieldState} label={label} isMagicAi={isMagicAi}>
      {() => (
        <div css={styles.wrapper(hasBorder)}>
          <div
            css={styles.track}
            ref={sliderRef}
            onKeyDown={noop}
            onClick={(event) => {
              setValue(updateThumbPosition(event.clientX, sliderRef, min, max));
            }}
          >
            <div css={styles.fill} style={{ width: `${percentage}%` }} />
            <div css={styles.thumb(isMagicAi)} style={{ left: `${percentage}%` }} ref={thumbRef} />
          </div>
          <input
            type="text"
            css={styles.input}
            value={String(value) ?? ''}
            onChange={(event) => {
              setValue(Number(event.target.value));
            }}
            ref={ref}
            onFocus={() => {
              ref.current?.select();
            }}
          />
        </div>
      )}
    </FormFieldWrapper>
  );
};

export default FormRangeSliderField;
const styles = {
  wrapper: (hasBorder: boolean) => css`
		display: grid;
		grid-template-columns: 1fr 45px;
		gap: ${spacing[20]};
		align-items: center;
		${
      hasBorder &&
      css`
			border: 1px solid ${colorTokens.stroke.disable};
			border-radius: ${borderRadius[6]};
			padding: ${spacing[12]} ${spacing[10]} ${spacing[12]} ${spacing[16]};
		`
    }
	`,
  track: css`
		position: relative;
		height: 4px;
		background-color: ${colorTokens.bg.gray20};
		border-radius: ${borderRadius[50]};
		width: 100%;
		flex-shrink: 0;
    cursor: pointer;
	`,
  fill: css`
		position: absolute;
		left: 0;
		top: 0;
		height: 100%;
		background: linear-gradient(73.09deg, #FF9645 18.05%, #FF6471 30.25%, #CF6EBD 55.42%, #A477D1 71.66%, #3E64DE 97.9%);
		width: 50%;
		border-radius: ${borderRadius[50]};
	`,
  thumb: (isMagicAi: boolean) => css`
		position: absolute;
		top: 50%;
		transform: translate(-50%, -50%);
		width: 20px;
		height: 20px;
		border-radius: ${borderRadius.circle};

		&::before {
			content: '';
			position: absolute;
			top: 50%;
			left: 50%;
			width: 8px;
			height: 8px;
			transform: translate(-50%, -50%);
			border-radius: ${borderRadius.circle};
			background-color: ${colorTokens.background.white};
			cursor: pointer;
		}

		${
      isMagicAi &&
      css`
			background: linear-gradient(73.09deg, #FF9645 18.05%, #FF6471 30.25%, #CF6EBD 55.42%, #A477D1 71.66%, #3E64DE 97.9%);
		`
    }
	`,
  input: css`
		${typography.caption('medium')};
		height: 32px;
		border: 1px solid ${colorTokens.stroke.border};
		border-radius: ${borderRadius[6]};
		text-align: center;
		color: ${colorTokens.text.primary};

		&:focus-visible {
			${styleUtils.inputFocus};
		}
	`,
};
