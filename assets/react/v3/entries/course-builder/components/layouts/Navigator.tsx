import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { isRTL } from '@Config/constants';
import { spacing } from '@Config/styles';
import Show from '@Controls/Show';
import routes from '@CourseBuilderConfig/routes';
import { useCourseNavigator } from '@CourseBuilderContexts/CourseNavigatorContext';
import {
  convertCourseDataToPayload,
  useUpdateCourseMutation,
  type CourseFormData,
} from '@CourseBuilderServices/course';
import { useCurrentPath } from '@Hooks/useCurrentPath';
import { css, type SerializedStyles } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';
import { determinePostStatus, getCourseId } from '../../utils/utils';

interface NavigatorProps {
  styleModifier?: SerializedStyles;
}

const courseId = getCourseId();

const Navigator = ({ styleModifier }: NavigatorProps) => {
  const { steps, setSteps } = useCourseNavigator();
  const navigate = useNavigate();
  const currentPath = useCurrentPath(routes);
  const form = useFormContext<CourseFormData>();
  const updateCourseMutation = useUpdateCourseMutation();

  const currentIndex = steps.findIndex((item) => item.path === currentPath);
  const previousIndex = Math.max(-1, currentIndex - 1);
  const nextIndex = Math.min(steps.length, currentIndex + 1);
  const previousStep = steps[previousIndex];
  const nextStep = steps[nextIndex];
  const postTitle = form.watch('post_title');

  const saveCourseData = async () => {
    try {
      await form.handleSubmit(async (data) => {
        const payload = convertCourseDataToPayload(data);

        await updateCourseMutation.mutateAsync({
          course_id: courseId,
          ...payload,
          post_status: determinePostStatus(
            form.getValues('post_status') as 'trash' | 'future' | 'draft',
            form.getValues('visibility') as 'private' | 'password_protected',
          ),
        });
      })();
    } catch (error) {
      console.error(sprintf(__('Failed to update course data before navigating. %s', 'tutor'), error));
    }
  };

  const handlePreviousClick = async () => {
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

    await saveCourseData();
    navigate(previousStep.path);
  };

  const handleNextClick = async () => {
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

    await saveCourseData();
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
          <SVGIcon name={!isRTL ? 'chevronLeft' : 'chevronRight'} height={18} width={18} />
        </Button>
      </Show>
      <Show when={currentIndex < steps.length - 1 && postTitle}>
        <Button
          variant="tertiary"
          icon={<SVGIcon name={!isRTL ? 'chevronRight' : 'chevronLeft'} />}
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
