// Dashboard Entry Point
// Initializes dashboard functionality based on current page

import { initializeAssignments } from './pages/assignments';
import { initializeCertificates } from './pages/certificates';
import { initializeMyCourses } from './pages/my-courses';
import { initializeOverview } from './pages/overview';
import { initializeQuizAttempts } from './pages/quiz-attempts';
import { initializeSettings } from './pages/settings';

const initializeDashboard = () => {
  const currentPage = document.body.dataset.page;

  // eslint-disable-next-line no-console
  console.log('Initializing dashboard page:', currentPage);

  // Initialize page-specific functionality
  switch (currentPage) {
    case 'dashboard-overview':
      initializeOverview();
      break;
    case 'dashboard-courses':
      initializeMyCourses();
      break;
    case 'dashboard-assignments':
      initializeAssignments();
      break;
    case 'dashboard-quiz-attempts':
      initializeQuizAttempts();
      break;
    case 'dashboard-settings':
      initializeSettings();
      break;
    case 'dashboard-certificates':
      initializeCertificates();
      break;
    default:
      // eslint-disable-next-line no-console
      console.warn('Unknown dashboard page:', currentPage);
      initializeOverview(); // Fallback
  }

  // TODO: Initialize common dashboard features
  // - Sidebar navigation
  // - Global search
  // - Notifications
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeDashboard);
} else {
  initializeDashboard();
}

export { initializeDashboard };
