import React, { Suspense } from 'react';
import { setLocaleData } from '@wordpress/i18n';

import { LoadingOverlay } from '@TutorShared/atoms/LoadingSpinner';

import { tutorConfig } from '@TutorShared/config/config';

import type QuizModalContentOriginal from '@CourseBuilderComponents/modals/QuizModalContent';

type QuizModalContentProps = React.ComponentProps<typeof QuizModalContentOriginal>;

let QuizModalComponent: React.ComponentType<QuizModalContentProps>;

if (process.env.MAKE_POT) {
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  QuizModalComponent = require('@CourseBuilderComponents/modals/QuizModalContent').default;
} else {
  QuizModalComponent = React.lazy(() => {
    if (tutorConfig.course_builder_quiz_modal_locales) {
      setLocaleData(tutorConfig.course_builder_quiz_modal_locales, 'tutor');
    }
    return import(
      /* webpackChunkName: "tutor-course-builder-quiz-modal" */ '@CourseBuilderComponents/modals/QuizModalContent'
    );
  });
}

export const prefetchQuizModal = () => {
  if (!process.env.MAKE_POT) {
    import(
      /* webpackChunkName: "tutor-course-builder-quiz-modal" */ '@CourseBuilderComponents/modals/QuizModalContent'
    );
  }
};

const QuizModalWrapper: React.FC<QuizModalContentProps> = (props) => (
  <Suspense fallback={<LoadingOverlay />}>
    <QuizModalComponent {...props} />
  </Suspense>
);

export default QuizModalWrapper;
