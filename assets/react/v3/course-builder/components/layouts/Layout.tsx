import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import Header from "@CBComponents/layouts/Header";
import Sidebar from "@CBComponents/layouts/Sidebar";
import Footer from "@CBComponents/layouts/Footer";
import { css } from "@emotion/react";
import { footerHeight, headerHeight } from "@Config/styles";
import { CourseProgressSteps, Option } from "@Utils/types";
import CourseBasic from "@CBComponents/CourseBasic";
import Curriculum from "@CBComponents/Curriculum";
import Additionals from "@CBComponents/Additionals";
import Certificate from "@CBComponents/Certificate";

const progressSteps: Option<CourseProgressSteps>[] = [
  {
    label: __("Course Basic", "tutor"),
    value: "basic",
  },
  {
    label: __("Curriculum", "tutor"),
    value: "curriculum",
  },
  {
    label: __("Additionals", "tutor"),
    value: "additionals",
  },
  {
    label: __("Certificate", "tutor"),
    value: "certificate",
  },
];

const Layout: React.FC = () => {
  const [activeStep, setActiveStep] = useState<CourseProgressSteps>("basic");
  const [completedSteps, setCompletedSteps] = useState<CourseProgressSteps[]>(["basic"]);

  const mainContents = {
    basic: <CourseBasic />,
    curriculum: <Curriculum />,
    additionals: <Additionals />,
    certificate: <Certificate />,
  };

  const getCompletion = () => {
    const totalSteps = progressSteps.length;
    const curriculumIndex = progressSteps.findIndex(item => item.value === activeStep);
    return (100 / totalSteps) * (curriculumIndex + 1);
  }

  const handleNextClick = () => {
    const curriculumIndex = progressSteps.findIndex(item => item.value === activeStep);    
    if (curriculumIndex < (progressSteps.length - 1)) {
      setActiveStep(progressSteps[curriculumIndex + 1].value);
    }
  }

  const handlePrevClick = () => {
    const curriculumIndex = progressSteps.findIndex(item => item.value === activeStep);
    if (curriculumIndex > 0) {
      setActiveStep(progressSteps[curriculumIndex - 1].value);
    }
  }

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
        <div css={styles.mainContent}>{mainContents[activeStep]}</div>
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
