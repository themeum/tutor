import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import SVGIcon from '@Atoms/SVGIcon';
import Button from '@Atoms/Button';
import coursePlaceholder from '@Images/common/course-placeholder.png';
import { typography } from '@Config/typography';

interface StudentCardProps {
  name: string;
  email: string;
  avatar?: string;
  hasSideBorders?: boolean;
  isSelected?: boolean;
  onItemClick?: () => void;
  onRemoveClick?: () => void;
}

function StudentCard({
  name,
  email,
  avatar,
  hasSideBorders = false,
  isSelected = false,
  onItemClick,
  onRemoveClick,
}: StudentCardProps) {
  return (
    <div css={styles.studentItem(hasSideBorders, !!onItemClick, isSelected)} onClick={onItemClick}>
      <div css={styles.studentThumb}>
        <img src={avatar || coursePlaceholder} css={styles.thumbnail} alt="course item" />
      </div>
      <div css={styles.studentContent}>
        <div css={styles.studentTitle}>{name}</div>
        <div css={styles.studentSubTitle}>{email}</div>
      </div>
      {onRemoveClick && (
        <div data-student-item-cross>
          <Button variant="text" onClick={onRemoveClick}>
            <SVGIcon name="cross" width={24} height={24} />
          </Button>
        </div>
      )}
    </div>
  );
}

export default StudentCard;

const styles = {
  studentItem: (hasSideBorders: boolean, hasOnClick: boolean, isSelected: boolean) => css`
    padding: ${spacing[8]} ${spacing[8]} ${spacing[8]} ${spacing[16]};
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    transition: background-color 0.25s ease-in;
    border-bottom: 1px solid ${colorTokens.stroke.disable};
    position: relative;

    ${hasOnClick &&
    css`
      cursor: pointer;
    `}

    ${isSelected &&
    css`
      &::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 3px;
        background-color: ${colorTokens.background.brand};
      }
    `}

    ${hasSideBorders &&
    css`
      border-left: 1px solid ${colorTokens.stroke.white};
      border-right: 1px solid ${colorTokens.stroke.white};
    `}

    [data-student-item-cross] {
      visibility: hidden;
    }

    &:hover {
      background-color: ${colorTokens.background.hover};

      ${hasSideBorders &&
      css`
        border-left: 1px solid ${colorTokens.stroke.disable};
        border-right: 1px solid ${colorTokens.stroke.disable};
      `}

      [data-student-item-cross] {
        visibility: visible;
      }
    }
  `,
  studentThumb: css`
    height: 34px;
  `,
  thumbnail: css`
    width: 34px;
    height: 34px;
    border-radius: ${borderRadius.circle};
  `,
  studentContent: css`
    width: 100%;
  `,
  studentTitle: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.primary};
  `,
  studentSubTitle: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
  `,
};
