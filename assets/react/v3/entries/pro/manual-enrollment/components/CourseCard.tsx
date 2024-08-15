import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';

interface CourseCardProps {
  title: string;
  image: string;
  date: string;
  duration: string;
  total_enrolled: string;
  handleReplaceClick: () => void;
}

function CourseCard({ title, image, date, duration, total_enrolled, handleReplaceClick }: CourseCardProps) {
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
        <div css={styles.time}>{date}</div>
        <div css={styles.title}>{title}</div>
        <div css={styles.bottom}>
          <div>
            <SVGIcon name="clock" width={20} height={20} />
            <span>{duration}</span>
          </div>
          <div>
            <SVGIcon name="user" width={20} height={20} />
            <span>{total_enrolled}</span>
          </div>
        </div>
      </div>
    </div>
  );
}

export default CourseCard;

const styles = {
  wrapper: css`
    border: 1px solid ${colorTokens.stroke.border};
    border-radius: ${borderRadius[6]};
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
      border-top-left-radius: ${spacing[4]};
      border-top-right-radius: ${spacing[4]};
    }
  `,
  content: css`
    background-color: ${colorTokens.background.white};
    padding: ${spacing[16]} ${spacing[20]} ${spacing[20]};
    border-bottom-left-radius: ${spacing[6]};
    border-bottom-right-radius: ${spacing[6]};
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
};
