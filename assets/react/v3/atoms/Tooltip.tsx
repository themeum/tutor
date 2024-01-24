import { borderRadius, colorPalate, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { AnimatedDiv } from '@Hooks/useAnimation';
import { useSpring } from '@react-spring/web';
import Tippy from '@tippyjs/react/headless';
import { AnyObject } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import React, { ReactNode } from 'react';

interface TooltipProps {
  children: ReactNode;
  content: string | ReactNode;
  allowHTML?: boolean;
}

const initialStyles = { opacity: 0, transform: 'scale(0.8)' };
const config = { tension: 300, friction: 15 };

const Tooltip = ({ children, content, allowHTML }: TooltipProps) => {
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
          <AnimatedDiv style={props} {...attributes} css={styles.contentBox}>
            {content}
          </AnimatedDiv>
        );
      }}
      animation
      onMount={onMount}
      onHide={onHide}
      allowHTML={allowHTML}
      delay={[0, 100]}
    >
      <button type="button" css={styles.button}>
        {children}
      </button>
    </Tippy>
  );
};

export default Tooltip;

const styles = {
  contentBox: css`
    max-width: 200px;
    width: 100%;
    background-color: ${colorPalate.surface.default};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};
    padding: ${spacing[8]} ${spacing[10]};
    ${typography.body()};
  `,
  button: css`
    ${styleUtils.resetButton};
    line-height: 1;
  `,
};
