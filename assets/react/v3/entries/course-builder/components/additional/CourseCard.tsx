import { css } from '@emotion/react';

import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, fontWeight, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';

import { styleUtils } from '@Utils/style-utils';

interface CourseCardProps {
	id: number;
	title: string;
	image: string;
}

const CourseCard = ({ id, title, image }: CourseCardProps) => {
	return (
		<div key={id} css={styles.courseCard}>
			<div css={styles.imageWrapper}>
				<img src={image} alt={title} css={styles.image} />
			</div>
			<div css={styles.cardContent}>
				<span css={styles.cardTitle}>{title}</span>
				<p css={typography.tiny()}>{id}</p>
			</div>
			<button type="button" css={styles.removeButton} data-visually-hidden>
				<SVGIcon name="times" width={14} height={14} />
			</button>
		</div>
	);
};

export default CourseCard;

const styles = {
	courseCard: css`
    position: relative;
    padding: ${spacing[8]};
    border: 1px solid transparent;
    border-radius: ${borderRadius.card};
    display: grid;
    grid-template-columns: 76px 1fr;
    gap: ${spacing[10]};
    align-items: center;
    cursor: pointer;
    transition: border 0.3s ease;
    [data-visually-hidden] {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }

    &:hover {
      border-color: ${colorTokens.stroke.default};
      [data-visually-hidden] {
        opacity: 1;
      }
    }
  `,
	imageWrapper: css`
    height: 42px;
  `,
	image: css`
    width: 100%;
    height: 100%;
    border-radius: ${borderRadius.card};
    object-fit: cover;
    object-position: center;
  `,
	cardContent: css`
    display: flex;
    flex-direction: column;
  `,
	cardTitle: css`
    ${typography.small()};
    ${styleUtils.text.ellipsis(1)};
    font-weight: ${fontWeight.medium};
  `,
	removeButton: css`
    ${styleUtils.resetButton};
    position: absolute;
    top: 50%;
    right: ${spacing[8]};
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: ${borderRadius.circle};
    background: ${colorTokens.background.white};
    transition: opacity 0.3s ease-in-out;

    svg {
      color: ${colorTokens.icon.default};
      transition: color 0.3s ease-in-out;
    }

    :hover {
      svg {
        color: ${colorTokens.icon.hover};
      }
    }

    :focus {
      box-shadow: ${shadow.focus};
    }
  `,
};
