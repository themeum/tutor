import { initializeLessonComments } from './comments';
import { initializeLessonPlayer } from './player';

/**
 * Initialize all lesson related logic
 */
export const initializeLesson = () => {
  initializeLessonPlayer();
  initializeLessonComments();
};
