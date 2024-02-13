import React, { useEffect, useState } from 'react';
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

const progressSteps: Option<string>[] = [
  {
    label: __('Course Basic', 'tutor'),
    value: CourseBuilderRouteConfigs.CourseBasics.buildLink(),
  },
  {
    label: __('Curriculum', 'tutor'),
    value: CourseBuilderRouteConfigs.CourseCurriculum.buildLink(),
  },
  {
    label: __('Additional', 'tutor'),
    value: CourseBuilderRouteConfigs.CourseAdditional.buildLink(),
  },
  {
    label: __('Certificate', 'tutor'),
    value: CourseBuilderRouteConfigs.CourseCertificate.buildLink(),
  },
];

const Layout: React.FC = () => {
  const params = new URLSearchParams(window.location.href);
  const courseId = params.get('course-id')?.split('#')[0];

  const currentPath = useCurrentPath(routes);
  const navigate = useNavigate();

  const courseBasicForm = useFormWithGlobalError<CourseFormData>({
    defaultValues: courseDefaultData,
  });

  const [activeStep, setActiveStep] = useState<string>(currentPath);
  const [completedSteps, setCompletedSteps] = useState<string[]>([currentPath]);

  const courseDetailsQuery = useCourseDetailsQuery(Number(courseId));

  useEffect(() => {
    if (courseDetailsQuery.data) {
      courseBasicForm.reset(convertCourseDataToFormData(courseDetailsQuery.data));
    }
  }, [courseDetailsQuery.data]);

  useEffect(() => {
    setActiveStep(currentPath);
    setCompletedSteps((previous) =>
      previous.includes(currentPath) ? previous.filter((path) => path !== currentPath) : [...previous, currentPath]
    );
  }, [currentPath]);

  const getCompletion = () => {
    const totalSteps = progressSteps.length;
    const curriculumIndex = progressSteps.findIndex((item) => item.value === activeStep);
    return (100 / totalSteps) * (curriculumIndex + 1);
  };

  const handleNextClick = () => {
    const curriculumIndex = progressSteps.findIndex((item) => item.value === activeStep);
    if (curriculumIndex < progressSteps.length - 1) {
      const pagePath = progressSteps[curriculumIndex + 1].value;
      navigate(pagePath);
    }
  };

  const handlePrevClick = () => {
    const curriculumIndex = progressSteps.findIndex((item) => item.value === activeStep);
    if (curriculumIndex > 0) {
      const pagePath = progressSteps[curriculumIndex - 1].value;
      navigate(pagePath);
    }
  };

  return (
    <FormProvider {...courseBasicForm}>
      <div css={styles.wrapper}>
        <Header />
        <div css={styles.contentWrapper}>
          <Sidebar
            progressSteps={progressSteps}
            activeStep={activeStep}
            setActiveStep={setActiveStep}
            completedSteps={completedSteps}
          />
          <div css={styles.mainContent}>
            <Outlet />
          </div>
        </div>
        <Footer completion={getCompletion()} onNextClick={handleNextClick} onPrevClick={handlePrevClick} />
      </div>
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
