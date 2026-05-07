// Dashboard Entry Point
// Initializes dashboard functionality based on current page

import { initializeReviews } from '@FrontendComponents/reviews';
import { initializeCommon } from '@FrontendServices/common';
import { initializeHeader } from './header';
import { initializeAnnouncements } from './pages/announcements';
import { initBillingCsvExport } from './pages/billing';
import { initializeDiscussions } from './pages/discussions';
import { initializeHome } from './pages/instructor/home';
import { initializeMyCourses } from './pages/my-courses';
import { initializeQuizAttempts } from './pages/quiz-attempts';
import { initializeSettings } from './pages/settings';
import { initializeWithdrawals } from './pages/withdrawals';

/**
 * Get current dashboard page from URL
 *
 * @since 4.0.0
 */
const getCurrentPage = (): string => {
  const path = window.location.pathname;
  const params = new URLSearchParams(window.location.search);

  // Check for subpage parameter - if not 'dashboard', return early
  const subpage = params.get('page') ? params.get('subpage') : '';
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
  if (path.includes('/announcements')) {
    return 'announcements';
  }
  if (path.includes('/quiz-attempts') || path.includes('/my-quiz-attempts')) {
    return 'quiz-attempts';
  }
  if (path.includes('/discussions')) {
    return 'discussions';
  }
  if (path.includes('/reviews')) {
    return 'reviews';
  }
  if (path.includes('/account/withdrawals')) {
    return 'withdrawals';
  }
  if (path.includes('/account/billing')) {
    return 'billing';
  }
  if (path.includes('/settings')) {
    return 'settings';
  }

  // Default to home when subpage=dashboard
  return 'home';
};

const initializeDashboard = () => {
  initializeHeader();
  initializeCommon();

  const currentPage = getCurrentPage();

  // Initialize page-specific functionality
  switch (currentPage) {
    case 'home':
    case 'dashboard':
      initializeHome();
      break;
    case 'my-courses':
      initializeMyCourses();
      break;
    case 'announcements':
      initializeAnnouncements();
      break;
    case 'quiz-attempts':
      initializeQuizAttempts();
      break;
    case 'discussions':
      initializeDiscussions();
      break;
    case 'reviews':
      initializeReviews();
      break;
    case 'withdrawals':
      initializeWithdrawals();
      break;
    case 'billing':
      initBillingCsvExport();
      break;
    case 'settings':
      initializeSettings();
      break;
    default:
      // eslint-disable-next-line no-console
      console.warn('Unknown dashboard page:', currentPage);
  }
};

// Initialize when Alpine is ready
if (document.readyState === 'loading') {
  document.addEventListener('alpine:init', initializeDashboard);
} else {
  initializeDashboard();
}

export { initializeDashboard };
