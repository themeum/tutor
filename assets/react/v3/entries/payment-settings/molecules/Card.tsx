import { css } from '@emotion/react';
import { animated, useSpring } from '@react-spring/web';
import { type ReactNode, useEffect, useRef, useState } from 'react';

import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorPalate, colorTokens, lineHeight, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

import { isDefined } from '@Utils/types';
import { __ } from '@wordpress/i18n';
import Badge from '../atoms/Badge';

interface CardProps {
  children: ReactNode;
  hasBorder?: boolean;
  title: string | ReactNode;
  titleIcon?: string;
  actionTray?: ReactNode;
  subscription?: boolean;
  collapsed?: boolean;
  noSeparator?: boolean;
}

const Card = ({
  children,
  hasBorder = false,
  title,
  titleIcon,
  actionTray,
  subscription = false,
  collapsed = false,
  noSeparator = false,
}: CardProps) => {
  const [isCollapsed, setIsCollapsed] = useState<boolean>(collapsed);
  const cardRef = useRef<HTMLDivElement>(null);

  const [collapseAnimation, collapseAnimate] = useSpring(
    {
      height: !isCollapsed ? cardRef.current?.scrollHeight : 0,
      opacity: !isCollapsed ? 1 : 0,
      overflow: 'hidden',
      config: {
        duration: 300,
        easing: (t) => t * (2 - t),
      },
    },
    [isCollapsed]
  );

  useEffect(() => {
    if (isDefined(cardRef.current)) {
      collapseAnimate.start({
        height: !isCollapsed ? cardRef.current.scrollHeight : 0,
        opacity: !isCollapsed ? 1 : 0,
      });
    }
  }, [isCollapsed]);

  return (
    <div css={styles.wrapper(hasBorder)}>
      <div css={styles.headerWrapper(isCollapsed || noSeparator)}>
        <h5 css={styles.title}>
          {titleIcon && (
            <span css={styles.titleIcon}>
              <img src={titleIcon} alt={__('Icon', 'tutor')} />
            </span>
          )}
          {title}
          <Show when={subscription}>
            <Badge variant="success">{__('Supports Subscriptions', 'tutor')}</Badge>
          </Show>
        </h5>

        <div css={styles.actions}>
          <Show when={actionTray}>{actionTray}</Show>
          <button
            type="button"
            css={styles.collapseButton({ isCollapsed })}
            onClick={() => setIsCollapsed(!isCollapsed)}
          >
            <SVGIcon name="change" width={24} height={24} />
          </button>
        </div>
      </div>
      <animated.div style={{ ...collapseAnimation }}>
        <div ref={cardRef}>{children}</div>
      </animated.div>
    </div>
  );
};

export default Card;

const styles = {
  wrapper: (hasBorder: boolean) => css`
    width: 100%;
    border-radius: ${borderRadius.card};
    background-color: ${colorPalate.basic.white};
    box-shadow: ${shadow.card};

    ${hasBorder &&
    css`
      box-shadow: none;
      border: 1px solid ${colorTokens.stroke.divider};
    `}
  `,
  headerWrapper: (collapsed: boolean) => css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[8]};
    padding: ${spacing[24]};

    ${!collapsed &&
    css`
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    `}
  `,
  title: css`
    ${typography.body('medium')};
    line-height: ${lineHeight[20]};
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
  `,
  titleIcon: css`
    display: flex;
    img {
      width: 24px;
      height: 24px;
    }
  `,
  collapseButton: ({ isCollapsed }: { isCollapsed: boolean }) => css`
    ${styleUtils.resetButton};
    display: flex;
    align-items: center;
    color: ${colorTokens.icon.brand};
    transition: color 0.3s ease-in-out;

    ${isCollapsed &&
    css`
      color: ${colorTokens.icon.default};
    `}
  `,
  actions: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
  `,
};
