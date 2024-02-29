import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { __ } from '@wordpress/i18n';
import Header from '@CourseBuilderComponents/layouts/Header';
import Sidebar from '@CourseBuilderComponents/layouts/Sidebar';
import Footer from '@CourseBuilderComponents/layouts/Footer';
import { css } from '@emotion/react';
import { footerHeight, headerHeight } from '@Config/styles';
import { Option } from '@Utils/types';
import { Outlet, useNavigate } from 'react-router-dom';
import { CourseBuilderRouteConfigs } from '@CourseBuilderConfig/route-configs';
import { useCurrentPath } from '@Hooks/useCurrentPath';
import routes from '@CourseBuilderConfig/routes';
import { FormProvider } from 'react-hook-form';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { CourseFormData, courseDefaultData, useCourseDetailsQuery } from '@CourseBuilderServices/course';
import { convertCourseDataToFormData } from '@CourseBuilderUtils/utils';
import { SidebarProvider, useSidebar } from '../../contexts/SidebarContext';

const Layout: React.FC = () => {
  const params = new URLSearchParams(window.location.search);
  const courseId = params.get('course_id');

  const form = useFormWithGlobalError<CourseFormData>({
    defaultValues: courseDefaultData,
  });

  const courseDetailsQuery = useCourseDetailsQuery(Number(courseId));

  useEffect(() => {
    if (courseDetailsQuery.data) {
      form.reset(convertCourseDataToFormData(courseDetailsQuery.data));
    }
  }, [courseDetailsQuery.data]);

  return (
    <FormProvider {...form}>
      <SidebarProvider>
        <div css={styles.wrapper}>
          <Header />
          <div css={styles.contentWrapper}>
            <Sidebar />
            <div css={styles.mainContent}>
              <Outlet />
            </div>
          </div>
          <Footer />
        </div>
      </SidebarProvider>
    </FormProvider>
  );
};

export default Layout;

const styles = {
  wrapper: {},
  contentWrapper: css`
    display: grid;
    grid-template-columns: 320px 1fr;
    min-height: calc(100vh - (${headerHeight}px + ${footerHeight}px));
  `,
  mainContent: css`
    max-width: 1170px;
  `,
};
