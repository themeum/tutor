import { borderRadius, colorTokens, fontSize, lineHeight, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import SVGIcon from '@Atoms/SVGIcon';
import Button from '@Atoms/Button';
import profilePlaceholder from '@Images/profile-photo.png';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { __ } from '@wordpress/i18n';

interface StudentCardProps {
  name: string;
  email: string;
  avatar?: string;
  isEnrolled?: boolean;
  enrollmentStatus?: string;
  hasSideBorders?: boolean;
  isSelected?: boolean;
  onItemClick?: () => void;
  onRemoveClick?: () => void;
}

function StudentCard({
  name,
  email,
  avatar,
  isEnrolled = false,
  enrollmentStatus,
  hasSideBorders = false,
  isSelected = false,
  onItemClick,
  onRemoveClick,
}: StudentCardProps) {
  return (
    <div
      role="button"
      css={styles.studentItem(hasSideBorders, !!onItemClick, isSelected, isEnrolled)}
      onClick={!isEnrolled ? onItemClick : undefined}
    >
      <div css={styles.studentThumb}>
        <img src={avatar || profilePlaceholder} css={styles.thumbnail} alt="avatar" />
      </div>
      <div css={styles.studentContent}>
        <div css={styles.studentTitle(isEnrolled)}>
          {name}
          <Show when={isEnrolled}>
            <div css={styles.alreadyEnrolled}>
              {__('Already Enrolled', 'tutor')} ({enrollmentStatus})
            </div>
          </Show>
        </div>
        <div css={styles.studentSubTitle(isEnrolled)}>{email}</div>
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
  studentItem: (hasSideBorders: boolean, hasOnClick: boolean, isSelected: boolean, isEnrolled: boolean) => css`
    padding: ${spacing[8]} ${spacing[8]} ${spacing[8]} ${spacing[16]};
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    transition: background-color 0.25s ease-in;
    border-bottom: 1px solid ${colorTokens.stroke.disable};
    position: relative;

    ${hasOnClick &&
    !isEnrolled &&
    css`
      cursor: pointer;
    `}

    ${isSelected &&
    css`
      &::before {
        content: '';
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
      ${!isEnrolled &&
      css`
        background-color: ${colorTokens.background.hover};
      `}

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
  studentTitle: (disabled: boolean) => css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};

    ${typography.body('medium')};
    color: ${colorTokens.text.primary};

    ${disabled &&
    css`
      color: ${colorTokens.text.disable};
    `}
  `,
  studentSubTitle: (disabled: boolean) => css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};

    ${disabled &&
    css`
      color: ${colorTokens.text.disable};
    `}
  `,
  alreadyEnrolled: css`
    font-size: ${fontSize[12]};
    line-height: ${lineHeight[16]};
    padding: ${spacing[2]} ${spacing[4]};
    border-radius: ${borderRadius[2]};
    background-color: ${colorTokens.background.disable};
    color: ${colorTokens.text.primary};
    text-transform: capitalize;
  `,
};
