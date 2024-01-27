import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import Header from '@CourseBuilderComponents/layouts/Header';
import Sidebar from '@CourseBuilderComponents/layouts/Sidebar';
import Footer from '@CourseBuilderComponents/layouts/Footer';
import { css } from '@emotion/react';
import { footerHeight, headerHeight } from '@Config/styles';
import { CourseProgressSteps, Option } from '@Utils/types';
import { Outlet } from 'react-router-dom';
import { CourseBuilderRouteConfigs } from '@CourseBuilderConfig/route-configs';
import { RouteDefinition } from '@Config/route-configs';

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
  const [activeStep, setActiveStep] = useState<CourseProgressSteps>('basic');
  const [completedSteps, setCompletedSteps] = useState<CourseProgressSteps[]>(['basic']);

  const getCompletion = () => {
    const totalSteps = progressSteps.length;
    const curriculumIndex = progressSteps.findIndex(item => item.value === activeStep);
    return (100 / totalSteps) * (curriculumIndex + 1);
  };

  const handleNextClick = () => {
    const curriculumIndex = progressSteps.findIndex(item => item.value === activeStep);
    if (curriculumIndex < progressSteps.length - 1) {
      // setActiveStep(progressSteps[curriculumIndex + 1].value);
    }
  };

  const handlePrevClick = () => {
    const curriculumIndex = progressSteps.findIndex(item => item.value === activeStep);
    if (curriculumIndex > 0) {
      // setActiveStep(progressSteps[curriculumIndex - 1].value);
    }
  };

  return (
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
    max-width: 1140px;
  `,
};
