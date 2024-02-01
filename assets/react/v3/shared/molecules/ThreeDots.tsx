import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { AnimationType } from '@Hooks/useAnimation';
import { styleUtils } from '@Utils/style-utils';
import React, { MouseEvent, ReactNode, useRef } from 'react';

import Popover from './Popover';

interface ThreeDotsOptionProps {
  text: string | ReactNode;
  icon?: ReactNode;
  onClick?: (event: MouseEvent<HTMLButtonElement>) => void;
  onClosePopover?: () => void;
  isTrash?: boolean;
}

export const ThreeDotsOption = ({ text, icon, onClick, onClosePopover, isTrash = false }: ThreeDotsOptionProps) => {
  return (
    <button
      type="button"
      css={styles.option(isTrash)}
      onClick={(event) => {
        if (onClick) {
          onClick(event);
        }
        if (onClosePopover) {
          onClosePopover();
        }
      }}
    >
      {icon && icon}
      <span>{text}</span>
    </button>
  );
};

export type ArrowPosition = 'top' | 'bottom' | 'left' | 'right';
interface ThreeDotsProps {
  isOpen: boolean;
  disabled?: boolean;
  onClick: React.MouseEventHandler<HTMLButtonElement> | undefined;
  closePopover: () => void;
  children: ReactNode;
  arrowPosition?: ArrowPosition;
  animationType?: AnimationType;
}

const ThreeDots = ({
  onClick,
  isOpen,
  disabled = false,
  closePopover,
  arrowPosition = 'top',
  children,
  animationType = AnimationType.slideLeft,
}: ThreeDotsProps) => {
  const ref = useRef<HTMLButtonElement>(null);
  return (
    <>
      <Button variant="text" ref={ref} onClick={onClick} buttonCss={styles.button} disabled={disabled}>
        <SVGIcon name="threeDots" width={18} height={18} />
      </Button>
      <Popover
        gap={13}
        maxWidth="148px"
        arrow={arrowPosition}
        triggerRef={ref}
        isOpen={isOpen}
        closePopover={closePopover}
        animationType={animationType}
      >
        <div css={styles.wrapper}>
          {React.Children.map(children, (child) => {
            if (React.isValidElement(child)) {
              const props = {
                onClosePopover: closePopover,
              };

              return React.cloneElement(child, props);
            }

            return child;
          })}
        </div>
      </Popover>
    </>
  );
};

ThreeDots.Option = ThreeDotsOption;
export default ThreeDots;

const styles = {
  wrapper: css`
    padding-block: ${spacing[8]};
  `,
  option: (isTrash: boolean) => css`
    ${styleUtils.resetButton};
    ${typography.body()};
    width: 100%;
    padding: ${spacing[10]} ${spacing[20]};
    transition: background-color 0.3s ease-in-out;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: ${spacing[8]};

    :hover {
      background-color: ${colorPalate.surface.hover};
    }

    ${isTrash &&
    css`
      color: ${colorPalate.text.critical};

      &:hover {
        background-color: ${colorPalate.surface.critical.neutralHover};
      }

      &:active {
        background-color: ${colorPalate.surface.critical.neutralPressed};
      }
    `}
  `,
  button: css`
    width: 32px;
    height: 32px;
    border-radius: ${borderRadius.circle};
    display: flex;
    justify-content: center;
    align-items: center;
    transition: background-color 0.3s ease-in-out;

    :hover {
      background-color: ${colorPalate.surface.selected.default};
    }
  `,
};
