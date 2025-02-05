import {
  type InjectedContent,
  type InjectedField,
  useCourseBuilderSlot,
} from '@CourseBuilderContexts/CourseBuilderSlotProvider';
import { type SectionPath } from '@TutorShared/utils/types';
import React from 'react';
import { type UseFormReturn } from 'react-hook-form';
import ContentRenderer from './ContentRenderer';
import FieldRenderer from './FieldRenderer';

const CourseBuilderInjectionSlot: React.FC<{
  section: SectionPath;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<any>;
}> = ({ section, form }) => {
  const { fields, contents } = useCourseBuilderSlot();
  const getNestedFields = (): InjectedField[] => {
    const parts = section.split('.');
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    let current: any = fields;

    for (const part of parts) {
      if (!current[part]) return [];
      current = current[part];
    }
    return Array.isArray(current) ? current : [];
  };

  const getNestedContent = (): InjectedContent[] => {
    const parts = section.split('.');
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    let current: any = contents;

    for (const part of parts) {
      if (!current[part]) return [];
      current = current[part];
    }
    return Array.isArray(current) ? current : [];
  };

  return (
    <>
      {getNestedFields().map((props: InjectedField) => (
        <FieldRenderer key={props.name} form={form} {...props} />
      ))}
      {getNestedContent().map(({ component }, index) => (
        <ContentRenderer key={index} component={component} />
      ))}
    </>
  );
};

export default CourseBuilderInjectionSlot;
