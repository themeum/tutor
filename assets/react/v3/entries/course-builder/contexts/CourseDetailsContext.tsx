import { type CourseDetailsResponse, useCourseDetailsQuery } from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import type React from 'react';
import { createContext, useContext, useEffect, useState } from 'react';

const courseId = getCourseId();

const CourseDetailsContext = createContext<CourseDetailsResponse | null>(null);

export const useCourseDetails = () => useContext(CourseDetailsContext);

interface CourseDetailsProviderProps {
  children: React.ReactNode;
}

export const CourseDetailsProvider = ({ children }: CourseDetailsProviderProps) => {
  const [courseDetails, setCourseDetails] = useState<CourseDetailsResponse | null>(null);
  const { data: courseDetailsData } = useCourseDetailsQuery(Number(courseId));

  useEffect(() => {
    if (courseDetailsData) {
      setCourseDetails(courseDetailsData);
    }
  }, [courseDetailsData]);

  return <CourseDetailsContext.Provider value={courseDetails}>{children}</CourseDetailsContext.Provider>;
};
