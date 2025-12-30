// Dashboard Entry Point
// Initializes dashboard functionality based on current page

import { initializeAssignments } from './pages/assignments';
import { initializeCertificates } from './pages/certificates';
import { initializeHome } from './pages/instructor/home';
import { initializeMyCourses } from './pages/my-courses';
import { initializeOverview } from './pages/overview';
import { initializeQuizAttempts } from './pages/quiz-attempts';
import { initializeReviews } from './pages/reviews';
import { initializeSettings } from './pages/settings';

/**
 * Get current dashboard page from URL
 *
 * Currently, we are supporting both the playground and the dashboard
 *
 * @TODO: Remove playground support
 */
const getCurrentPage = (): string => {
  const path = window.location.pathname;
  const params = new URLSearchParams(window.location.search);

  // Check for subpage parameter - if not 'dashboard', return early
  const subpage = params.get('subpage');
  if (subpage && subpage !== 'dashboard') {
    return ''; // Not on dashboard, will be handled by early return in initializeDashboard
  }

  // Check for dashboard-page parameter (highest priority when subpage=dashboard)
  const dashboardPage = params.get('dashboard-page');
  if (dashboardPage) {
    return dashboardPage;
  }

  // Check for legacy query parameters
  const pageParam = params.get('subpage');
  if (pageParam) {
    return pageParam;
  }

  // Check URL path patterns
  if (path.includes('/my-courses') || path.includes('my-courses')) {
    return 'my-courses';
  }
  if (path.includes('/assignments')) {
    return 'assignments';
  }
  if (path.includes('/quiz-attempts')) {
    return 'quiz-attempts';
  }
  if (path.includes('/settings')) {
    return 'settings';
  }
  if (path.includes('/certificates')) {
    return 'certificates';
  }
  if (path.includes('/reviews')) {
    return 'reviews';
  }

  // Default to home when subpage=dashboard
  return 'home';
};

const initializeDashboard = () => {
  const currentPage = getCurrentPage();

  // Initialize page-specific functionality
  switch (currentPage) {
    case 'home':
    case 'dashboard':
      initializeOverview();
      initializeHome();
      break;
    case 'courses':
    case 'my-courses':
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
    case 'reviews':
      initializeReviews();
      break;
    default:
      // eslint-disable-next-line no-console
      console.warn('Unknown dashboard page:', currentPage);
      initializeOverview(); // Fallback
  }
};

// Initialize when Alpine is ready
if (document.readyState === 'loading') {
  document.addEventListener('alpine:init', initializeDashboard);
} else {
  initializeDashboard();
}

export { initializeDashboard };
