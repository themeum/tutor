import {
  type InjectedContent,
  type InjectedField,
  type InjectionSlots,
  type SectionPath,
} from '@TutorShared/utils/types';
import { produce } from 'immer';
import React, { createContext, type ReactNode, useCallback, useContext, useEffect, useMemo, useState } from 'react';

type CurriculumType = 'Lesson' | 'Quiz' | 'Assignment';

type SectionData<T> = Record<string, T[]>;
interface CurriculumData<T> {
  Lesson: SectionData<T>;
  Quiz: SectionData<T>;
  Assignment: SectionData<T>;
}

export interface CourseBuilderData<T> {
  Basic: SectionData<T>;
  Curriculum: CurriculumData<T>;
  Additional: SectionData<T>;
}

const defaultCourseBuilderState = {
  fields: {
    Basic: {
      after_description: [],
      after_settings: [],
    },
    Curriculum: {
      Lesson: {
        after_description: [],
        bottom_of_sidebar: [],
      },
      Quiz: {
        after_question_description: [],
        bottom_of_question_sidebar: [],
        bottom_of_settings: [],
      },
      Assignment: {
        after_description: [],
        bottom_of_sidebar: [],
      },
    },
    Additional: {
      after_certificates: [],
      bottom_of_sidebar: [],
    },
  },
  contents: {
    Basic: {
      after_description: [],
      after_settings: [],
    },
    Curriculum: {
      Lesson: {
        after_description: [],
        bottom_of_sidebar: [],
      },
      Quiz: {
        after_question_description: [],
        bottom_of_question_sidebar: [],
        bottom_of_settings: [],
      },
      Assignment: {
        after_description: [],
        bottom_of_sidebar: [],
      },
    },
    Additional: {
      after_certificates: [],
      bottom_of_sidebar: [],
    },
  },
};

type CourseBuilderContextType = {
  fields: CourseBuilderData<InjectedField>;
  contents: CourseBuilderData<InjectedContent>;
  registerField: (section: SectionPath, fields: InjectedField | InjectedField[]) => void;
  registerContent: (section: SectionPath, content: InjectedContent) => void;
};

const updateSection = <T extends { priority?: number }>(
  currentState: CourseBuilderData<T>,
  section: SectionPath,
  items: T[],
): CourseBuilderData<T> => {
  return produce(currentState, (draft) => {
    const sectionPath = section.split('.') as [keyof InjectionSlots, CurriculumType | undefined, string];
    const [root, sub, slot] =
      sectionPath.length > 2 ? sectionPath : [sectionPath[0], undefined, sectionPath[sectionPath.length - 1]];

    const target = sub ? draft[root][sub] : draft[root];

    // @ts-ignore
    if (slot && target[slot]) {
      // @ts-ignore
      target[slot] = [...target[slot], ...items].sort((a, b) => (a.priority ?? 10) - (b.priority ?? 10));
    }
  });
};

const registerField = (
  previousFields: CourseBuilderData<InjectedField>,
  section: SectionPath,
  fields: InjectedField | InjectedField[],
): CourseBuilderData<InjectedField> => {
  const items = Array.isArray(fields) ? fields : [fields];
  return updateSection(previousFields, section, items);
};

const registerContent = (
  previousContents: CourseBuilderData<InjectedContent>,
  section: SectionPath,
  content: InjectedContent,
): CourseBuilderData<InjectedContent> => {
  return updateSection(previousContents, section, [content]);
};

const CourseBuilderSlotContext = createContext<CourseBuilderContextType>({
  fields: defaultCourseBuilderState.fields,
  contents: defaultCourseBuilderState.contents,
  registerField: () => {},
  registerContent: () => {},
});

export const CourseBuilderSlotProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [fields, setFields] = useState<CourseBuilderData<InjectedField>>(defaultCourseBuilderState.fields);
  const [contents, setContents] = useState<CourseBuilderData<InjectedContent>>(defaultCourseBuilderState.contents);

  const handleRegisterField = useCallback((section: SectionPath, fields: InjectedField | InjectedField[]) => {
    setFields((prev) => registerField(prev, section, fields));
  }, []);

  const handleRegisterContent = useCallback((section: SectionPath, content: InjectedContent) => {
    setContents((prev) => registerContent(prev, section, content));
  }, []);

  useEffect(() => {
    const createCurriculumAPI = (type: CurriculumType) => ({
      registerField: (slot: InjectionSlots['Curriculum'][typeof type], fields: InjectedField | InjectedField[]) =>
        handleRegisterField(`Curriculum.${type}.${slot}` as 'Curriculum.Lesson.after_description', fields),
      registerContent: (slot: InjectionSlots['Curriculum'][typeof type], content: InjectedContent) =>
        handleRegisterContent(`Curriculum.${type}.${slot}` as 'Curriculum.Lesson.after_description', content),
    });

    window.Tutor = {
      CourseBuilder: {
        Basic: {
          registerField: (slot: InjectionSlots['Basic'], fields) => handleRegisterField(`Basic.${slot}`, fields),
          registerContent: (slot: InjectionSlots['Basic'], contents) =>
            handleRegisterContent(`Basic.${slot}`, contents),
        },
        Curriculum: {
          Lesson: createCurriculumAPI('Lesson'),
          Quiz: createCurriculumAPI('Quiz'),
          Assignment: createCurriculumAPI('Assignment'),
        },
        Additional: {
          registerField: (slot: InjectionSlots['Additional'], fields) =>
            handleRegisterField(`Additional.${slot}`, fields),
          registerContent: (slot: InjectionSlots['Additional'], contents) =>
            handleRegisterContent(`Additional.${slot}`, contents),
        },
      },
    };
  }, [handleRegisterField, handleRegisterContent]);

  const contextValue = useMemo(
    () => ({
      fields,
      contents,
      registerField: handleRegisterField,
      registerContent: handleRegisterContent,
    }),
    [fields, contents, handleRegisterField, handleRegisterContent],
  );

  return <CourseBuilderSlotContext.Provider value={contextValue}>{children}</CourseBuilderSlotContext.Provider>;
};
export const useCourseBuilderSlot = () => {
  const context = useContext(CourseBuilderSlotContext);

  if (!context) {
    throw new Error('useCourseBuilderSlot must be used within CourseBuilderSlotProvider');
  }

  return context;
};
