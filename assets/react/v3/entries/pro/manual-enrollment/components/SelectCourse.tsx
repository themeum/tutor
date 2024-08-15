import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { Controller, useFormContext } from 'react-hook-form';
import { Box } from '@Atoms/Box';
import { requiredRule } from '@Utils/validation';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import { __ } from '@wordpress/i18n';
import SVGIcon from '@Atoms/SVGIcon';
import Button from '@Atoms/Button';
import { useModal } from '@Components/modals/Modal';
import { Enrollment } from '@EnrollmentServices/enrollment';
import coursePlaceholder from '@Images/placeholder.png';
import { typography } from '@Config/typography';
import CourseListModal from '@EnrollmentComponents/modals/CourseListModal';
import CourseCard from '@EnrollmentComponents/CourseCard';

function Students() {
  const form = useFormContext<Enrollment>();
  const { showModal } = useModal();

  const selectedCourse = form.watch('course');

  return (
    <div css={styles.wrapper}>
      {selectedCourse ? (
        <CourseCard
          title={selectedCourse.title}
          image={selectedCourse.image ?? coursePlaceholder}
          date="28 Mar, 2020 10:50 am"
          duration="6 h 30m"
          total_enrolled="1050"
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
          variant="primary"
          isOutlined
          buttonCss={styles.buttonStyle}
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
}
export default Students;

const styles = {
  wrapper: css``,
  buttonStyle: css`
    width: 100%;
  `,
};
