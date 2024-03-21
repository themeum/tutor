import { borderRadius, colorPalate, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import type { ReactNode } from 'react';

const styles = {
  wrapper: (hasBorder: boolean) => css`
    width: 100%;
    border-radius: ${borderRadius[6]};
    background-color: ${colorPalate.basic.white};
    box-shadow: ${shadow.card};

    ${
      hasBorder &&
      css`
      box-shadow: none;
      border: 1px solid ${colorPalate.border.neutral};
    `
    }
  `,
};

interface CardProps {
  children: ReactNode;
  hasBorder?: boolean;
}

const Card = ({ children, hasBorder = false }: CardProps) => {
  return <div css={styles.wrapper(hasBorder)}>{children}</div>;
};

export default Card;

interface CardHeaderProps {
  title: string | ReactNode;
  subtitle?: string;
  actionTray?: ReactNode;
  collapsed?: boolean;
  noSeparator?: boolean;
}

const headerStyles = {
  wrapper: (collapsed: boolean) => css`
    padding: ${spacing[16]} ${spacing[24]};
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: ${spacing[4]};

    ${
      !collapsed &&
      css`
      border-bottom: 1px solid ${colorPalate.surface.neutral.hover};
    `
    }
  `,
  titleAndAction: css`
    display: flex;
    justify-content: space-between;
    align-items: center;
  `,
  subtitle: css`
    ${typography.body()};
    color: ${colorPalate.text.neutral};
  `,
  title: css`
    ${typography.heading5('medium')};
    display: flex;
    align-items: center;
  `,
};

export const CardHeader = ({
  title,
  subtitle,
  actionTray,
  collapsed = false,
  noSeparator = false,
}: CardHeaderProps) => {
  return (
    <div css={headerStyles.wrapper(collapsed || noSeparator)}>
      <div css={headerStyles.titleAndAction}>
        <h5 css={headerStyles.title}>{title}</h5>
        {actionTray && <div>{actionTray}</div>}
      </div>
      {subtitle && <p css={headerStyles.subtitle}>{subtitle}</p>}
    </div>
  );
};
