import { useCourseBuilderSlot } from '@CourseBuilderContexts/CourseBuilderSlotProvider';
import React from 'react';

const ContentRenderer: React.FC<{ section: string }> = ({ section }) => {
  const { contents } = useCourseBuilderSlot();

  return (
    <>{contents[section]?.map(({ component }, index) => <React.Fragment key={index}>{component}</React.Fragment>)}</>
  );
};

export default ContentRenderer;
