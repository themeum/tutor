import { quizAutoStartMeta } from './auto-start';
import { quizLayoutMeta } from './layout';
import { questionMatchingMeta } from './questions/matching';
import { questionOrderingMeta } from './questions/ordering';
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
      quizLayoutMeta,
    ],
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
