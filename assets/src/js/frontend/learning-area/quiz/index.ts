import { questionMatchingMeta } from './questions/matching';
import { questionOrderingMeta } from './questions/ordering';
import { quizAutoStartMeta, quizLayoutMeta, quizSubmissionMeta } from './quiz';
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
