import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { useAddCourseToBundleMutation, type BundleFormData, type Course } from '@BundleBuilderServices/bundle';
import { useModal } from '@TutorShared/components/modals/Modal';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { useFormContext } from 'react-hook-form';
import { getBundleId } from '../../utils/utils';
import CourseListModal from '../modals/CourseListModal';

const bundleId = getBundleId();

const CourseSelectionHeader = () => {
  const { showModal } = useModal();
  const form = useFormContext<BundleFormData>();
  const selectedCourses = form.watch('courses');

  const addOrRemoveCourseMutation = useAddCourseToBundleMutation();

  const handleAddCourse = async (course: Course) => {
    const response = await addOrRemoveCourseMutation.mutateAsync({
      ID: bundleId,
      course_id: course.id,
      user_action: 'add_course',
    });

    if (response.data) {
      form.setValue('courses', [...selectedCourses, course]);
    }
  };

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
              onSelect: (course) => {
                form.setValue('courses', [...selectedCourses, course]);
                handleAddCourse(course);
              },
              selectedCourseIds: selectedCourses.map((course) => course.id),
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
    outline: 1px solid ${colorTokens.stroke.border};

    &:hover {
      outline: 1px solid ${colorTokens.stroke.border};
    }
  `,
};
