import { useQuery } from '@tanstack/react-query';

export interface Lesson {
  title: string;
}

export interface Quiz {
  title: string;
}

export interface Assignment {
  title: string;
}

export type TopicContent = Lesson | Assignment | Quiz;

export interface CurriculumTopic {
  title: string;
  summary: string;
  lessons: Lesson[];
  quiz: Quiz[];
  assignments: Assignment[];
}

const mockCurriculum: CurriculumTopic[] = [
  {
    title: 'Basic JavaScript',
    summary:
      'The versatility of the tools and its compatibility with other software means that AutoCAD is the most used software in architectural and industrial projects. In this Domestika Basics of 5 courses, learn how to draw any type of project from scratch, alongside Alicia Sanz, model maker and interior designer.',
    lessons: [
      {
        title: 'Basic JavaScript',
      },
    ],
    quiz: [],
    assignments: [],
  },
  {
    title: 'JavaScript variables and functions',
    summary:
      'The versatility of the tools and its compatibility with other software means that AutoCAD is the most used software in architectural and industrial projects. In this Domestika Basics of 5 courses, learn how to draw any type of project from scratch, alongside Alicia Sanz, model maker and interior designer.',
    lessons: [
      {
        title: 'Variable declaration',
      },
      {
        title: 'Variable declaration',
      },
    ],
    quiz: [],
    assignments: [],
  },
  {
    title: 'Asynchronous functions and event loop',
    summary:
      'The versatility of the tools and its compatibility with other software means that AutoCAD is the most used software in architectural and industrial projects. In this Domestika Basics of 5 courses, learn how to draw any type of project from scratch, alongside Alicia Sanz, model maker and interior designer.',
    lessons: [
      {
        title: 'Basic JavaScript',
      },
    ],
    quiz: [],
    assignments: [],
  },
];
const getCourseCurriculum = (courseId: number) => {
  return Promise.resolve({
    data: mockCurriculum,
  });
};

export const useCourseCurriculumQuery = (courseId: number) => {
  return useQuery({
    queryKey: ['CourseCurriculum', courseId],
    queryFn: () => getCourseCurriculum(courseId).then(res => res.data),
    enabled: !!courseId,
  });
};
