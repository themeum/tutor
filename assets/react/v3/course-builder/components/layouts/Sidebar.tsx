import ProgressStep, { ProgressStatus } from "@Atoms/ProgressStep";
import { colorPalateTutor, spacing } from "@Config/styles";
import { CourseProgressSteps, Option } from "@Utils/types";
import { css } from "@emotion/react";
import { __ } from "@wordpress/i18n";

type SidebarProps = {
  progressSteps: Option<CourseProgressSteps>[];
  activeStep: CourseProgressSteps;
  setActiveStep: (step: CourseProgressSteps) => void;
  completedSteps: CourseProgressSteps[];
};

const Sidebar = ({
  progressSteps,
  activeStep,
  setActiveStep,
  completedSteps,
}: SidebarProps) => {
  const getStatus = (step: Option<CourseProgressSteps>): ProgressStatus => {
    if (completedSteps.includes(step.value)) {
      return "completed";
    }

    if (step.value === activeStep) {
      return "active";
    }

    return "inactive";
  };

  return (
    <div css={styles.sidebar}>
      <div css={styles.progressWrapper}>
        {progressSteps.map((step, idx) => (
          <ProgressStep
            key={idx}
            step={step}
            status={getStatus(step)}
            onClick={setActiveStep}
          />
        ))}
      </div>
    </div>
  );
};

export default Sidebar;

const styles = {
  sidebar: css`
    padding: ${spacing[24]} 0 0 ${spacing[56]};
    border-right: 1px solid ${colorPalateTutor.stroke.divider};

    html[dir="rtl"] & {
      padding: ${spacing[24]} ${spacing[56]} 0 0;
      border-right: 0;
      border-left: 1px solid ${colorPalateTutor.stroke.divider};
    }
  `,
  progressWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[32]};
  `,
};
