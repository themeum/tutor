// Learning Area Entry Point
// Initializes learning area functionality based on current page

import { initializeLesson } from './lesson';
import { initializeAssignmentView } from './pages/assignment-view';
import { initializeQuizInterface } from './pages/quiz';

const initializeLearningArea = () => {
  const params = new URLSearchParams(window.location.search);
  const currentPage = params.get('subpage');

  switch (currentPage) {
    case 'quiz':
      initializeQuizInterface();
      break;
    case 'assignment-view':
      initializeAssignmentView();
      break;
    default:
      // eslint-disable-next-line no-console
      console.warn('Unknown learning area page:', currentPage);
  }

  // Initialized lesson contents
  const lessonContentWrapper = document.querySelector('.tutor-lesson-content');
  if (lessonContentWrapper) {
    initializeLesson();
  }
};

if (document.readyState === 'loading') {
  document.addEventListener('alpine:init', initializeLearningArea);
} else {
  initializeLearningArea();
}

export { initializeLearningArea };
