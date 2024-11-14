import { css } from '@emotion/react';

import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';

interface TopicDragOverlayProps {
  title: string;
}

const TopicDragOverlay = ({ title }: TopicDragOverlayProps) => {
  return (
    <div css={styles.wrapper}>
      <SVGIcon name="dragVertical" width={24} height={24} />
      <span>{title}</span>
    </div>
  );
};

export default TopicDragOverlay;

const styles = {
  wrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    background-color: ${colorTokens.background.hover};
    padding: ${spacing[12]} ${spacing[16]};
    box-shadow: ${shadow.drag};
    ${typography.body()};
    color: ${colorTokens.text.hints};
    cursor: grabbing;

    svg {
      color: ${colorTokens.color.black[40]};
      flex-shrink: 0;
    }
  `,
};
