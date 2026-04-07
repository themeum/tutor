// Learning Area Entry Point
// Initializes learning area functionality based on current page

import { initializeReviews } from '@FrontendComponents/reviews';
import { initializeCommon } from './common';
import { initializeLesson } from './lesson';
import { initializeAssignmentView } from './pages/assignment-view';
import { initializeCourseCourseInfo } from './pages/course-info';
import { initializeQna } from './pages/qna';
import { initializeQuizInterface } from './quiz';
import { initializeSidebar } from './sidebar';
import { tutorConfig } from '@TutorShared/config/config';

const initializeLearningArea = () => {
  initializeCommon();
  initializeSidebar();
  initializeReviews();
  const { pathname, search } = window.location;

  // Normalize path segments
  const pathSegments = pathname.split('/').filter(Boolean);
  const { lesson_slug = 'lessons', quiz_slug = 'quizzes', assignment_slug = 'assignments' } = tutorConfig || {};

  let currentPage = null;

  if (pathSegments.includes(assignment_slug)) {
    currentPage = 'assignment-view';
  } else if (pathSegments.includes(lesson_slug)) {
    currentPage = 'lesson';
  } else if (pathSegments.includes(quiz_slug)) {
    currentPage = 'quiz';
  } else {
    // fallback to query param (older behavior)
    const params = new URLSearchParams(search);
    currentPage = params.get('subpage');
  }

  switch (currentPage) {
    case 'quiz':
      initializeQuizInterface();
      break;

    case 'assignment-view':
      initializeAssignmentView();
      break;

    case 'lesson':
      initializeLesson();
      break;

    case 'qna':
      initializeQna();
      break;
    case 'course-info':
      initializeCourseCourseInfo();
      break;

    default:
      // eslint-disable-next-line no-console
      console.warn('Unknown learning area page:', currentPage);
  }

  // Ensure all registered components are initialized with Alpine.
  if (window.TutorComponentRegistry) {
    window.TutorComponentRegistry.initWithAlpine(window.Alpine);
  }
};

if (document.readyState === 'loading') {
  document.addEventListener('alpine:init', initializeLearningArea);
} else {
  initializeLearningArea();
}

export { initializeLearningArea };
