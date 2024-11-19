import { css } from '@emotion/react';
import { animated, useSpring } from '@react-spring/web';
import { __ } from '@wordpress/i18n';
import { type ReactNode, useEffect, useRef } from 'react';

import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, lineHeight, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

import {} from '@/v3/shared/hooks/useAnimation';
import { isDefined } from '@Utils/types';
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
  dataAttribute?: string;
  style?: React.CSSProperties;
  toggleCollapse: () => void;
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
  style = {},
  dataAttribute,
  toggleCollapse,
}: CardProps) => {
  const cardRef = useRef<HTMLDivElement>(null);

  const additionalAttributes = {
    ...(isDefined(dataAttribute) && { [dataAttribute]: true }),
  };

  const [collapseAnimation, collapseAnimate] = useSpring(
    {
      height: !collapsed ? cardRef.current?.scrollHeight : 0,
      opacity: !collapsed ? 1 : 0,
      overflow: 'hidden',
      config: {
        duration: 300,
        easing: (t) => t * (2 - t),
      },
    },
    [collapsed],
  );

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (!cardRef.current) return;

    const resizeObserver = new ResizeObserver((entries) => {
      const [entry] = entries;
      if (entry) {
        collapseAnimate.start({
          height: !collapsed ? cardRef.current?.scrollHeight : 0,
          opacity: !collapsed ? 1 : 0,
        });
      }
    });

    resizeObserver.observe(cardRef.current);

    return () => {
      resizeObserver.disconnect();
    };
  }, [collapsed]);

  return (
    <div css={styles.wrapper(hasBorder)} {...additionalAttributes} style={style}>
      <div css={styles.headerWrapper(collapsed || noSeparator)}>
        <h5 css={styles.title}>
          <span css={styles.titleIcon}>
            {titleIcon ? (
              <img src={titleIcon} alt={__('Icon', 'tutor')} />
            ) : (
              <SVGIcon name="handCoin" width={24} height={24} />
            )}
          </span>
          {title}
          <Show when={subscription}>
            <Badge variant="success">{__('Supports Subscriptions', 'tutor')}</Badge>
          </Show>
        </h5>

        <div css={styles.actions}>
          <Show when={actionTray}>{actionTray}</Show>
          <button type="button" css={styles.collapseButton({ isCollapsed: collapsed })} onClick={toggleCollapse}>
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
    background-color: ${colorTokens.background.white};
    box-shadow: ${shadow.card};

    ${
      hasBorder &&
      css`
      box-shadow: none;
      border: 1px solid ${colorTokens.stroke.divider};
    `
    }
  `,
  headerWrapper: (collapsed: boolean) => css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[8]};
    padding: ${spacing[20]} ${spacing[24]};
    min-height: 72px;

    ${
      !collapsed &&
      css`
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    `
    }
  `,
  title: css`
    ${typography.body('medium')};
    line-height: ${lineHeight[20]};
    display: flex;
    align-items: center;
    gap: ${spacing[8]};

    img {
      width: 24px;
      height: 24px;
    }

    svg {
      color: ${colorTokens.icon.default};
    }
  `,
  titleIcon: css`
    display: flex;
  `,
  collapseButton: ({ isCollapsed }: { isCollapsed: boolean }) => css`
    ${styleUtils.resetButton};
    display: flex;
    align-items: center;
    color: ${colorTokens.icon.brand};
    transition: color 0.3s ease-in-out;

    ${
      isCollapsed &&
      css`
      color: ${colorTokens.icon.default};
    `
    }
  `,
  actions: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
  `,
};
