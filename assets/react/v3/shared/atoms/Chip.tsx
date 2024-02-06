import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { styleUtils } from '@Utils/style-utils';
import { ReactNode } from 'react';

import SVGIcon from './SVGIcon';
import { noop } from '@Utils/util';

type ChipProps = {
  label: string;
  onClick?: () => void;
  showIcon?: boolean;
  icon?: ReactNode;
  isClickable?: boolean;
};

const Chip = ({
  label,
  onClick = noop,
  showIcon = true,
  icon = <SVGIcon name="cross" width={20} height={20} />,
  isClickable,
}: ChipProps) => {
  if (isClickable) {
    return (
      <button css={styles.wrapper({ hasIcon: showIcon, isClickable: true })} onClick={onClick}>
        <div css={styles.label}>{label}</div>
        {showIcon && (
          <div css={styles.iconWrapper} data-icon-wrapper>
            {icon}
          </div>
        )}
      </button>
    );
  }

  return (
    <div css={styles.wrapper({ hasIcon: showIcon, isClickable: false })}>
      <div css={styles.label}>{label}</div>
      {showIcon && (
        <button css={styles.iconWrapper} onClick={onClick} data-icon-wrapper>
          {icon}
        </button>
      )}
    </div>
  );
};

export default Chip;

const styles = {
  wrapper: ({ hasIcon = false, isClickable }: { hasIcon: boolean; isClickable: boolean }) => css`
    ${styleUtils.resetButton};
    background-color: #E4E5E7;
    border-radius: ${borderRadius[24]};
    padding: ${spacing[4]} ${spacing[8]};
    min-height: 24px;
    transition: background-color 0.3s ease;

    ${!isClickable && css`
      cursor: inherit;
    `}

    ${hasIcon &&
    css`
      display: flex;
      justify-content: center;
      align-items: center;
      gap: ${spacing[2]};
      padding: ${spacing[4]} ${spacing[8]} ${spacing[4]} ${spacing[12]};
    `}

    :hover {
      [data-icon-wrapper] {
        > svg {
          color: ${colorTokens.icon.hover};
        }
      }
    }
  `,
  label: css`
    ${typography.caption()}
  `,
  iconWrapper: css`
    ${styleUtils.resetButton};
    border-radius: ${borderRadius['circle']};
    transition: background-color 0.3s ease;
    height: 20px;
    width: 20px;
    text-align: center;

    svg {
      color: ${colorTokens.icon.default};
      transition: color 0.3s ease;
    }
  `,
};
