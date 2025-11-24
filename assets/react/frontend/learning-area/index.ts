// Learning Area Entry Point
// Initializes learning area functionality based on current page

import Alpine from 'alpinejs';
import { initializeAssignmentView } from './pages/assignment-view';
import { initializeCoursePlayer } from './pages/course-player';
import { initializeLessonContent } from './pages/lesson-content';
import { initializeQuizInterface } from './pages/quiz';

const initializeLearningArea = () => {
  const params = new URLSearchParams(window.location.search);
  const currentPage = params.get('subpage');

  // eslint-disable-next-line no-console
  console.log('Initializing learning area page:', currentPage);

  // Initialize page-specific functionality
  switch (currentPage) {
    case 'course-player':
      initializeCoursePlayer();
      break;
    case 'lesson-content':
      initializeLessonContent();
      break;
    case 'quiz':
      initializeQuizInterface();
      break;
    case 'assignment-view':
      initializeAssignmentView();
      break;
    default:
      // eslint-disable-next-line no-console
      console.warn('Unknown learning area page:', currentPage);
      initializeCoursePlayer(); // Fallback
  }

  window.Alpine = Alpine;
  Alpine.start();

  // TODO: Initialize common learning area features
  // - Course navigation
  // - Progress tracking
  // - Sidebar functionality
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeLearningArea);
} else {
  initializeLearningArea();
}

export { initializeLearningArea };
