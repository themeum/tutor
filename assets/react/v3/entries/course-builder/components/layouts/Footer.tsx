import Button, { ButtonSize, ButtonVariant } from '@Atoms/Button';
import { colorPalate, colorTokens, spacing, zIndex } from '@Config/styles';
import routes from '@CourseBuilderConfig/routes';
import { useSidebar } from '@CourseBuilderContexts/SidebarContext';
import { useCurrentPath } from '@Hooks/useCurrentPath';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useMemo } from 'react';
import { useNavigate } from 'react-router-dom';

const Footer = () => {
  const { steps, setSteps } = useSidebar();
  const currentPath = useCurrentPath(routes);
  const navigate = useNavigate();

  const completion = useMemo(() => {
    const totalSteps = steps.length;
    const curriculumIndex = steps.findIndex(item => item.path === currentPath);
    return (100 / totalSteps) * (curriculumIndex + 1);
  }, [steps, currentPath]);

  const handlePreviousClick = () => {
    const currentIndex = steps.findIndex(item => item.path === currentPath);
    const previousIndex = Math.max(0, currentIndex - 1);
    const previousStep = steps[previousIndex];

    setSteps(previous => {
      return [...previous].map((item, index) => {
        if (index === currentIndex) {
          return {
            ...item,
            isVisited: true,
            isActive: false,
          };
        }

        if (index === previousIndex) {
          return {
            ...item,
            isVisited: true,
            isActive: true,
          };
        }

        return item;
      });
    });

    navigate(previousStep.path);
  };

  const handleNextClick = () => {
    const currentIndex = steps.findIndex(item => item.path === currentPath);
    const nextIndex = Math.min(steps.length - 1, currentIndex + 1);
    const nextStep = steps[nextIndex];

    setSteps(previous => {
      return [...previous].map((item, index) => {
        if (index === currentIndex) {
          return {
            ...item,
            isVisited: true,
            isCompleted: true,
            isActive: false,
          };
        }

        if (index === nextIndex) {
          return {
            ...item,
            isVisited: true,
            isActive: true,
          };
        }

        return item;
      });
    });

    navigate(nextStep.path);
  };

  return (
    <div css={styles.wrapper(completion)}>
      <div css={styles.buttonWrapper}>
        <Button variant="secondary" size="small" onClick={handlePreviousClick}>
          {__('Previous', 'tutor')}
        </Button>
        <Button variant="secondary" size="small" onClick={handleNextClick}>
          {__('Next', 'tutor')}
        </Button>
      </div>
    </div>
  );
};

export default Footer;

const styles = {
  wrapper: (completion: number) => css`
    background-color: ${colorTokens.primary[30]};
    padding: ${spacing[12]} ${spacing[16]};
    position: sticky;
    bottom: 0;
    z-index: ${zIndex.footer};

    &::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      background-color: ${colorTokens.color.black[10]};
      height: 2px;
      width: 100%;
    }

    &::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      background-color: ${colorTokens.primary[80]};
      height: 2px;
      width: ${completion}%;
      transition: 0.35s ease-in-out;
    }
  `,
  buttonWrapper: css`
    max-width: 1000px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
  `,
};
