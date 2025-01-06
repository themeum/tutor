import Button from '@/v3/shared/atoms/Button';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@/v3/shared/config/styles';
import { typography } from '@/v3/shared/config/typography';
import { css } from '@emotion/react';
import { type ReactNode } from 'react';
import coursePlaceholder from '@SharedImages/course-placeholder.png';

interface CategoryItemProps {
  image: string;
  title: string;
  subTitle: string | ReactNode;
  handleDeleteClick: () => void;
}

export default function CategoryItem({ image, title, subTitle, handleDeleteClick }: CategoryItemProps) {
  return (
    <div css={styles.selectedItem}>
      <div css={styles.selectedThumb}>
        <img src={image || coursePlaceholder} css={styles.thumbnail} alt="course item" />
      </div>
      <div css={styles.selectedContent}>
        <div css={styles.selectedTitle}>{title}</div>
        <div css={styles.selectedSubTitle}>{subTitle}</div>
      </div>
      <div>
        <Button variant="text" onClick={handleDeleteClick}>
          <SVGIcon name="delete" width={24} height={24} />
        </Button>
      </div>
    </div>
  );
}

const styles = {
  selectedItem: css`
    padding: ${spacing[12]};
    display: flex;
    align-items: center;
    gap: ${spacing[16]};

    &:not(:last-child) {
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    }
  `,
  selectedContent: css`
    width: 100%;
  `,
  selectedTitle: css`
    ${typography.small()};
    color: ${colorTokens.text.primary};
    margin-bottom: ${spacing[4]};
  `,
  selectedSubTitle: css`
    ${typography.small()};
    color: ${colorTokens.text.hints};
  `,
  selectedThumb: css`
    height: 48px;
  `,
  thumbnail: css`
    width: 48px;
    height: 48px;
    border-radius: ${borderRadius[4]};
    object-fit: cover;
  `,
};
