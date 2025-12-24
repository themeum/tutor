import { useCourseBuilderSlot } from '@CourseBuilderContexts/CourseBuilderSlotContext';
import { type InjectedContent, type InjectedField, type SectionPath } from '@TutorShared/utils/types';
import { type UseFormReturn } from 'react-hook-form';
import ContentRenderer from './ContentRenderer';
import FieldRenderer from './FieldRenderer';

interface CourseBuilderInjectionSlotProps {
  section: SectionPath;
  namePrefix?: string;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<any>;
}

const CourseBuilderInjectionSlot = ({ section, namePrefix, form }: CourseBuilderInjectionSlotProps) => {
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
        <FieldRenderer
          key={props.name}
          form={form}
          {...props}
          name={namePrefix ? `${namePrefix}${props.name}` : props.name}
        />
      ))}
      {getNestedContent().map(({ component }, index) => (
        <ContentRenderer key={index} component={component} />
      ))}
    </>
  );
};

export default CourseBuilderInjectionSlot;
