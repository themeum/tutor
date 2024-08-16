import { spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { useFormContext } from 'react-hook-form';
import { __ } from '@wordpress/i18n';
import Button from '@Atoms/Button';
import StudentListModal from '@EnrollmentComponents/modals/StudentListModal';
import FormFieldWrapper from '@Components/fields/FormFieldWrapper';
import { useModal } from '@Components/modals/Modal';
import { Enrollment, Student } from '@EnrollmentServices/enrollment';
import TextInput from '@Atoms/TextInput';
import SelectedStudents from './SelectedStudents';
import { FormControllerProps } from '@Utils/form';

interface FormSelectStudentsProps extends FormControllerProps<Student[]> {
  label?: string;
  helpText?: string;
  disabled?: boolean;
  loading?: boolean;
}

function FormSelectStudents({ label, field, fieldState, helpText, disabled, loading }: FormSelectStudentsProps) {
  const form = useFormContext<Enrollment>();
  const { showModal } = useModal();

  const students = form.watch('students') ?? [];

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      helpText={helpText}
      disabled={disabled}
      loading={loading}
    >
      {() => {
        return (
          <div css={styles.wrapper}>
            <div css={styles.searchWrapper}>
              <TextInput
                variant="search"
                placeholder={__('Search students', 'tutor')}
                onChange={(value) => console.log(value)}
                disabled={disabled}
              />
              <Button
                variant="tertiary"
                onClick={() => {
                  showModal({
                    component: StudentListModal,
                    props: {
                      title: __('Select students', 'tutor'),
                      form,
                    },
                    closeOnOutsideClick: true,
                  });
                }}
                disabled={disabled}
              >
                {__('Browse', 'tutor')}
              </Button>
            </div>

            {students.length > 0 && <SelectedStudents form={form} students={students} />}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
}
export default FormSelectStudents;

const styles = {
  wrapper: css``,
  searchWrapper: css`
    display: flex;
    gap: ${spacing[8]};
    align-items: end;
  `,
};
