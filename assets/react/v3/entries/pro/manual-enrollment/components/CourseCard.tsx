import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, fontWeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { Course } from '@EnrollmentServices/enrollment';
const { __ } = wp.i18n;

interface CourseCardProps {
  course: Course;
  isSubscriptionCourse: boolean;
  handleReplaceClick: () => void;
}

function CourseCard({ course, isSubscriptionCourse, handleReplaceClick }: CourseCardProps) {
  const { title, image, last_updated, course_duration, total_enrolled, regular_price, sale_price } = course;
  return (
    <div css={styles.wrapper}>
      <div css={styles.overlay} data-overlay>
        <Button
          variant="secondary"
          icon={<SVGIcon name="refresh" width={24} height={24} />}
          onClick={handleReplaceClick}
        >
          {__('Replace', 'tutor')}
        </Button>
      </div>
      <div css={styles.thumb}>
        <img src={image} alt={title} />
      </div>
      <div css={styles.content}>
        <div css={styles.time}>{last_updated}</div>
        <div css={styles.title}>{title}</div>
        <div css={styles.bottom}>
          <div>
            <SVGIcon name="clock" width={20} height={20} />
            <span>{course_duration}</span>
          </div>
          <div>
            <SVGIcon name="user" width={20} height={20} />
            <span>{total_enrolled}</span>
          </div>
        </div>
      </div>
      {!isSubscriptionCourse && (
        <div css={styles.footer}>
          <span css={styles.priceLabel}>{__('Price:', 'tutor')}</span>
          <span css={styles.price}>{sale_price ? sale_price : regular_price}</span>
          {sale_price && <span css={styles.discountPrice}>{regular_price}</span>}
        </div>
      )}
    </div>
  );
}

export default CourseCard;

const styles = {
  wrapper: css`
    border: 1px solid ${colorTokens.stroke.border};
    border-radius: ${borderRadius[6]};
    overflow: hidden;
    position: relative;

    &:hover {
      [data-overlay] {
        display: flex;
      }
    }
  `,
  overlay: css`
    position: absolute;
    height: 100%;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #41454fb2;
    border-radius: ${borderRadius[6]};
    backdrop-filter: blur(2px);
    display: none;
  `,
  thumb: css`
    height: 144px;
    width: 100%;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  `,
  content: css`
    background-color: ${colorTokens.background.white};
    padding: ${spacing[16]} ${spacing[20]} ${spacing[20]};
  `,
  time: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
    margin-bottom: ${spacing[8]};
  `,
  title: css`
    ${typography.body('bold')};
    color: ${colorTokens.text.primary};
    margin-bottom: ${spacing[16]};
  `,
  bottom: css`
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;

    > div {
      ${typography.small()};
      color: ${colorTokens.text.primary};
      display: flex;
      align-items: center;
      gap: ${spacing[2]};
    }

    svg {
      color: ${colorTokens.text.hints};
    }
  `,
  footer: css`
    ${typography.caption()};
    background-color: ${colorTokens.background.white};
    padding: ${spacing[12]} ${spacing[20]};
    border-top: 1px solid ${colorTokens.stroke.divider};
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
  `,
  priceLabel: css`
    color: ${colorTokens.text.hints};
  `,
  price: css`
    font-weight: ${fontWeight.medium};
    color: ${colorTokens.text.primary};
  `,
  discountPrice: css`
    text-decoration: line-through;
    color: ${colorTokens.text.subdued};
  `,
};
