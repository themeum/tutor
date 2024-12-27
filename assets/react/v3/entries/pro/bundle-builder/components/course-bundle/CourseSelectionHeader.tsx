import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import CourseListModal from '@BundleBuilderComponents/modals/CourseListModal';
import { type Course } from '@BundleBuilderServices/bundle';
import { useModal } from '@Components/modals/Modal';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';

interface CourseSelectionHeaderProps {
  onAddCourses: (course: Course) => void;
}

const CourseSelectionHeader = ({ onAddCourses }: CourseSelectionHeaderProps) => {
  const { showModal } = useModal();

  return (
    <div css={styles.wrapper}>
      <div css={styles.left}>
        <div css={typography.body('medium')}>{sprintf(__('%d Courses selected', 'tutor'), 3)}</div>

        {/* @TODO: need remove/comment-out before pushing
        <Controller
          name="enable_qna"
          control={form.control}
          render={(controllerProps) => (
            <FormCheckbox {...controllerProps} label={__('Enable Course Prerequisite', 'tutor')} />
          )}
        /> */}
      </div>

      <Button
        variant="secondary"
        isOutlined
        icon={<SVGIcon name="plusSquareBrand" width={24} height={24} />}
        buttonCss={styles.addCourseButton}
        onClick={() => {
          showModal({
            component: CourseListModal,
            props: {
              title: __('Add Courses', 'tutor'),
              onAddCourse: (course) => {
                onAddCourses(course);
              },
            },
          });
        }}
      >
        {__('Add Courses', 'tutor')}
      </Button>
    </div>
  );
};

export default CourseSelectionHeader;

const styles = {
  wrapper: css`
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 ${spacing[20]} ${spacing[12]} ${spacing[20]};
    border-bottom: 1px solid ${colorTokens.stroke.divider};
  `,
  left: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  addCourseButton: css`
    border: 1px solid ${colorTokens.stroke.border};

    &:hover {
      border: 1px solid ${colorTokens.stroke.border};
    }
  `,
};
