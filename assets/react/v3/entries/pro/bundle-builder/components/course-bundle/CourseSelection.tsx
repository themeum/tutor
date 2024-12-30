import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useFieldArray, useFormContext } from 'react-hook-form';

import { Box } from '@Atoms/Box';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { useModal } from '@Components/modals/Modal';

import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';

import CourseSelectionHeader from '@/v3/entries/pro/bundle-builder/components/course-bundle/CourseSelectionHeader';
import SelectedCourseList from '@/v3/entries/pro/bundle-builder/components/course-bundle/SelectedCourseList';
import SelectionOverview from '@/v3/entries/pro/bundle-builder/components/course-bundle/SelectionOverview';
import CourseListModal from '@BundleBuilderComponents/modals/CourseListModal';
import { BundleFormData } from '@BundleBuilderServices/bundle';

import bundleEmptyState from '@Images/bundle-empty-state.webp';
import { priceWithOutCurrencySymbol } from '../../utils/utils';

const CourseSelection = () => {
  const form = useFormContext<BundleFormData>();
  const { showModal } = useModal();
  const {
    fields: selectedCourses,
    append: addCourse,
    remove: removeCourse,
    move: moveCourse,
  } = useFieldArray({
    control: form.control,
    name: 'courses',
  });

  const bundlePrice = priceWithOutCurrencySymbol(form.watch('bundle_price'));
  const bundleSalePrice = priceWithOutCurrencySymbol(form.watch('bundle_sale_price'));

  return (
    <div css={styles.wrapper}>
      <label css={typography.caption()}>{__('Courses', 'tutor')}</label>
      <Box css={styles.boxWrapper}>
        <Show
          when={selectedCourses.length > 0}
          fallback={
            <div css={styles.emptyState}>
              <img src={bundleEmptyState} alt={__('Empty State', 'tutor')} />
              <p>{__('No Courses Added Yet', 'tutor')}</p>
              <Button
                variant="secondary"
                isOutlined
                icon={<SVGIcon name="plusSquareBrand" width={24} height={24} />}
                css={styles.addCourseButton}
                onClick={() => {
                  showModal({
                    component: CourseListModal,
                    props: {
                      title: __('Add Courses', 'tutor'),
                      onAddCourse: (course) => {
                        addCourse(course);
                      },
                    },
                  });
                }}
              >
                {__('Add Courses', 'tutor')}
              </Button>
            </div>
          }
        >
          <CourseSelectionHeader
            onAddCourses={(course) => {
              addCourse(course);

              form.setValue('bundle_price', String(bundlePrice + priceWithOutCurrencySymbol(course.regular_price)), {
                shouldValidate: true,
                shouldDirty: true,
              });

              form.setValue(
                'bundle_sale_price',
                String(bundleSalePrice + priceWithOutCurrencySymbol(course.sale_price || '0')),
                {
                  shouldValidate: true,
                  shouldDirty: true,
                },
              );
            }}
          />

          <SelectedCourseList
            courses={selectedCourses}
            onRemove={(index) => {
              removeCourse(index);
              const updatedCourses = selectedCourses.filter((_, i) => i !== index);
              const atOneHasSalePrice = updatedCourses.some((course) => course.sale_price);

              const removedCourse = selectedCourses[index];
              form.setValue(
                'bundle_price',
                String(bundlePrice - priceWithOutCurrencySymbol(removedCourse.regular_price)),
                {
                  shouldValidate: true,
                  shouldDirty: true,
                },
              );

              form.setValue(
                'bundle_sale_price',
                atOneHasSalePrice
                  ? String(
                      bundleSalePrice -
                        priceWithOutCurrencySymbol(removedCourse.sale_price || removedCourse.regular_price),
                    )
                  : '',
                {
                  shouldValidate: true,
                  shouldDirty: true,
                },
              );
            }}
            onSort={moveCourse}
          />

          <SelectionOverview />
        </Show>
      </Box>
    </div>
  );
};

export default CourseSelection;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[6]};
  `,
  boxWrapper: css`
    padding-inline: 0;
    border: 1px solid ${colorTokens.stroke.divider};
  `,
  emptyState: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
    align-items: center;
    padding-block: ${spacing[32]};

    img {
      max-width: 60px;
      width: 100%;
      object-fit: contain;
      object-position: center;
    }

    p {
      ${typography.body('medium')};
    }
  `,
  addCourseButton: css`
    outline: 1px solid ${colorTokens.stroke.border};

    &:hover {
      outline: 1px solid ${colorTokens.stroke.border};
    }
  `,
};
