import { useCourseBuilderSlot } from '@CourseBuilderContexts/CourseBuilderSlotProvider';
import React from 'react';
import ContentRenderer from './ContentRenderer';
import FieldRenderer from './FieldRenderer';

const CourseBuilderInjectionSlot: React.FC<{ section: string; form: any }> = ({ section, form }) => {
  const { fields } = useCourseBuilderSlot();

  return (
    <>
      {/* Render injected fields */}
      {fields[section]?.map((props) => <FieldRenderer key={props.name} form={form} {...props} />)}

      {/* Render injected custom content */}
      <ContentRenderer section={section} />
    </>
  );
};

export default CourseBuilderInjectionSlot;
