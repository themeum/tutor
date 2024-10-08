import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import { borderRadius, colorTokens, shadow, spacing } from '@/v3/shared/config/styles';
import { typography } from '@/v3/shared/config/typography';
import { css } from '@emotion/react';

interface TopicDragOverlayProps {
  topicTitle: string;
}

const TopicDragOverlay = ({ topicTitle }: TopicDragOverlayProps) => {
  return (
    <div css={styles.wrapper}>
      <SVGIcon name="dragVertical" width={24} height={24} />
      <span>{topicTitle}</span>
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

    svg {
      color: ${colorTokens.color.black[40]};
      flex-shrink: 0;
    }
  `,
};
