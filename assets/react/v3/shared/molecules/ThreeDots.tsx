import { type SerializedStyles, css } from '@emotion/react';
import rgba from 'polished/lib/color/rgba';
import React, { type MouseEvent, type ReactNode, useRef } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { POPOVER_PLACEMENTS, type PopoverPlacement } from '@TutorShared/hooks/usePortalPopover';
import { styleUtils } from '@TutorShared/utils/style-utils';

import Popover from './Popover';

interface ThreeDotsOptionProps {
  text: string | ReactNode;
  icon?: ReactNode;
  onClick?: (event: MouseEvent<HTMLButtonElement>) => void;
  onClosePopover?: () => void;
  isTrash?: boolean;
  buttonCss?: SerializedStyles;
  size?: 'small' | 'medium';
  disabled?: boolean;
}

export const ThreeDotsOption = ({
  text,
  icon,
  onClick,
  onClosePopover,
  isTrash = false,
  size = 'medium',
  buttonCss,
  disabled,
  ...props
}: ThreeDotsOptionProps) => {
  return (
    <button
      type="button"
      css={[styles.option({ isTrash: isTrash, size: size }), buttonCss]}
      onClick={(event) => {
        if (onClick) {
          onClick(event);
        }
        if (onClosePopover) {
          onClosePopover();
        }
      }}
      disabled={disabled}
      {...props}
    >
      {icon && icon}
      <span>{text}</span>
    </button>
  );
};

export type DotsOrientation = 'vertical' | 'horizontal';

interface ThreeDotsProps {
  isOpen: boolean;
  disabled?: boolean;
  onClick: React.MouseEventHandler<HTMLButtonElement> | undefined;
  closePopover: () => void;
  children: ReactNode;
  placement?: PopoverPlacement;
  animationType?: AnimationType;
  dotsOrientation?: DotsOrientation;
  maxWidth?: string;
  isInverse?: boolean;
  arrow?: boolean;
  size?: 'small' | 'medium';
  closeOnEscape?: boolean;
  wrapperCss?: SerializedStyles;
}

const ThreeDots = ({
  onClick,
  isOpen,
  disabled = false,
  closePopover,
  placement = POPOVER_PLACEMENTS.BOTTOM_RIGHT,
  children,
  animationType = AnimationType.slideLeft,
  dotsOrientation = 'horizontal',
  maxWidth = '148px',
  isInverse = false,
  arrow = false,
  size = 'medium',
  closeOnEscape = true,
  wrapperCss,
  ...props
}: ThreeDotsProps) => {
  const ref = useRef<HTMLButtonElement>(null);

  return (
    <>
      <button
        type="button"
        ref={ref}
        onClick={onClick}
        css={[styles.button({ isOpen, isInverse, isDisabled: disabled }), wrapperCss]}
        disabled={disabled}
        {...props}
      >
        <SVGIcon name={dotsOrientation === 'horizontal' ? 'threeDots' : 'threeDotsVertical'} width={32} height={32} />
      </button>
      <Popover
        gap={13}
        maxWidth={maxWidth}
        placement={placement}
        triggerRef={ref}
        isOpen={isOpen}
        closePopover={closePopover}
        animationType={animationType}
        arrow={arrow}
        closeOnEscape={closeOnEscape}
      >
        <div css={styles.wrapper({ size })}>
          {React.Children.map(children, (child) => {
            if (React.isValidElement(child)) {
              const props = {
                size,
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
  wrapper: ({ size = 'medium' }: { size: 'small' | 'medium' }) => css`
    padding-block: ${spacing[8]};
    position: relative;

    ${size === 'small' &&
    css`
      padding-block: ${spacing[4]};
    `}
  `,
  option: ({ isTrash = false, size = 'medium' }: { isTrash: boolean; size: 'small' | 'medium' }) => css`
    ${styleUtils.resetButton};
    ${typography.body()};

    width: 100%;
    padding: ${spacing[10]} ${spacing[20]};
    transition: background-color 0.3s ease-in-out;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: ${spacing[8]};

    &:focus,
    &:active,
    &:hover {
      background: none;
      color: ${colorTokens.text.primary};
    }

    svg {
      flex-shrink: 0;
      color: ${colorTokens.icon.default};
    }

    ${size === 'small' &&
    css`
      padding: ${spacing[8]} ${spacing[16]};
      ${typography.small('medium')};
    `}

    :hover:not(:disabled) {
      background-color: ${colorTokens.background.hover};
      color: ${colorTokens.text.title};

      svg {
        color: ${colorTokens.icon.hover};
        filter: grayscale(0%);
      }
    }

    :disabled {
      cursor: not-allowed;
      color: ${colorTokens.text.disable};

      svg {
        color: ${colorTokens.icon.disable.background};
      }
    }

    ${isTrash &&
    css`
      color: ${colorTokens.text.error};
      svg {
        color: ${colorTokens.icon.error};
      }

      &:hover:not(:disabled) {
        color: ${colorTokens.text.error};
        background-color: ${rgba(colorTokens.bg.error, 0.1)};

        svg {
          color: ${colorTokens.icon.error};
        }
      }

      &:active {
        color: ${colorTokens.text.error};
        background-color: ${colorTokens.color.danger[40]};

        svg {
          color: ${colorTokens.icon.error};
        }
      }
    `}

    :focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: -4px;
      border-radius: ${borderRadius.input};
    }
  `,
  button: ({ isOpen = false, isInverse = false, isDisabled = false }) => css`
    ${styleUtils.resetButton};
    width: 32px;
    height: 32px;
    border-radius: ${borderRadius.circle};
    display: flex;
    justify-content: center;
    align-items: center;
    transition: background-color 0.3s ease-in-out;

    svg {
      color: ${colorTokens.icon.default};
      flex-shrink: 0;
    }

    :hover {
      background-color: ${colorTokens.background.hover};

      svg {
        color: ${colorTokens.icon.default};
      }
    }

    &:focus,
    &:active {
      background: none;
    }

    &:focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
    }

    ${isOpen &&
    css`
      background-color: ${colorTokens.background.hover};
      svg {
        color: ${colorTokens.icon.brand};
      }
    `}

    ${isInverse &&
    css`
      background-color: ${colorTokens.background.white};
      :hover {
        background-color: ${colorTokens.background.white};
        svg {
          color: ${!isDisabled && colorTokens.icon.brand};
        }
      }
    `}

    :disabled {
      cursor: not-allowed;
    }
  `,
};
