import Button from '@Atoms/Button';
import { LoadingSection } from '@Atoms/LoadingSpinner';
import TextInput from '@Atoms/TextInput';
import FormFieldWrapper from '@Components/fields/FormFieldWrapper';
import { useModal } from '@Components/modals/Modal';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import StudentListModal from '@EnrollmentComponents/modals/StudentListModal';
import { type Enrollment, type Student, useStudentListQuery } from '@EnrollmentServices/enrollment';
import { useDebounce } from '@Hooks/useDebounce';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { useFormContext } from 'react-hook-form';
import SelectedStudents from './SelectedStudents';
import StudentCard from './StudentCard';

interface FormSelectStudentsProps extends FormControllerProps<Student[]> {
  label?: string;
  helpText?: string;
  disabled?: boolean;
  loading?: boolean;
}

function FormSelectStudents({ label, field, fieldState, helpText, disabled, loading }: FormSelectStudentsProps) {
  const form = useFormContext<Enrollment>();
  const { showModal } = useModal();

  const course = form.watch('course');
  const students = form.watch('students') ?? [];

  const [searchText, setSearchText] = useState('');
  const debouncedSearchText = useDebounce(searchText);

  const studentListQuery = useStudentListQuery({
    offset: 0,
    limit: 10,
    object_id: course?.id,
    filter: {
      search: debouncedSearchText,
    },
  });

  const studentListResult = studentListQuery.data?.results ?? [];

  const [isOpen, setIsOpen] = useState(false);
  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  function handleItemClick(item: Student) {
    const isAlreadySelected = students.find((student) => student.ID === item.ID);
    if (!isAlreadySelected) {
      form.setValue('students', [...students, item]);
    }
    setIsOpen(false);
  }

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
            <div ref={triggerRef} css={styles.searchWrapper}>
              <TextInput
                variant="search"
                placeholder={__('Search students', 'tutor')}
                value={searchText}
                onFocus={() => setIsOpen(true)}
                onChange={setSearchText}
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

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
              <div
                css={[styles.optionsWrapper, { left: position.left, top: position.top, maxWidth: triggerWidth }]}
                ref={popoverRef}
              >
                {studentListQuery.isFetching ? (
                  <LoadingSection />
                ) : studentListResult.length > 0 ? (
                  studentListResult.map((item) => (
                    <StudentCard
                      key={item.ID}
                      name={item.display_name}
                      email={item.user_email}
                      avatar={item.avatar_url}
                      isSelected={!!students.find((student) => student.ID === item.ID)}
                      onItemClick={() => handleItemClick(item)}
                    />
                  ))
                ) : (
                  <div css={styles.noDataFound}>{__('No data found!', 'tutor')}</div>
                )}
              </div>
            </Portal>

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
  optionsWrapper: css`
    position: absolute;
    width: 100%;
    z-index: ${zIndex.dropdown};
    background-color: ${colorTokens.background.white};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};
    padding-bottom: ${spacing[8]};
    max-height: 300px;
    overflow-y: auto;
  `,
  options: css`
    list-style-type: none;
    max-height: 500px;
    padding: ${spacing[4]} 0;
    margin: 0;
    ${styleUtils.overflowYAuto};
  `,
  noDataFound: css`
    padding: ${spacing[20]};
    text-align: center;
  `,
};
