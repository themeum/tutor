import { colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { typography } from '@Config/typography';
import StudentCard from './StudentCard';
import { Enrollment, Student } from '@EnrollmentServices/enrollment';
import { UseFormReturn } from 'react-hook-form';

interface SelectedStudentsProps {
  form: UseFormReturn<Enrollment>;
  students: Student[];
}

function SelectedStudents({ form, students }: SelectedStudentsProps) {
  function removesSelectedItem(id: number) {
    form.setValue(
      'students',
      students?.filter((item) => item.id !== id)
    );
  }

  return (
    <div css={styles.selectedWrapper}>
      <div css={styles.selectedCount}>{students.length} Students selected</div>
      {students?.map((item) => (
        <StudentCard
          key={item.id}
          name={item.name}
          email={item.email}
          avatar={item.avatar}
          handleRemoveClick={() => removesSelectedItem(item.id)}
        />
      ))}
    </div>
  );
}
export default SelectedStudents;

const styles = {
  selectedWrapper: css`
    margin-left: -${spacing[16]};
    margin-right: -${spacing[16]};
  `,
  selectedCount: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.primary};
    padding: ${spacing[12]} ${spacing[16]};
    border-bottom: 1px solid ${colorTokens.stroke.disable};
    margin-top: ${spacing[16]};
  `,
};
