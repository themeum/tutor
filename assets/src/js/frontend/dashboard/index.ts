// Dashboard Entry Point
// Initializes dashboard functionality based on current page

import { initializeCommon } from '@FrontendServices/common';
import { createRouteConfig, registerRoutePreload, type TutorRouteConfig, withBasePack } from '../route-preload';
import { initializeHeader } from './header';

type DashboardRouteModule = {
  initializeDashboardRoute?: () => void;
};

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

const dashboardRoutes: Record<string, TutorRouteConfig<DashboardRouteModule>> = {
  home: createRouteConfig(withBasePack('core-form-controls'), async () => {
    const { initializeHome } = await import(/* webpackChunkName: "tutor-dashboard-home" */ './pages/instructor/home');
    return {
      initializeDashboardRoute: initializeHome,
    };
  }),
  dashboard: createRouteConfig(withBasePack('core-form-controls'), async () => {
    const { initializeHome } = await import(/* webpackChunkName: "tutor-dashboard-home" */ './pages/instructor/home');
    return {
      initializeDashboardRoute: initializeHome,
    };
  }),
  'my-courses': createRouteConfig(withBasePack(), async () => {
    const { initializeMyCourses } = await import(
      /* webpackChunkName: "tutor-dashboard-my-courses" */ './pages/my-courses'
    );
    return {
      initializeDashboardRoute: initializeMyCourses,
    };
  }),
  announcements: createRouteConfig(withBasePack('core-form-controls'), async () => {
    const { initializeAnnouncements } = await import(
      /* webpackChunkName: "tutor-dashboard-announcements" */ './pages/announcements'
    );
    return {
      initializeDashboardRoute: initializeAnnouncements,
    };
  }),
  'quiz-attempts': createRouteConfig(withBasePack('core-form-controls', 'core-media-editor'), async () => {
    const { initializeQuizAttempts } = await import(
      /* webpackChunkName: "tutor-dashboard-quiz-attempts" */ './pages/quiz-attempts'
    );
    return {
      initializeDashboardRoute: initializeQuizAttempts,
    };
  }),
  discussions: createRouteConfig(withBasePack('core-media-editor'), async () => {
    const { initializeDiscussions } = await import(
      /* webpackChunkName: "tutor-dashboard-discussions" */ './pages/discussions'
    );
    return {
      initializeDashboardRoute: initializeDiscussions,
    };
  }),
  reviews: createRouteConfig(withBasePack(), async () => {
    const { initializeReviews } = await import(
      /* webpackChunkName: "tutor-dashboard-reviews" */ '@FrontendComponents/reviews'
    );
    return {
      initializeDashboardRoute: initializeReviews,
    };
  }),
  withdrawals: createRouteConfig(withBasePack('core-form-controls'), async () => {
    const { initializeWithdrawals } = await import(
      /* webpackChunkName: "tutor-dashboard-withdrawals" */ './pages/withdrawals'
    );
    return {
      initializeDashboardRoute: initializeWithdrawals,
    };
  }),
  billing: createRouteConfig(withBasePack('core-form-controls'), async () => {
    const { initBillingCsvExport } = await import(/* webpackChunkName: "tutor-dashboard-billing" */ './pages/billing');
    return {
      initializeDashboardRoute: initBillingCsvExport,
    };
  }),
  settings: createRouteConfig(withBasePack('core-form-controls', 'core-media-editor'), async () => {
    const { initializeSettings } = await import(/* webpackChunkName: "tutor-dashboard-settings" */ './pages/settings');
    return {
      initializeDashboardRoute: initializeSettings,
    };
  }),
};

const getDashboardRouteConfig = (route: string): TutorRouteConfig<DashboardRouteModule> | undefined => {
  return dashboardRoutes[route];
};

const preloadedDashboardRoute = getCurrentPage();
const preloadedDashboardRouteConfig = getDashboardRouteConfig(preloadedDashboardRoute);
registerRoutePreload({
  routeConfig: preloadedDashboardRouteConfig,
  beforeLoad: initializeHeader,
  initializeRoute: (routeModule) => {
    routeModule.initializeDashboardRoute?.();
  },
});

const initializeDashboard = async () => {
  initializeCommon();

  const currentPage = getCurrentPage();
  const routeConfig = getDashboardRouteConfig(currentPage);
  if (!routeConfig && currentPage) {
    // eslint-disable-next-line no-console
    console.warn('Unknown dashboard page:', currentPage);
  }
};

const bootstrapDashboard = () => {
  void initializeDashboard().catch((error) => {
    // eslint-disable-next-line no-console
    console.error('Failed to initialize dashboard', error);
  });
};

// Initialize when Alpine is ready
if (document.readyState === 'loading') {
  document.addEventListener('alpine:init', bootstrapDashboard);
} else {
  bootstrapDashboard();
}

export { initializeDashboard };
