import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { Controller, useFormContext } from 'react-hook-form';
import { Box } from '@Atoms/Box';
import { requiredRule } from '@Utils/validation';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import { __ } from '@wordpress/i18n';
import SVGIcon from '@Atoms/SVGIcon';
import Button from '@Atoms/Button';
import StudentListModal from '@EnrollmentComponents/modals/StudentListModal';
import { useModal } from '@Components/modals/Modal';
import { Enrollment } from '@EnrollmentServices/enrollment';
import coursePlaceholder from '@Images/common/course-placeholder.png';
import { typography } from '@Config/typography';

function Students() {
  const form = useFormContext<Enrollment>();
  const { showModal } = useModal();

  const students = form.watch('students') ?? [];

  function removesSelectedItem(id: number) {
    form.setValue(
      'students',
      students?.filter((item) => item.id !== id)
    );
  }
  return (
    <div css={styles.wrapper}>
      <div css={styles.searchWrapper}>
        <Controller
          name="coupon_title"
          //   control={form.control}
          rules={requiredRule()}
          render={(controllerProps) => (
            <FormInputWithContent
              {...controllerProps}
              label={__('Courses', 'tutor')}
              placeholder={__('Search courses or bundles...', 'tutor')}
              content={<SVGIcon name="search" width={24} height={24} />}
              showVerticalBar={false}
            />
          )}
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
        >
          {__('Browse', 'tutor')}
        </Button>
      </div>

      {students.length > 0 && (
        <div css={styles.selectedWrapper}>
          <div css={styles.selectedCount}>{students.length} Students selected</div>
          {students?.map((item) => (
            <div key={item.id} css={styles.selectedItem}>
              <div css={styles.selectedThumb}>
                <img src={item.avatar || coursePlaceholder} css={styles.thumbnail} alt="course item" />
              </div>
              <div css={styles.selectedContent}>
                <div css={styles.selectedTitle}>{item.name}</div>
                <div css={styles.selectedSubTitle}>{item.email}</div>
              </div>
              <div data-selected-item-cross>
                <Button variant="text" onClick={() => removesSelectedItem(item.id)}>
                  <SVGIcon name="cross" width={24} height={24} />
                </Button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
export default Students;

const styles = {
  wrapper: css`
    background-color: ${colorTokens.background.white};
    border-radius: ${borderRadius[8]};
  `,
  searchWrapper: css`
    display: flex;
    gap: ${spacing[8]};
    align-items: end;
    padding: ${spacing[16]};
  `,
  selectedWrapper: css``,
  selectedCount: css`
    ${typography.small('medium')};
    color: ${colorTokens.text.title};
    padding: ${spacing[8]} ${spacing[16]};
    border-bottom: 1px solid ${colorTokens.stroke.disable};
  `,
  selectedItem: css`
    padding: ${spacing[8]} ${spacing[8]} ${spacing[8]} ${spacing[16]};
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    transition: background-color 0.25s ease-in;

    &:not(:last-child) {
      border-bottom: 1px solid ${colorTokens.stroke.disable};
    }

    [data-selected-item-cross] {
      visibility: hidden;
    }

    &:hover {
      background-color: ${colorTokens.background.hover};
      [data-selected-item-cross] {
        visibility: visible;
      }
    }
  `,
  selectedContent: css`
    width: 100%;
  `,
  selectedTitle: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.primary};
  `,
  selectedSubTitle: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
  `,
  selectedThumb: css`
    height: 34px;
  `,
  thumbnail: css`
    width: 34px;
    height: 34px;
    border-radius: ${borderRadius.circle};
  `,
};
