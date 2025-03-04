import { css } from '@emotion/react';
import { useEffect } from 'react';
import { FormProvider } from 'react-hook-form';
import { Outlet } from 'react-router-dom';

import Header from '@CourseBuilderComponents/layouts/header/Header';
import { CourseNavigatorProvider } from '@CourseBuilderContexts/CourseNavigatorContext';
import {
  type CourseFormData,
  convertCourseDataToFormData,
  courseDefaultData,
  useCourseDetailsQuery,
} from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { Breakpoint, colorTokens, containerMaxWidth, headerHeight, spacing } from '@TutorShared/config/styles';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';

import { useCourseBuilderSlot } from '@CourseBuilderContexts/CourseBuilderSlotContext';
import { findSlotFields } from '@TutorShared/utils/util';
import Notebook from './Notebook';

const Layout = () => {
  const { fields } = useCourseBuilderSlot();
  const courseId = getCourseId();

  const form = useFormWithGlobalError<CourseFormData>({
    defaultValues: courseDefaultData,
    shouldFocusError: true,
    mode: 'onChange',
  });

  const courseDetailsQuery = useCourseDetailsQuery(Number(courseId));

  useEffect(() => {
    if (courseDetailsQuery.data) {
      const dirtyFields = Object.keys(form.formState.dirtyFields);
      const convertedCourseData = convertCourseDataToFormData(
        courseDetailsQuery.data,
        findSlotFields({ fields: fields.Basic }, { fields: fields.Additional }),
      );
      const formValues = form.getValues();

      const updatedCourseData = Object.entries(convertedCourseData).reduce<Partial<CourseFormData>>(
        (courseFormData, [key, value]) => {
          const typedKey = key as keyof CourseFormData;
          courseFormData[typedKey] = dirtyFields.includes(key) ? formValues[typedKey] : value;
          return courseFormData;
        },
        {},
      );

      form.reset(updatedCourseData, {
        keepDirtyValues: true,
        keepDirty: true,
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [courseDetailsQuery.data, form.reset]);

  return (
    <FormProvider {...form}>
      <CourseNavigatorProvider>
        <div css={styles.wrapper}>
          <Header />
          <div css={styles.contentWrapper}>
            <Outlet />
          </div>
          <Notebook />
        </div>
      </CourseNavigatorProvider>
    </FormProvider>
  );
};

export default Layout;

const styles = {
  wrapper: css`
    background-color: ${colorTokens.surface.courseBuilder};
  `,
  contentWrapper: css`
    display: flex;
    max-width: ${containerMaxWidth}px;
    width: 100%;
    min-height: calc(100vh - ${headerHeight}px);
    margin: 0 auto;

    ${Breakpoint.smallTablet} {
      padding-inline: ${spacing[12]};
      padding-bottom: ${spacing[56]};
    }
  `,
};
