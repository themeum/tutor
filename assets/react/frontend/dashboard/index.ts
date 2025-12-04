// Dashboard Entry Point
// Initializes dashboard functionality based on current page

import { initializeAssignments } from './pages/assignments';
import { initializeCertificates } from './pages/certificates';
import { initializeHome } from './pages/instructor/home';
import { initializeMyCourses } from './pages/my-courses';
import { initializeOverview } from './pages/overview';
import { initializeQuizAttempts } from './pages/quiz-attempts';
import { initializeSettings } from './pages/settings';

const initializeDashboard = () => {
  const params = new URLSearchParams(window.location.search);
  const currentPage = params.get('subpage');
  const currentDashboardPage = params.get('dashboard-page') || 'home';

  if (currentPage !== 'dashboard') {
    return;
  }

  // Initialize page-specific functionality
  switch (currentDashboardPage) {
    case 'home':
      initializeOverview();
      initializeHome();
      break;
    case 'courses':
      initializeMyCourses();
      break;
    case 'assignments':
      initializeAssignments();
      break;
    case 'quiz-attempts':
      initializeQuizAttempts();
      break;
    case 'settings':
      initializeSettings();
      break;
    case 'certificates':
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
  document.addEventListener('alpine:init', initializeDashboard);
} else {
  initializeDashboard();
}

export { initializeDashboard };
