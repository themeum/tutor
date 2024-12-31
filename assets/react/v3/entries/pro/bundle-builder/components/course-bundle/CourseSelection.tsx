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

import CourseSelectionHeader from '@BundleBuilderComponents/course-bundle/CourseSelectionHeader';
import SelectedCourseList from '@BundleBuilderComponents/course-bundle/SelectedCourseList';
import SelectionOverview from '@BundleBuilderComponents/course-bundle/SelectionOverview';
import CourseListModal from '@BundleBuilderComponents/modals/CourseListModal';
import { type BundleFormData, type Course } from '@BundleBuilderServices/bundle';

import bundleEmptyState from '@Images/bundle-empty-state.webp';
import { useMemo } from 'react';
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
    keyName: '_id',
  });

  const bundlePrice = priceWithOutCurrencySymbol(form.watch('bundle_price'));
  const bundleSalePrice = priceWithOutCurrencySymbol(form.watch('bundle_sale_price'));
  const selectedCourseIds = useMemo(() => selectedCourses.map((course) => course.id), [selectedCourses]);

  const handleAddCourses = (courses: Course[]) => {
    for (const course of courses) {
      addCourse(course);
    }

    const containsSalePriceCourse = courses.some((course) => course.sale_price);

    form.setValue(
      'bundle_price',
      String(
        bundlePrice +
          courses.reduce((totalPrice, course) => totalPrice + priceWithOutCurrencySymbol(course.regular_price), 0),
      ),
      {
        shouldDirty: true,
      },
    );

    form.setValue(
      'bundle_sale_price',
      containsSalePriceCourse
        ? String(
            bundleSalePrice +
              courses.reduce(
                (totalSalePrice, course) =>
                  totalSalePrice + priceWithOutCurrencySymbol(course.sale_price || course.regular_price),
                0,
              ),
          )
        : '',
      {
        shouldDirty: true,
      },
    );
  };

  const handleRemoveCourse = (index: number) => {
    removeCourse(index);
    const updatedCourses = selectedCourses.filter((_, i) => i !== index);
    const atOneHasSalePrice = updatedCourses.some((course) => course.sale_price);

    const removedCourse = selectedCourses[index];
    form.setValue('bundle_price', String(bundlePrice - priceWithOutCurrencySymbol(removedCourse.regular_price)), {
      shouldDirty: true,
    });

    form.setValue(
      'bundle_sale_price',
      atOneHasSalePrice
        ? String(bundleSalePrice - priceWithOutCurrencySymbol(removedCourse.sale_price || removedCourse.regular_price))
        : '',
      {
        shouldValidate: true,
        shouldDirty: true,
      },
    );
  };

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
                      onAddCourses: handleAddCourses,
                      selectedCourseIds: selectedCourseIds,
                    },
                  });
                }}
              >
                {__('Add Courses', 'tutor')}
              </Button>
            </div>
          }
        >
          <CourseSelectionHeader onAddCourses={handleAddCourses} selectedCourseIds={selectedCourseIds} />

          <SelectedCourseList courses={selectedCourses} onRemove={handleRemoveCourse} onSort={moveCourse} />

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
