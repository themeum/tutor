import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useFieldArray, useFormContext } from 'react-hook-form';

import { Box } from '@Atoms/Box';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';

import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

import CourseSelectionHeader from '@/v3/entries/pro/bundle-builder/components/course-bundle/CourseSelectionHeader';
import SelectedCourseList from '@/v3/entries/pro/bundle-builder/components/course-bundle/SelectedCourseList';
import SelectionOverview from '@/v3/entries/pro/bundle-builder/components/course-bundle/SelectionOverview';
import CourseListModal from '@BundleBuilderComponents/modals/CourseListModal';
import { type CourseBundle } from '@BundleBuilderServices/bundle';

import bundleEmptyState from '@Images/bundle-empty-state.webp';

const CourseSelection = () => {
  const form = useFormContext<CourseBundle>();
  const { showModal } = useModal();
  const {
    append: addCourse,
    remove: removeCourse,
    move: moveCourse,
  } = useFieldArray({
    control: form.control,
    name: 'courses',
  });

  const mockData = form.watch('courses') || [];

  return (
    <div css={styles.wrapper}>
      <label css={typography.caption()}>{__('Courses', 'tutor')}</label>
      <Box css={styles.boxWrapper}>
        <Show
          when={mockData.length > 0}
          fallback={
            <div css={styles.emptyState}>
              <img src={bundleEmptyState} alt={__('Empty State', 'tutor')} />
              <p>{__('No Courses Added Yet', 'tutor')}</p>
              <Button
                variant="secondary"
                isOutlined
                icon={<SVGIcon name="plusSquareBrand" width={24} height={24} />}
                css={styles.addCourseButton}
                onClick={() => {
                  showModal({
                    component: CourseListModal,
                    props: {
                      title: __('Add Courses', 'tutor'),
                      onAddCourse: (course) => {
                        addCourse(course);
                      },
                    },
                  });
                }}
              >
                {__('Add Courses', 'tutor')}
              </Button>
            </div>
          }
        >
          <CourseSelectionHeader onAddCourses={addCourse} />

          <SelectedCourseList courses={mockData} onRemove={removeCourse} onSort={moveCourse} />

          <SelectionOverview />
        </Show>
      </Box>
    </div>
  );
};

export default CourseSelection;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[6]};
  `,
  boxWrapper: css`
    padding-inline: 0;
  `,
  emptyState: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
    align-items: center;
    padding-block: ${spacing[32]};

    img {
      max-width: 60px;
      width: 100%;
      object-fit: contain;
      object-position: center;
    }

    p {
      ${typography.body('medium')};
    }
  `,
  addCourseButton: css`
    border: 1px solid ${colorTokens.stroke.border};

    &:hover {
      border: 1px solid ${colorTokens.stroke.border};
    }
  `,
};
