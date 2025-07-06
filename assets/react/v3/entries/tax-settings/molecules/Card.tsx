import { type SerializedStyles, css } from '@emotion/react';
import type { ReactNode } from 'react';

import { borderRadius, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';

interface CardProps {
  children: ReactNode;
  hasBorder?: boolean;
  cardStyle?: SerializedStyles;
}

const Card = ({ children, hasBorder = false, cardStyle }: CardProps) => {
  return <div css={[styles.wrapper(hasBorder), cardStyle]}>{children}</div>;
};

const styles = {
  wrapper: (hasBorder: boolean) => css`
    width: 100%;
    border-radius: ${borderRadius.card};
    background-color: ${colorTokens.background.white};
    box-shadow: ${shadow.card};

    ${hasBorder &&
    css`
      box-shadow: none;
      border: 1px solid ${colorTokens.border.neutral};
    `}
  `,
};

export default Card;

interface CardHeaderProps {
  title: string | ReactNode;
  subtitle?: string | ReactNode;
  actionTray?: ReactNode;
  collapsed?: boolean;
  noSeparator?: boolean;
  size?: 'regular' | 'small';
}

export const CardHeader = ({
  title,
  subtitle,
  actionTray,
  collapsed = false,
  noSeparator = false,
  size = 'regular',
}: CardHeaderProps) => {
  return (
    <div css={headerStyles.wrapper(collapsed || noSeparator, size)}>
      <div css={headerStyles.titleAndAction}>
        <h5 css={headerStyles.title}>{title}</h5>
        {actionTray && <div>{actionTray}</div>}
      </div>
      {subtitle && <div css={headerStyles.subtitle}>{subtitle}</div>}
    </div>
  );
};

const headerStyles = {
  wrapper: (collapsed: boolean, size: 'regular' | 'small') => css`
    padding: ${spacing[16]} ${spacing[20]} ${spacing[12]} ${spacing[20]};

    ${size === 'small' &&
    css`
      padding: ${spacing[16]};
    `}

    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: ${spacing[4]};

    ${!collapsed &&
    css`
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    `}
  `,
  titleAndAction: css`
    display: flex;
    justify-content: space-between;
    align-items: center;
  `,
  subtitle: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
  title: css`
    ${typography.body('medium')};
    display: flex;
    align-items: center;
  `,
};
