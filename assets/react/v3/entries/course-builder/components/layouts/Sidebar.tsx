import ProgressStep from '@Atoms/ProgressStep';
import { defineRoute } from '@Config/route-configs';
import { colorTokens, headerHeight, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useNavigate } from 'react-router-dom';
import { Step, useSidebar } from '../../contexts/SidebarContext';

const Sidebar = () => {
  const navigate = useNavigate();
  const { steps, setSteps } = useSidebar();

  return (
    <div css={styles.sidebar}>
      <div css={styles.progressWrapper}>
        {steps.map((step, idx) => (
          <ProgressStep
            key={idx}
            step={step}
            index={idx}
            onClick={() => {
              setSteps(previous =>
                [...previous].map((item, index) => {
                  return {
                    ...item,
                    isActive: idx === index ? true : item.isActive,
                    isVisited: idx === index ? true : item.isVisited,
                  };
                })
              );

              navigate(step.path);
            }}
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
    border-right: 1px solid ${colorTokens.stroke.divider};
  `,
  progressWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[32]};

    position: sticky;
    top: ${headerHeight + 24}px;
  `,
};
