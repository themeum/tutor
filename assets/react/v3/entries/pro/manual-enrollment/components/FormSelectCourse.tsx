import { css } from '@emotion/react';
import { useFormContext } from 'react-hook-form';
import { __ } from '@wordpress/i18n';
import Button from '@Atoms/Button';
import { useModal } from '@Components/modals/Modal';
import { Course, Enrollment } from '@EnrollmentServices/enrollment';
import CourseListModal from '@EnrollmentComponents/modals/CourseListModal';
import CourseCard from '@EnrollmentComponents/CourseCard';
import FormFieldWrapper from '@Components/fields/FormFieldWrapper';
import { FormControllerProps } from '@Utils/form';

interface FormSelectCourseProps extends FormControllerProps<Course | null> {
  disabled?: boolean;
}

function FormSelectCourse({ field, fieldState, disabled }: FormSelectCourseProps) {
  const form = useFormContext<Enrollment>();
  const { showModal } = useModal();

  const selectedCourse = form.watch('course');

  const hasError = !!fieldState.error;

  return (
    <FormFieldWrapper field={field} fieldState={fieldState} disabled={disabled}>
      {() => {
        return (
          <div css={styles.wrapper}>
            {selectedCourse ? (
              <CourseCard
                course={selectedCourse}
                isSubscriptionCourse={!!selectedCourse.plans?.length}
                handleReplaceClick={() => {
                  showModal({
                    component: CourseListModal,
                    props: {
                      title: __('Replace course', 'tutor'),
                      form,
                    },
                    closeOnOutsideClick: true,
                  });
                }}
              />
            ) : (
              <Button
                variant={hasError ? 'danger' : 'primary'}
                isOutlined
                buttonCss={styles.buttonStyle}
                disabled={disabled}
                onClick={() => {
                  showModal({
                    component: CourseListModal,
                    props: {
                      title: __('Select course', 'tutor'),
                      form,
                    },
                    closeOnOutsideClick: true,
                  });
                }}
              >
                {__('Select Course', 'tutor')}
              </Button>
            )}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
}
export default FormSelectCourse;

const styles = {
  wrapper: css``,
  buttonStyle: css`
    width: 100%;
  `,
};
