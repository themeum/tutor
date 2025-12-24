// Dashboard Entry Point
// Initializes dashboard functionality based on current page

import { initializeAnnouncements } from './pages/announcements';
import { initializeAssignments } from './pages/assignments';
import { initializeCertificates } from './pages/certificates';
import { initializeMyCourses } from './pages/my-courses';
import { initializeOverview } from './pages/overview';
import { initializeQuizAttempts } from './pages/quiz-attempts';
import { initializeSettings } from './pages/settings';

/**
 * Get current dashboard page from URL
 */
const getCurrentPage = (): string => {
  const path = window.location.pathname;
  const params = new URLSearchParams(window.location.search);

  // Check for query parameter (e.g., ?page=my-courses)
  const pageParam = params.get('tutor_dashboard_page') || params.get('page');
  if (pageParam) {
    return pageParam;
  }

  // Check URL path patterns
  if (path.includes('/my-courses') || path.includes('my-courses')) {
    return 'my-courses';
  }
  if (path.includes('/announcements')) {
    return 'dashboard-announcements';
  }
  if (path.includes('/assignments')) {
    return 'dashboard-assignments';
  }
  if (path.includes('/quiz-attempts')) {
    return 'dashboard-quiz-attempts';
  }
  if (path.includes('/settings')) {
    return 'dashboard-settings';
  }
  if (path.includes('/certificates')) {
    return 'dashboard-certificates';
  }

  // Default to overview
  return 'dashboard-overview';
};

const initializeDashboard = () => {
  const currentPage = getCurrentPage();

  // Initialize page-specific functionality
  switch (currentPage) {
    case 'dashboard-overview':
      initializeOverview();
      break;
    case 'my-courses':
      initializeMyCourses();
      break;
    case 'dashboard-announcements':
      initializeAnnouncements();
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

// Initialize when Alpine is ready
if (document.readyState === 'loading') {
  document.addEventListener('alpine:init', initializeDashboard);
} else {
  initializeDashboard();
}

export { initializeDashboard };
