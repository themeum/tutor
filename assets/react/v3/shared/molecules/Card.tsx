import { css } from '@emotion/react';
import { animated } from '@react-spring/web';
import { useRef, useState, type ReactNode } from 'react';

import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorPalate, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

import { useCollapseExpandAnimation } from '@Hooks/useCollapseExpandAnimation';

interface CardProps {
  children: ReactNode;
  hasBorder?: boolean;
  title: string | ReactNode;
  subtitle?: string;
  actionTray?: ReactNode;
  collapsed?: boolean;
  noSeparator?: boolean;
  hideArrow?: boolean;
  isAlternative?: boolean;
}

const Card = ({
  children,
  hasBorder = false,
  title,
  subtitle,
  actionTray,
  collapsed = false,
  noSeparator = false,
  hideArrow = false,
  isAlternative = false,
}: CardProps) => {
  const [isCollapsed, setIsCollapsed] = useState<boolean>(collapsed);
  const cardRef = useRef<HTMLDivElement>(null);

  const collapseAnimation = useCollapseExpandAnimation({
    ref: cardRef,
    isOpen: !isCollapsed,
  });

  return (
    <div css={styles.wrapper(hasBorder)}>
      <div css={styles.headerWrapper(isCollapsed || noSeparator, isAlternative)}>
        <div css={styles.headerAndAction}>
          <div css={styles.header}>
            <h5 css={styles.title}>{title}</h5>
            <Show when={subtitle}>
              <p css={styles.subtitle}>{subtitle}</p>
            </Show>
          </div>

          <div css={styles.actions}>
            <Show when={actionTray}>{actionTray}</Show>
            <Show when={!hideArrow}>
              <button
                type="button"
                css={styles.collapseButton({ isCollapsed })}
                onClick={() => setIsCollapsed(!isCollapsed)}
              >
                <SVGIcon name="chevronDown" width={24} height={24} style={styles.arrowUpDown} />
              </button>
            </Show>
          </div>
        </div>
      </div>
      <animated.div style={{ ...collapseAnimation }}>
        <div ref={cardRef} css={styles.cardBody}>
          {children}
        </div>
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

    ${
      hasBorder &&
      css`
				box-shadow: none;
				border: 1px solid ${colorTokens.stroke.divider};
    	`
    }
  `,
  headerWrapper: (collapsed: boolean, isAlternative: boolean) => css`
		padding: ${spacing[8]};
		display: flex;
		flex-direction: column;
		justify-content: center;
		gap: ${spacing[4]};

		${
      !collapsed &&
      css`
				border-bottom: 1px solid ${colorTokens.stroke.divider};;
			`
    }

    ${isAlternative && css`
      padding: ${spacing[12]} ${spacing[16]} ${spacing[12]} ${spacing[24]};
    `}
	`,
  headerAndAction: css`
		display: flex;
		justify-content: space-between;
		align-items: center;
	`,
  header: css`
		display: flex;
		flex-direction: column;
		padding: ${spacing[4]} ${spacing[16]};
	`,
  subtitle: css`
		${typography.caption()};
		color: ${colorTokens.text.subdued};
	`,
  title: css`
		${typography.body('medium')};
		color: ${colorTokens.text.primary};
		display: flex;
		align-items: center;
	`,
  collapseButton: ({
    isCollapsed,
  }: {
    isCollapsed: boolean;
  }) => css`
		${styleUtils.resetButton};
		display: flex;
		align-items: center;
		transition: transform 0.3s ease-in-out;

		${
      isCollapsed &&
      css`
			transform: rotate(180deg);
		`
    }
	`,
  arrowUpDown: css`
    color: ${colorTokens.icon.default};
    display: flex;
    justify-content: center;
    align-items: center;
  `,
  cardBody: css`
		padding: ${spacing[16]} ${spacing[24]} ${spacing[32]} ${spacing[24]};
	`,
  actions: css`
		display: flex;
		align-items: center;
		gap: ${spacing[8]};
	`,
};
