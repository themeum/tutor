import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { Controller, useFormContext } from 'react-hook-form';
import { Box } from '@Atoms/Box';
import { requiredRule } from '@Utils/validation';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import { __ } from '@wordpress/i18n';
import SVGIcon from '@Atoms/SVGIcon';
import Button from '@Atoms/Button';
import SelectCourseModal from '@EnrollmentComponents/modals/CourseListModal';
import { useModal } from '@Components/modals/Modal';
import { Enrollment } from '@EnrollmentServices/enrollment';
import coursePlaceholder from '@Images/common/course-placeholder.png';
import { typography } from '@Config/typography';

function Courses() {
  const form = useFormContext<Enrollment>();
  const { showModal } = useModal();

  const courses = form.watch('courses') ?? [];

  function removesSelectedItem(id: number) {
    form.setValue(
      'courses',
      courses?.filter((item) => item.id !== id)
    );
  }
  return (
    <Box bordered>
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
              component: SelectCourseModal,
              props: {
                title: __('Selected items', 'tutor'),
                form,
              },
              closeOnOutsideClick: true,
            });
          }}
        >
          {__('Browse', 'tutor')}
        </Button>
      </div>

      {courses.length > 0 && (
        <div css={styles.selectedWrapper}>
          {courses?.map((item) => (
            <div key={item.id} css={styles.selectedItem}>
              <div css={styles.selectedThumb}>
                <img src={item.image || coursePlaceholder} css={styles.thumbnail} alt="course item" />
              </div>
              <div css={styles.selectedContent}>
                <div css={styles.selectedTitle}>{item.title}</div>
                <div css={styles.selectedSubTitle}>
                  <div css={styles.price}>
                    <span>{item.sale_price ? item.sale_price : item.regular_price}</span>
                    {item.sale_price && <span css={styles.discountPrice}>{item.regular_price}</span>}
                  </div>
                </div>
              </div>
              <div>
                <Button variant="text" onClick={() => removesSelectedItem(item.id)}>
                  <SVGIcon name="delete" width={24} height={24} />
                </Button>
              </div>
            </div>
          ))}
        </div>
      )}
    </Box>
  );
}
export default Courses;

const styles = {
  searchWrapper: css`
    display: flex;
    gap: ${spacing[8]};
    align-items: end;
  `,
  price: css`
    display: flex;
    gap: ${spacing[4]};
  `,
  discountPrice: css`
    text-decoration: line-through;
  `,
  selectedWrapper: css`
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[6]};
  `,
  selectedItem: css`
    padding: ${spacing[12]};
    display: flex;
    align-items: center;
    gap: ${spacing[16]};

    &:not(:last-child) {
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    }
  `,
  selectedContent: css`
    width: 100%;
  `,
  selectedTitle: css`
    ${typography.small()};
    color: ${colorTokens.text.primary};
    margin-bottom: ${spacing[4]};
  `,
  selectedSubTitle: css`
    ${typography.small()};
    color: ${colorTokens.text.hints};
  `,
  selectedThumb: css`
    height: 48px;
  `,
  thumbnail: css`
    width: 48px;
    height: 48px;
    border-radius: ${borderRadius[4]};
  `,
};
