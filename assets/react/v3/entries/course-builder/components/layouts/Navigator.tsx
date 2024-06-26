import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { spacing } from '@Config/styles';
import routes from '@CourseBuilderConfig/routes';
import { useCourseNavigator } from '@CourseBuilderContexts/CourseNavigatorContext';
import { useCurrentPath } from '@Hooks/useCurrentPath';
import { type SerializedStyles, css } from '@emotion/react';
import { useNavigate } from 'react-router-dom';

interface NavigatorProps {
  styleModifier?: SerializedStyles;
}

const Navigator = ({ styleModifier }: NavigatorProps) => {
  const { steps, setSteps } = useCourseNavigator();
  const navigate = useNavigate();
  const currentPath = useCurrentPath(routes);

  const handlePreviousClick = () => {
    const currentIndex = steps.findIndex((item) => item.path === currentPath);
    const previousIndex = Math.max(0, currentIndex - 1);
    const previousStep = steps[previousIndex];

    setSteps((previous) => {
      return previous.map((item, index) => {
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
    const currentIndex = steps.findIndex((item) => item.path === currentPath);
    const nextIndex = Math.min(steps.length - 1, currentIndex + 1);
    const nextStep = steps[nextIndex];

    setSteps((previous) => {
      return previous.map((item, index) => {
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
    <div css={[styles.wrapper, styleModifier]}>
      <Button
        variant="tertiary"
        iconPosition="right"
        size="small"
        onClick={handleNextClick}
      >
        <SVGIcon name="chevronLeft" height={18} width={18} />
      </Button>
      <Button
        variant="tertiary"
        icon={<SVGIcon name="chevronRight" />}
        iconPosition="right"
        size="small"
        onClick={handleNextClick}
      >
        Next
      </Button>
    </div>
  );
};

export default Navigator;

const styles = {
  wrapper: css`
    width: 100%;
    display: flex;
    justify-content: end;
    height: 32px;
    align-items: center;
    gap: ${spacing[16]};

    & > button:last-of-type {
      padding-right: ${spacing[8]};
    }
  `,
};
