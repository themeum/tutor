import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import CourseCategorySelectModal from '@/v3/shared/components/modals/CourseCategorySelectModal';
import { type BundleFormData } from '@BundleBuilderServices/bundle';
import { useModal } from '@Components/modals/Modal';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormContext } from 'react-hook-form';

const CourseSelectionHeader = () => {
  const { showModal } = useModal();
  const form = useFormContext<BundleFormData>();

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
            component: CourseCategorySelectModal,
            props: {
              title: __('Add Courses', 'tutor'),
              form,
              type: 'courses',
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
