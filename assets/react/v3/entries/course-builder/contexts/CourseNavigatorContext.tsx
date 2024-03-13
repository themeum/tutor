import { CourseBuilderRouteConfigs } from '@CourseBuilderConfig/route-configs';
import routes from '@CourseBuilderConfig/routes';
import { CourseDetailsResponse, CourseBuilderSteps, useCourseDetailsQuery } from '@CourseBuilderServices/course';
import { useCurrentPath } from '@Hooks/useCurrentPath';
import { isDefined } from '@Utils/types';
import { noop } from '@Utils/util';
import { __ } from '@wordpress/i18n';
import React, { useCallback, useContext, useEffect, useMemo, useState } from 'react';

export type StepStatus = 'active' | 'completed' | 'inactive' | 'visited';
export type CompletionStatus = 'incomplete' | 'complete';

export interface Step {
  indicator: number;
  id: CourseBuilderSteps;
  label: string;
  path: string;
  isDisabled: boolean;
  isCompleted: boolean;
  isActive: boolean;
  isVisited: boolean;
}

interface CourseNavigatorContextType {
  steps: Step[];
  currentIndex: number;
  courseContent: CourseDetailsResponse | null;
  setSteps: React.Dispatch<React.SetStateAction<Step[]>>;
  updateStepByIndex: (index: number, updatedStep: Step) => void;
}

const defaultSteps: Step[] = [
  {
    indicator: 1,
    id: 'basic',
    label: __('Course Basic', 'tutor'),
    path: CourseBuilderRouteConfigs.CourseBasics.buildLink(),
    isDisabled: false,
    isVisited: true,
    isCompleted: false,
    isActive: true,
  },
  {
    indicator: 2,
    id: 'curriculum',
    label: __('Curriculum', 'tutor'),
    path: CourseBuilderRouteConfigs.CourseCurriculum.buildLink(),
    isDisabled: true,
    isCompleted: false,
    isVisited: false,
    isActive: false,
  },
  {
    indicator: 3,
    id: 'additional',
    label: __('Additional', 'tutor'),
    path: CourseBuilderRouteConfigs.CourseAdditional.buildLink(),
    isDisabled: true,
    isCompleted: false,
    isVisited: false,
    isActive: false,
  },
];

const CourseNavigatorContext = React.createContext<CourseNavigatorContextType>({
  steps: defaultSteps,
  setSteps: noop,
  updateStepByIndex: noop,
  currentIndex: 0,
  courseContent: null,
});

export const useCourseNavigator = () => useContext(CourseNavigatorContext);

interface CourseNavigatorProviderProps {
  children: React.ReactNode;
}

export const CourseNavigatorProvider = ({ children }: CourseNavigatorProviderProps) => {
  const [steps, setSteps] = useState<Step[]>(defaultSteps);
  const currentPath = useCurrentPath(routes);
  const params = new URLSearchParams(window.location.search);
  const courseId = params.get('course_id');
  const courseDetailsQuery = useCourseDetailsQuery(Number(courseId));

  const courseContent = useMemo(() => {
    if (!courseDetailsQuery.data) {
      return null;
    }

    return courseDetailsQuery.data;
  }, [courseDetailsQuery.data]);

  const updateStepByIndex = useCallback((index: number, updatedStep: Partial<Step>) => {
    setSteps(previous => {
      return previous.map((step, idx) => {
        if (idx === index) {
          return { ...step, ...updatedStep };
        }
        return step;
      });
    });
  }, []);

  const currentIndex = useMemo(() => {
    return steps.findIndex(step => step.path === currentPath);
  }, [steps, currentPath]);

  useEffect(() => {
    setSteps(previous =>
      previous.map((step, index) => {
        return {
          ...step,
          isActive: index === currentIndex,
        };
      })
    );
  }, [currentIndex]);

  useEffect(() => {
    if (!isDefined(courseContent)) {
      return;
    }
    setSteps(previous =>
      previous.map(step => {
        return {
          ...step,
          isCompleted: courseContent.step_completion_status[step.id],
          isDisabled: false,
        };
      })
    );
  }, [courseContent]);

  return (
    <CourseNavigatorContext.Provider value={{ steps, setSteps, updateStepByIndex, currentIndex, courseContent }}>
      {children}
    </CourseNavigatorContext.Provider>
  );
};
