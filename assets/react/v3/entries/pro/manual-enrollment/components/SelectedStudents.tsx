import { colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { typography } from '@Config/typography';
import StudentCard from './StudentCard';
import { Enrollment, Student } from '@EnrollmentServices/enrollment';
import { UseFormReturn } from 'react-hook-form';
const { __, _n, sprintf } = wp.i18n;

interface SelectedStudentsProps {
  form: UseFormReturn<Enrollment>;
  students: Student[];
}

function SelectedStudents({ form, students }: SelectedStudentsProps) {
  function removesSelectedItem(id: String) {
    form.setValue(
      'students',
      students?.filter((item) => item.ID !== id)
    );
  }

  return (
    <div css={styles.selectedWrapper}>
      <div css={styles.selectedCount}>
        {sprintf(_n('%d Student selected', '%d Students selected', students.length, 'tutor'), students.length)}
      </div>
      {students?.map((item) => (
        <StudentCard
          key={item.ID}
          name={item.display_name}
          email={item.user_email}
          avatar={item.avatar_url}
          hasSideBorders
          onRemoveClick={() => removesSelectedItem(item.ID)}
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
