import { borderRadius, colorTokens, fontSize, lineHeight, spacing } from '@Config/styles';
import { AnimatedDiv } from '@Hooks/useAnimation';
import type { AnyObject } from '@Utils/form';
import { css } from '@emotion/react';
import { useSpring } from '@react-spring/web';
import Tippy from '@tippyjs/react/headless';
import type { ReactNode } from 'react';

type Placement = 'top' | 'right' | 'bottom' | 'left';

interface TooltipProps {
  children: ReactNode;
  content: string | ReactNode;
  allowHTML?: boolean;
  placement?: Placement;
  hideOnClick?: boolean;
  delay?: number;
  disabled?: boolean;
}

const initialStyles = { opacity: 0, transform: 'scale(0.8)' };
const config = { tension: 300, friction: 15 };

const Tooltip = ({
  children,
  content,
  allowHTML,
  placement = 'top',
  hideOnClick,
  delay = 0,
  disabled = false,
}: TooltipProps) => {
  if (disabled) return children;

  const [props, setSpring] = useSpring(() => initialStyles);

  const onMount = () => {
    setSpring.start({
      opacity: 1,
      transform: 'scale(1)',
      config,
    });
  };

  const onHide = ({ unmount }: AnyObject) => {
    setSpring.start({
      ...initialStyles,
      onRest: unmount,
      config: { ...config, clamp: true },
    });
  };

  return (
    <Tippy
      render={(attributes) => {
        return (
          <AnimatedDiv style={props} hideOnOverflow={false} {...attributes} css={styles.contentBox(placement)}>
            {content}
          </AnimatedDiv>
        );
      }}
      animation
      onMount={onMount}
      onHide={onHide}
      allowHTML={allowHTML}
      delay={[delay, 100]}
      hideOnClick={hideOnClick}
      placement={placement}
    >
      <div>{children}</div>
    </Tippy>
  );
};

export default Tooltip;

const styles = {
  contentBox: (placement: Placement) => css`
    max-width: 250px;
    width: 100%;
    background-color: ${colorTokens.color.black.main};
    color: ${colorTokens.text.white};
    border-radius: ${borderRadius[6]};
    padding: ${spacing[4]} ${spacing[8]};
    font-size: ${fontSize[15]};
    line-height: ${lineHeight[20]};
    position: relative;

    &::before {
      content: '';
      height: 8px;
      width: 8px;
      background-color: ${colorTokens.color.black.main};
      position: absolute;
      bottom: -4px;
      left: 50%;
      transform: translateX(-50%) rotate(45deg);

      ${placement === 'right' &&
      css`
        bottom: auto;
        left: -4px;
        top: 50%;
        transform: translateY(-50%) rotate(45deg);
      `}

      ${placement === 'bottom' &&
      css`
        bottom: auto;
        top: -4px;
        left: 50%;
        transform: translateX(-50%) rotate(45deg);
      `}

      ${placement === 'left' &&
      css`
        bottom: auto;
        top: 50%;
        left: auto;
        right: -4px;
        transform: translateY(-50%) rotate(45deg);
      `}
    }
  `,
};
