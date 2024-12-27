import { css } from '@emotion/react';

import SVGIcon from '@Atoms/SVGIcon';

import { type Course } from '@BundleBuilderServices/bundle';
import { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

interface CourseItemProps {
  course: Course;
  index: number;
  onRemove: () => void;
}

const CourseItem = ({ course, index, onRemove }: CourseItemProps) => {
  return (
    <div css={styles.wrapper}>
      <div css={styles.left}>
        <button data-drag-button css={styleUtils.resetButton}>
          <SVGIcon name="dragVertical" width={24} height={24} />
        </button>
        <span data-index>{index}</span>
        <img src={course.image} alt={course.title} />
        <p>{course.title}</p>
      </div>

      <div css={styles.right}>
        <button data-cross-button css={styleUtils.resetButton} onClick={() => onRemove?.()}>
          <SVGIcon name="cross" width={24} height={24} />
        </button>
        <Show
          when={course.sale_price}
          fallback={
            <span data-price css={styles.price({ hasSalePrice: false })}>
              {tutorConfig.tutor_currency.symbol}
              {course.regular_price}
            </span>
          }
        >
          <span data-price css={styles.price({ hasSalePrice: true })}>
            {tutorConfig.tutor_currency.symbol}
            {course.regular_price}
          </span>
          <span data-price css={styles.price({ hasSalePrice: false })}>
            {tutorConfig.tutor_currency.symbol}
            {course.sale_price}
          </span>
        </Show>
      </div>
    </div>
  );
};

export default CourseItem;

const styles = {
  wrapper: ({ isOverlay = false }) => css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
    align-items: center;
    padding: ${spacing[16]} ${spacing[20]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
    gap: ${spacing[28]};

    [data-drag-button] {
      cursor: grab;
      display: none;
      color: ${colorTokens.icon.hints};
    }

    [data-cross-button] {
      display: none;
      color: ${colorTokens.color.black[50]};
    }

    ${isOverlay &&
    css`
      box-shadow: ${shadow.drag};
      cursor: grabbing;
    `}

    &:hover {
      background-color: ${colorTokens.background.hover};

      [data-index],
      [data-price] {
        display: none;
      }

      [data-drag-button],
      [data-cross-button] {
        display: block;
      }
    }
  `,
  left: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[16]};

    img {
      width: 86px;
      height: ${spacing[48]};
      object-fit: cover;
      object-position: center;
      border-radius: ${borderRadius[2]};
      flex-shrink: 0;
    }

    p,
    span {
      ${typography.caption()};
      ${styleUtils.text.ellipsis(2)};
    }

    span {
      flex-shrink: 0;
      width: ${spacing[24]};
      ${styleUtils.flexCenter()};
    }
  `,
  right: css`
    ${styleUtils.display.flex()};
    align-items: center;
    justify-content: flex-end;
    flex-shrink: 0;
    max-width: 120px;
    width: 100%;
    gap: ${spacing[8]};
  `,
  price: ({ hasSalePrice = false }) => css`
    ${typography.caption()};
    color: ${hasSalePrice ? colorTokens.text.subdued : colorTokens.text.primary};
    text-decoration: ${hasSalePrice ? 'line-through' : 'none'};
  `,
};
