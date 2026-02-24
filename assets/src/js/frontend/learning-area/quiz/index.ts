import { quizAutoStartMeta } from './auto-start';
import { quizLayoutMeta } from './layout';
import { questionMatchingMeta } from './questions/matching';
import { questionOrderingMeta } from './questions/ordering';
import { quizSubmissionMeta } from './submission';
import { quizSummarySidebarMeta } from './summary-sidebar';
import { quizTimerMeta } from './timer';

export const initializeQuizInterface = () => {
  window.TutorComponentRegistry.registerAll({
    components: [
      quizTimerMeta,
      questionOrderingMeta,
      questionMatchingMeta,
      quizSubmissionMeta,
      quizAutoStartMeta,
      quizLayoutMeta,
      quizSummarySidebarMeta,
    ],
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
