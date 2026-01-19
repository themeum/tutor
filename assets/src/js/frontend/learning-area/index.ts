// Learning Area Entry Point
// Initializes learning area functionality based on current page

import { initializeAssignmentView } from './pages/assignment-view';
import { initializeCoursePlayer } from './pages/course-player';
import { initializeLessonContent } from './pages/lesson-content';
import { initializeQnA } from './pages/qna';
import { initializeQuizInterface } from './pages/quiz';

const initializeLearningArea = () => {
  const params = new URLSearchParams(window.location.search);
  const currentPage = params.get('subpage');

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
    case 'qna':
      initializeQnA();
      break;
    default:
      // eslint-disable-next-line no-console
      console.warn('Unknown learning area page:', currentPage);
      initializeCoursePlayer(); // Fallback
  }
};

if (document.readyState === 'loading') {
  document.addEventListener('alpine:init', initializeLearningArea);
} else {
  initializeLearningArea();
}

export { initializeLearningArea };
