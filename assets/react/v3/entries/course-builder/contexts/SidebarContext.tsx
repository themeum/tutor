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
  id: CourseBuilderSteps;
  label: string;
  path: string;
  isDisabled: boolean;
  isCompleted: boolean;
  isActive: boolean;
  isVisited: boolean;
}

interface SidebarContextType {
  steps: Step[];
  currentIndex: number;
  courseContent: CourseDetailsResponse | null;
  setSteps: React.Dispatch<React.SetStateAction<Step[]>>;
  updateStepByIndex: (index: number, updatedStep: Step) => void;
}

const defaultSteps: Step[] = [
  {
    id: 'basic',
    label: __('Course Basic', 'tutor'),
    path: CourseBuilderRouteConfigs.CourseBasics.buildLink(),
    isDisabled: false,
    isVisited: true,
    isCompleted: false,
    isActive: true,
  },
  {
    id: 'curriculum',
    label: __('Curriculum', 'tutor'),
    path: CourseBuilderRouteConfigs.CourseCurriculum.buildLink(),
    isDisabled: true,
    isCompleted: false,
    isVisited: false,
    isActive: false,
  },
  {
    id: 'additional',
    label: __('Additional', 'tutor'),
    path: CourseBuilderRouteConfigs.CourseAdditional.buildLink(),
    isDisabled: true,
    isCompleted: false,
    isVisited: false,
    isActive: false,
  },
  {
    id: 'certificate',
    label: __('Certificate', 'tutor'),
    path: CourseBuilderRouteConfigs.CourseCertificate.buildLink(),
    isDisabled: true,
    isCompleted: false,
    isVisited: false,
    isActive: false,
  },
];

const SidebarContext = React.createContext<SidebarContextType>({
  steps: defaultSteps,
  setSteps: noop,
  updateStepByIndex: noop,
  currentIndex: 0,
  courseContent: null,
});

export const useSidebar = () => useContext(SidebarContext);

interface SidebarProviderProps {
  children: React.ReactNode;
}

export const SidebarProvider = ({ children }: SidebarProviderProps) => {
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
      [...previous].map((step, index) => {
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
      [...previous].map(step => {
        return {
          ...step,
          isCompleted: courseContent.step_completion_status[step.id],
          isDisabled: false,
        };
      })
    );
  }, [courseContent]);

  return (
    <SidebarContext.Provider value={{ steps, setSteps, updateStepByIndex, currentIndex, courseContent }}>
      {children}
    </SidebarContext.Provider>
  );
};
