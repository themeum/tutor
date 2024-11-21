import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { spacing } from '@Config/styles';
import Show from '@Controls/Show';
import routes from '@CourseBuilderConfig/routes';
import { useCourseNavigator } from '@CourseBuilderContexts/CourseNavigatorContext';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { useCurrentPath } from '@Hooks/useCurrentPath';
import { type SerializedStyles, css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';

interface NavigatorProps {
  styleModifier?: SerializedStyles;
}

const Navigator = ({ styleModifier }: NavigatorProps) => {
  const { steps, setSteps } = useCourseNavigator();
  const navigate = useNavigate();
  const currentPath = useCurrentPath(routes);
  const form = useFormContext<CourseFormData>();

  const currentIndex = steps.findIndex((item) => item.path === currentPath);
  const previousIndex = Math.max(-1, currentIndex - 1);
  const nextIndex = Math.min(steps.length, currentIndex + 1);
  const previousStep = steps[previousIndex];
  const nextStep = steps[nextIndex];
  const postTitle = form.watch('post_title');

  const handlePreviousClick = () => {
    setSteps((previous) => {
      return previous.map((item, index) => {
        if (index === currentIndex) {
          return {
            ...item,
            isActive: false,
          };
        }

        if (index === previousIndex) {
          return {
            ...item,
            isActive: true,
          };
        }

        return item;
      });
    });

    navigate(previousStep.path);
  };

  const handleNextClick = () => {
    setSteps((previous) => {
      return previous.map((item, index) => {
        if (index === currentIndex) {
          return {
            ...item,
            isActive: false,
          };
        }

        if (index === nextIndex) {
          return {
            ...item,
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
      <Show when={currentIndex > 0}>
        <Button
          variant="tertiary"
          iconPosition="right"
          size="small"
          onClick={handlePreviousClick}
          buttonCss={css`
          padding: ${spacing[6]};
        `}
          disabled={previousIndex < 0}
        >
          <SVGIcon name="chevronLeft" height={18} width={18} />
        </Button>
      </Show>
      <Show when={currentIndex < steps.length - 1 && postTitle}>
        <Button
          variant="tertiary"
          icon={<SVGIcon name="chevronRight" />}
          iconPosition="right"
          size="small"
          onClick={handleNextClick}
          disabled={!postTitle || nextIndex >= steps.length}
        >
          {__('Next', 'tutor')}
        </Button>
      </Show>
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
