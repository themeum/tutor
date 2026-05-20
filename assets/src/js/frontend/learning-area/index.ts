// Learning Area Entry Point
// Initializes learning area functionality based on current page

import { initializeCommon } from '@FrontendServices/common';
import { tutorConfig } from '@TutorShared/config/config';
import { createRouteConfig, registerRoutePreload, type TutorRouteConfig, withBasePack } from '../route-preload';
import { initializeCommon as initializeLearningAreaCommon } from './common';
import { initializeSidebar } from './sidebar';

type LearningAreaRouteModule = {
  initializeLearningAreaRoute?: () => void;
};

const learningAreaRoutes: Record<string, TutorRouteConfig<LearningAreaRouteModule>> = {
  quiz: createRouteConfig(withBasePack('core-learning'), async () => {
    const { initializeQuizInterface } = await import(/* webpackChunkName: "tutor-learning-quiz" */ './quiz');
    return {
      initializeLearningAreaRoute: initializeQuizInterface,
    };
  }),
  lesson: createRouteConfig(withBasePack('core-learning'), async () => {
    const { initializeLesson } = await import(/* webpackChunkName: "tutor-learning-lesson" */ './lesson');
    return {
      initializeLearningAreaRoute: initializeLesson,
    };
  }),
  qna: createRouteConfig(withBasePack(), async () => {
    const { initializeQna } = await import(/* webpackChunkName: "tutor-learning-qna" */ './pages/qna');
    return {
      initializeLearningAreaRoute: initializeQna,
    };
  }),
  'course-info': createRouteConfig(withBasePack(), async () => {
    const { initializeCourseCourseInfo } = await import(
      /* webpackChunkName: "tutor-learning-course-info" */ './pages/course-info'
    );

    return {
      initializeLearningAreaRoute: () => {
        initializeCourseCourseInfo();
      },
    };
  }),
  reviews: createRouteConfig(withBasePack('core-form-controls'), async () => {
    const { initializeReviews } = await import(
      /* webpackChunkName: "tutor-learning-reviews" */ '@FrontendComponents/reviews'
    );

    return {
      initializeLearningAreaRoute: () => {
        initializeReviews();
      },
    };
  }),
};

const getCurrentLearningAreaPage = (): string | null => {
  const { pathname, search } = window.location;

  const pathSegments = pathname.split('/').filter(Boolean);
  const { lesson_slug = 'lessons', quiz_slug = 'quizzes' } = tutorConfig || {};

  if (pathSegments.includes(lesson_slug)) {
    return 'lesson';
  }

  if (pathSegments.includes(quiz_slug)) {
    return 'quiz';
  }

  const params = new URLSearchParams(search);
  return params.get('subpage');
};

const preloadedLearningAreaPage = getCurrentLearningAreaPage();
const preloadedLearningAreaRoute = preloadedLearningAreaPage
  ? learningAreaRoutes[preloadedLearningAreaPage]
  : undefined;
registerRoutePreload({
  routeConfig: preloadedLearningAreaRoute,
  beforeLoad: () => {
    initializeLearningAreaCommon();
    initializeSidebar();
  },
  initializeRoute: (routeModule) => {
    routeModule.initializeLearningAreaRoute?.();
  },
});

const initializeLearningArea = async () => {
  initializeCommon();
  const currentPage = getCurrentLearningAreaPage();

  const routeConfig = currentPage ? learningAreaRoutes[currentPage] : undefined;
  if (!routeConfig) {
    if (currentPage) {
      // eslint-disable-next-line no-console
      console.warn('Unknown learning area page:', currentPage);
    }
  }
};

const bootstrapLearningArea = () => {
  void initializeLearningArea().catch((error) => {
    // eslint-disable-next-line no-console
    console.error('Failed to initialize learning area', error);
  });
};

if (document.readyState === 'loading') {
  document.addEventListener('alpine:init', bootstrapLearningArea);
} else {
  bootstrapLearningArea();
}

export { initializeLearningArea };
