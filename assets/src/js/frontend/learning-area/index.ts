// Learning Area Entry Point
// Initializes learning area functionality based on current page

import { type TutorCorePackName } from '@Core/ts/packs/types';
import { initializeCommon } from '@FrontendServices/common';
import { tutorConfig } from '@TutorShared/config/config';
import { chainRoutePreload, requestCorePacks } from '../core-packs';
import { initializeCommon as initializeLearningAreaCommon } from './common';
import { initializeSidebar } from './sidebar';

type LearningAreaRouteModule = {
  initializeLearningAreaRoute?: () => void;
};

type LearningAreaRouteConfig = {
  packs: TutorCorePackName[];
  load: () => Promise<LearningAreaRouteModule>;
};

const learningAreaRoutes: Record<string, LearningAreaRouteConfig> = {
  quiz: {
    packs: ['core-base', 'core-learning'],
    load: async () => {
      const { initializeQuizInterface } = await import(/* webpackChunkName: "tutor-learning-quiz" */ './quiz');
      return {
        initializeLearningAreaRoute: initializeQuizInterface,
      };
    },
  },
  lesson: {
    packs: ['core-base', 'core-learning'],
    load: async () => {
      const { initializeLesson } = await import(/* webpackChunkName: "tutor-learning-lesson" */ './lesson');
      return {
        initializeLearningAreaRoute: initializeLesson,
      };
    },
  },
  qna: {
    packs: ['core-base'],
    load: async () => {
      const { initializeQnA } = await import(/* webpackChunkName: "tutor-learning-qna" */ './pages/qna');
      return {
        initializeLearningAreaRoute: initializeQnA,
      };
    },
  },
  'course-info': {
    packs: ['core-base'],
    load: async () => {
      const [{ initializeCourseCourseInfo }, { initializeReviews }] = await Promise.all([
        import(/* webpackChunkName: "tutor-learning-course-info" */ './pages/course-info'),
        import(/* webpackChunkName: "tutor-learning-reviews" */ '@FrontendComponents/reviews'),
      ]);

      return {
        initializeLearningAreaRoute: () => {
          initializeCourseCourseInfo();
          initializeReviews();
        },
      };
    },
  },
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
const preloadedLearningAreaModule = preloadedLearningAreaRoute ? preloadedLearningAreaRoute.load() : null;
const preloadLearningAreaRoute = async () => {
  initializeLearningAreaCommon();
  initializeSidebar();

  if (!preloadedLearningAreaModule) {
    return;
  }

  const routeModule = await preloadedLearningAreaModule;
  routeModule.initializeLearningAreaRoute?.();
};

const learningAreaCorePackPreload = requestCorePacks(preloadedLearningAreaRoute?.packs || ['core-base']);

chainRoutePreload(learningAreaCorePackPreload, preloadLearningAreaRoute());

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
