import ProgressStep, { ProgressStatus } from '@Atoms/ProgressStep';
import { defineRoute } from '@Config/route-configs';
import { colorTokens, spacing } from '@Config/styles';
import { CourseProgressSteps, Option } from '@Utils/types';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useNavigate } from 'react-router-dom';

type SidebarProps = {
  progressSteps: Option<string>[];
  activeStep: string;
  setActiveStep: (step: CourseProgressSteps) => void;
  completedSteps: string[];
};

const Sidebar = ({ progressSteps, activeStep, setActiveStep, completedSteps }: SidebarProps) => {
  const navigate = useNavigate();

  const getStatus = (step: Option<string>): ProgressStatus => {
    if (step.value === activeStep) {
      return 'active';
    }

    if (completedSteps.includes(step.value)) {
      return 'completed';
    }

    return 'inactive';
  };

  return (
    <div css={styles.sidebar}>
      <div css={styles.progressWrapper}>
        {progressSteps.map((step, idx) => (
          <ProgressStep key={idx} step={step} status={getStatus(step)} onClick={() => navigate(step.value)} />
        ))}
      </div>
    </div>
  );
};

export default Sidebar;

const styles = {
  sidebar: css`
    padding: ${spacing[24]} 0 0 ${spacing[56]};
    border-right: 1px solid ${colorTokens.stroke.divider};

    html[dir='rtl'] & {
      padding: ${spacing[24]} ${spacing[56]} 0 0;
      border-right: 0;
      border-left: 1px solid ${colorTokens.stroke.divider};
    }
  `,
  progressWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[32]};
  `,
};
