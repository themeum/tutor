import { css } from '@emotion/react';
import { useEffect } from 'react';
import { FormProvider } from 'react-hook-form';
import { Outlet } from 'react-router-dom';

import { Breakpoint, colorTokens, containerMaxWidth, headerHeight, spacing } from '@Config/styles';
import Header from '@CourseBuilderComponents/layouts/header/Header';
import { CourseNavigatorProvider } from '@CourseBuilderContexts/CourseNavigatorContext';
import {
  type CourseFormData,
  convertCourseDataToFormData,
  courseDefaultData,
  useCourseDetailsQuery,
} from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';

import Notebook from './Notebook';

const Layout = () => {
  const courseId = getCourseId();

  const form = useFormWithGlobalError<CourseFormData>({
    defaultValues: courseDefaultData,
    shouldFocusError: true,
    mode: 'onChange',
  });

  const courseDetailsQuery = useCourseDetailsQuery(Number(courseId));

  useEffect(() => {
    if (courseDetailsQuery.data) {
      form.reset(convertCourseDataToFormData(courseDetailsQuery.data), {
        keepDirtyValues: true,
        keepDirty: true,
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [courseDetailsQuery.data]);

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
