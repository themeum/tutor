import { css } from '@emotion/react';
import type React from 'react';

import { colorTokens, fontSize, lineHeight, spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import { isDefined } from '@TutorShared/utils/types';

import SVGIcon from './SVGIcon';

type ProBadgeSize = 'tiny' | 'small' | 'regular' | 'large';

interface ProBadgeBaseProps {
  size?: ProBadgeSize;
  textOnly?: boolean;
  children?: React.ReactNode;
  content?: React.ReactNode;
}

interface ProBadgeWithContent extends ProBadgeBaseProps {
  content: React.ReactNode;
  children?: never;
  textOnly?: boolean;
}

interface ProBadgeWithChildren extends ProBadgeBaseProps {
  content?: never;
  children: React.ReactNode;
  textOnly?: never;
}

type ProBadgeProps = ProBadgeWithContent | ProBadgeWithChildren;

const ProBadge = ({ children, content, size = 'regular', textOnly }: ProBadgeProps) => {
  return (
    <div css={styles.wrapper({ hasChildren: isDefined(children), size })}>
      {children}
      <Show when={!isDefined(children) && !textOnly}>
        <SVGIcon
          name={size === 'tiny' ? 'crownRoundedSmall' : 'crownRounded'}
          width={badgeSizes[size].iconSize}
          height={badgeSizes[size].iconSize}
        />
      </Show>
      <div
        css={styles.content({
          hasChildren: isDefined(children),
          size,
          textOnly,
        })}
      >
        {isDefined(children) ? (
          <SVGIcon
            name={size === 'tiny' ? 'crownRoundedSmall' : 'crownRounded'}
            width={size === 'tiny' ? badgeSizes[size].iconSize : 16}
          />
        ) : (
          content
        )}
      </div>
    </div>
  );
};

export default ProBadge;

const badgeSizes = {
  tiny: {
    borderRadius: spacing[10],
    height: spacing[10],
    gap: spacing[2],
    iconSize: 10,
    fontSize: '0.5rem',
    lineHeight: '0.625rem',
  },
  small: {
    borderRadius: spacing[16],
    height: spacing[16],
    gap: spacing[4],
    iconSize: 16,
    fontSize: fontSize[10],
    lineHeight: lineHeight[16],
  },
  regular: {
    borderRadius: '22px',
    height: '22px',
    gap: '5px',
    iconSize: 22,
    fontSize: fontSize[14],
    lineHeight: lineHeight[18],
  },
  large: {
    borderRadius: '26px',
    height: '26px',
    gap: spacing[6],
    iconSize: 26,
    fontSize: fontSize[16],
    lineHeight: lineHeight[26],
  },
};

const styles = {
  wrapper: ({ hasChildren, size = 'regular' }: { hasChildren: boolean; size?: ProBadgeSize }) => css`
    position: relative;

    svg {
      flex-shrink: 0;
    }

    ${!hasChildren &&
    css`
      height: ${badgeSizes[size].height};
      display: inline-flex;
      border-radius: ${badgeSizes[size].borderRadius};
      align-items: center;
      gap: ${badgeSizes[size].gap};
      overflow: hidden;
      background: linear-gradient(88.9deg, #d65702 6.26%, #e5803c 91.4%);
    `}
  `,
  content: ({
    hasChildren,
    size = 'regular',
    textOnly,
  }: {
    hasChildren: boolean;
    size?: ProBadgeSize;
    textOnly?: boolean;
  }) => css`
    position: absolute;
    top: 0;
    right: 0;
    display: flex;
    flex-shrink: 0;
    transform: translateX(50%) translateY(-50%);

    ${!hasChildren &&
    css`
      display: inline-flex;
      position: static;
      transform: none;
      padding: ${spacing[2]};
      color: ${colorTokens.icon.white};
      margin-right: ${badgeSizes[size].gap};
      font-size: ${badgeSizes[size].fontSize};
      line-height: ${badgeSizes[size].lineHeight};

      ${textOnly &&
      css`
        padding: 0;
        padding-inline: ${spacing[6]};
        margin: 0;
      `}
    `}
  `,
};
