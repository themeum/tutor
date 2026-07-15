// Learning Area Entry Point
// Initializes learning area functionality based on current page

import { initializeReviews } from '@FrontendComponents/reviews';
import { initializeTour } from '@FrontendComponents/tour';
import { initializeCommon } from '@FrontendServices/common';

import { initializeCommon as initializeLearningAreaCommon } from './common';
import { initializeLesson } from './lesson';
import { initializeCourseCourseInfo } from './pages/course-info';
import { initializeQna } from './pages/qna';
import { initializeQuizInterface } from './quiz';
import { initializeSidebar } from './sidebar';

const decodePathSegment = (segment: string): string => {
  try {
    return decodeURIComponent(segment);
  } catch {
    return segment;
  }
};

const initializeLearningArea = () => {
  initializeLearningAreaCommon();
  initializeCommon();
  initializeSidebar();
  initializeTour();
  initializeReviews();
  const { pathname, search } = window.location;

  // Decode URL-encoded path segments before comparing with configured slugs.
  const pathSegments = pathname.split('/').filter(Boolean).map(decodePathSegment);
  const { tutorConfig } = window.TutorCore.config;
  const { lesson_slug = 'lessons', quiz_slug = 'quizzes' } = tutorConfig || {};

  let currentPage = null;

  if (pathSegments.includes(lesson_slug)) {
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
