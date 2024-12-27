import { Breakpoint, colorTokens, headerHeight, spacing, zIndex } from '@/v3/shared/config/styles';
import { typography } from '@/v3/shared/config/typography';
import { styleUtils } from '@/v3/shared/utils/style-utils';
import { css } from '@emotion/react';
import CourseSelection from './CourseSelection';

const CourseBundleContainer = () => {
  return (
    <div css={styles.wrapper}>
      <div css={styles.mainForm({ isWpEditorFullScreen: true })}>
        <div css={styles.fieldsWrapper}>
          <div css={styles.titleAndSlug}>{/* Title and Slug */}</div>

          {/* WP Editor */}
          <CourseSelection />

          <div css={styles.sidebar}>
            {/* Visibility */}

            {/* Password */}

            {/* Schedule */}

            {/* Featured Image */}

            {/* Course Price Section */}

            {/* Ribbon Select */}
          </div>
        </div>
      </div>
    </div>
  );
};

export default CourseBundleContainer;

const styles = {
  wrapper: css`
    display: grid;
    grid-template-columns: 1fr 338px;
    gap: ${spacing[32]};
    width: 100%;

    ${Breakpoint.smallTablet} {
      grid-template-columns: 1fr;
      gap: 0;
    }
  `,
  mainForm: ({ isWpEditorFullScreen }: { isWpEditorFullScreen: boolean }) => css`
    padding-block: ${spacing[32]} ${spacing[24]};
    align-self: start;
    top: ${headerHeight}px;
    position: sticky;

    ${isWpEditorFullScreen &&
    css`
      z-index: ${zIndex.header + 1};
    `}

    ${Breakpoint.smallTablet} {
      padding-top: ${spacing[16]};
      position: unset;
    }
  `,

  fieldsWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,
  titleAndSlug: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  sidebar: css`
    border-left: 1px solid ${colorTokens.stroke.divider};
    min-height: calc(100vh - ${headerHeight}px);
    padding-left: ${spacing[32]};
    padding-block: ${spacing[24]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  priceRadioGroup: css`
    display: flex;
    align-items: center;
    gap: ${spacing[36]};
  `,
  coursePriceWrapper: css`
    display: flex;
    align-items: flex-start;
    gap: ${spacing[16]};
  `,
  statusAndDate: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  updatedOn: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
};
