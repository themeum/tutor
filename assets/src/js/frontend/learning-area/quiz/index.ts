import { quizRetryAttemptMeta } from '@FrontendComponents/quiz/retry-attempt';
import { quizSummarySidebarMeta } from '@FrontendComponents/quiz/summary-sidebar';

import { quizAutoStartMeta } from './auto-start';
import { quizLayoutMeta } from './layout';
import { questionMatchingMeta } from './questions/matching';
import { questionOrderingMeta } from './questions/ordering';
import { quizRadarMeta } from './radar';
import { quizSubmissionMeta } from './submission';
import { quizTimerMeta } from './timer';

export const initializeQuizInterface = () => {
  window.TutorComponentRegistry.registerAll({
    components: [
      quizTimerMeta,
      questionOrderingMeta,
      questionMatchingMeta,
      quizSubmissionMeta,
      quizAutoStartMeta,
      quizRadarMeta,
      quizLayoutMeta,
      quizRetryAttemptMeta,
      quizSummarySidebarMeta,
    ],
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
