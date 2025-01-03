import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
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

import { LoadingSection } from '@/v3/shared/atoms/LoadingSpinner';
import CourseListModal from '@BundleBuilderComponents/modals/CourseListModal';
import { useAddCourseToBundleMutation, type BundleFormData, type Course } from '@BundleBuilderServices/bundle';
import { getBundleId, priceWithOutCurrencySymbol } from '@BundleBuilderUtils/utils';
import bundleEmptyState from '@Images/bundle-empty-state.webp';
import { useIsFetching } from '@tanstack/react-query';

const bundleId = getBundleId();

const CourseSelection = () => {
  const form = useFormContext<BundleFormData>();
  const { showModal } = useModal();
  const {
    fields: selectedCourses,
    remove: removeCourse,
    move: moveCourse,
  } = useFieldArray({
    control: form.control,
    name: 'courses',
    keyName: '_id',
  });

  const addOrRemoveCourseMutation = useAddCourseToBundleMutation();
  const isBundleDetailsFetching = useIsFetching({
    queryKey: ['CourseBundle', bundleId],
  });

  const bundlePrice = priceWithOutCurrencySymbol(form.watch('regular_price'));
  const bundleSalePrice = priceWithOutCurrencySymbol(form.watch('sale_price'));

  const handleAddCourse = async (course: Course) => {
    const response = await addOrRemoveCourseMutation.mutateAsync({
      ID: bundleId,
      course_id: course.id,
      user_action: 'add_course',
    });

    if (response.data) {
      form.setValue('courses', [...selectedCourses, course]);
    }
  };

  const handleRemoveCourse = async (index: number) => {
    removeCourse(index);
    const updatedCourses = selectedCourses.filter((_, i) => i !== index);
    const atOneHasSalePrice = updatedCourses.some((course) => course.sale_price);

    const removedCourse = selectedCourses[index];
    if (!removedCourse) {
      return;
    }

    const response = await addOrRemoveCourseMutation.mutateAsync({
      ID: bundleId,
      course_id: removedCourse.id,
      user_action: 'remove_course',
    });

    if (response.data) {
      form.setValue('regular_price', String(bundlePrice - priceWithOutCurrencySymbol(removedCourse.regular_price)), {
        shouldDirty: true,
      });

      form.setValue(
        'sale_price',
        atOneHasSalePrice
          ? String(
              bundleSalePrice - priceWithOutCurrencySymbol(removedCourse.sale_price || removedCourse.regular_price),
            )
          : '',
        {
          shouldValidate: true,
          shouldDirty: true,
        },
      );
    }
  };

  useEffect(() => {
    if (selectedCourses.length === 0) {
      form.setValue('regular_price', '0', {
        shouldDirty: true,
      });
      form.setValue('sale_price', '', {
        shouldDirty: true,
      });
    }

    const containsSalePriceCourse = selectedCourses.some((course) => course.sale_price);

    form.setValue(
      'regular_price',
      String(
        selectedCourses.reduce(
          (totalPrice, course) => totalPrice + priceWithOutCurrencySymbol(course.regular_price),
          0,
        ),
      ),
      {
        shouldDirty: true,
      },
    );

    form.setValue(
      'sale_price',
      containsSalePriceCourse
        ? String(
            selectedCourses.reduce(
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
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedCourses.length]);

  return (
    <div css={styles.wrapper}>
      <label css={typography.caption()}>{__('Courses', 'tutor')}</label>
      <Box css={styles.boxWrapper}>
        <Show when={!isBundleDetailsFetching} fallback={<LoadingSection />}>
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
                        onSelect: handleAddCourse,
                        selectedCourseIds: selectedCourses.map((course) => course.id),
                      },
                    });
                  }}
                >
                  {__('Add Courses', 'tutor')}
                </Button>
              </div>
            }
          >
            <CourseSelectionHeader />

            <SelectedCourseList courses={selectedCourses} onRemove={handleRemoveCourse} onSort={moveCourse} />

            <SelectionOverview />
          </Show>
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
