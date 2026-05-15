// Dashboard Entry Point
// Initializes dashboard functionality based on current page

import { type TutorCorePackName } from '@Core/ts/packs/types';
import { initializeCommon } from '@FrontendServices/common';
import { chainRoutePreload, requestCorePacks } from '../core-packs';
import { initializeHeader } from './header';

type DashboardRouteModule = {
  initializeDashboardRoute?: () => void;
};

type DashboardRouteConfig = {
  packs: TutorCorePackName[];
  load: () => Promise<DashboardRouteModule>;
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

const dashboardRoutes: Record<string, DashboardRouteConfig> = {
  home: {
    packs: ['core-base', 'core-form-controls'],
    load: async () => {
      const { initializeHome } = await import(/* webpackChunkName: "tutor-dashboard-home" */ './pages/instructor/home');
      return {
        initializeDashboardRoute: initializeHome,
      };
    },
  },
  dashboard: {
    packs: ['core-base', 'core-form-controls'],
    load: async () => {
      const { initializeHome } = await import(/* webpackChunkName: "tutor-dashboard-home" */ './pages/instructor/home');
      return {
        initializeDashboardRoute: initializeHome,
      };
    },
  },
  'my-courses': {
    packs: ['core-base'],
    load: async () => {
      const { initializeMyCourses } = await import(
        /* webpackChunkName: "tutor-dashboard-my-courses" */ './pages/my-courses'
      );
      return {
        initializeDashboardRoute: initializeMyCourses,
      };
    },
  },
  announcements: {
    packs: ['core-base', 'core-form-controls'],
    load: async () => {
      const { initializeAnnouncements } = await import(
        /* webpackChunkName: "tutor-dashboard-announcements" */ './pages/announcements'
      );
      return {
        initializeDashboardRoute: initializeAnnouncements,
      };
    },
  },
  'quiz-attempts': {
    packs: ['core-base', 'core-form-controls'],
    load: async () => {
      const { initializeQuizAttempts } = await import(
        /* webpackChunkName: "tutor-dashboard-quiz-attempts" */ './pages/quiz-attempts'
      );
      return {
        initializeDashboardRoute: initializeQuizAttempts,
      };
    },
  },
  discussions: {
    packs: ['core-base'],
    load: async () => {
      const { initializeDiscussions } = await import(
        /* webpackChunkName: "tutor-dashboard-discussions" */ './pages/discussions'
      );
      return {
        initializeDashboardRoute: initializeDiscussions,
      };
    },
  },
  reviews: {
    packs: ['core-base'],
    load: async () => {
      const { initializeReviews } = await import(
        /* webpackChunkName: "tutor-dashboard-reviews" */ '@FrontendComponents/reviews'
      );
      return {
        initializeDashboardRoute: initializeReviews,
      };
    },
  },
  withdrawals: {
    packs: ['core-base', 'core-form-controls'],
    load: async () => {
      const { initializeWithdrawals } = await import(
        /* webpackChunkName: "tutor-dashboard-withdrawals" */ './pages/withdrawals'
      );
      return {
        initializeDashboardRoute: initializeWithdrawals,
      };
    },
  },
  billing: {
    packs: ['core-base', 'core-form-controls'],
    load: async () => {
      const { initBillingCsvExport } = await import(
        /* webpackChunkName: "tutor-dashboard-billing" */ './pages/billing'
      );
      return {
        initializeDashboardRoute: initBillingCsvExport,
      };
    },
  },
  settings: {
    packs: ['core-base', 'core-form-controls', 'core-media-editor'],
    load: async () => {
      const { initializeSettings } = await import(
        /* webpackChunkName: "tutor-dashboard-settings" */ './pages/settings'
      );
      return {
        initializeDashboardRoute: initializeSettings,
      };
    },
  },
};

const getDashboardRouteConfig = (route: string): DashboardRouteConfig | undefined => {
  return dashboardRoutes[route];
};

const preloadedDashboardRoute = getCurrentPage();
const preloadedDashboardRouteConfig = getDashboardRouteConfig(preloadedDashboardRoute);
const preloadedDashboardRouteModule = preloadedDashboardRouteConfig ? preloadedDashboardRouteConfig.load() : null;
const preloadDashboardRoute = async () => {
  initializeHeader();

  if (!preloadedDashboardRouteModule) {
    return;
  }

  const routeModule = await preloadedDashboardRouteModule;
  routeModule.initializeDashboardRoute?.();
};

const dashboardCorePackPreload = requestCorePacks(preloadedDashboardRouteConfig?.packs || ['core-base']);

chainRoutePreload(dashboardCorePackPreload, preloadDashboardRoute());

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
