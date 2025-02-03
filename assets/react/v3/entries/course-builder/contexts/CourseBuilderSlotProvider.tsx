import React, { createContext, ReactNode, useContext, useEffect, useState } from 'react';

export type InjectedField = {
  section: string;
  name: string;
  type: 'text' | 'textarea' | 'select' | 'radio';
  options?: { label: string; value: string }[];
  label?: string;
  placeholder?: string;
  rules?: any;
  priority?: number;
};

type InjectedContent = {
  section: string;
  component: ReactNode;
  priority?: number;
};

const CourseBuilderSlotContext = createContext<{
  fields: Record<string, InjectedField[]>;
  contents: Record<string, InjectedContent[]>;
  registerField: (section: string, fields: InjectedField | InjectedField[]) => void;
  registerContent: (section: string, content: InjectedContent) => void;
}>({
  fields: {},
  contents: {},
  registerField: () => {},
  registerContent: () => {},
});

export const CourseBuilderSlotProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [fields, setFields] = useState<Record<string, InjectedField[]>>({});
  const [contents, setContents] = useState<Record<string, InjectedContent[]>>({});

  const registerField = (section: string, fields: InjectedField | InjectedField[]) => {
    const fieldsArray = Array.isArray(fields) ? fields : [fields];
    setFields((prev) => {
      const updated = [...(prev[section] || []), ...fieldsArray].sort(
        (a, b) => (a.priority ?? 10) - (b.priority ?? 10),
      );
      return { ...prev, [section]: updated };
    });
  };

  const registerContent = (section: string, content: InjectedContent) => {
    setContents((prev) => {
      const updated = [...(prev[section] || []), content].sort((a, b) => (a.priority ?? 10) - (b.priority ?? 10));
      return { ...prev, [section]: updated };
    });
  };

  // Expose API globally for third-party plugins
  useEffect(() => {
    (window as any).TutorLMS = (window as any).TutorLMS || {};
    (window as any).TutorLMS.registerCourseBuilderField = registerField;
    (window as any).TutorLMS.registerCourseBuilderContent = registerContent;
  }, []);

  return (
    <CourseBuilderSlotContext.Provider value={{ fields, contents, registerField, registerContent }}>
      {children}
    </CourseBuilderSlotContext.Provider>
  );
};

export const useCourseBuilderSlot = () => useContext(CourseBuilderSlotContext);
