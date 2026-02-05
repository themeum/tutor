import { questionMatchingMeta } from './questions/matching';
import { questionOrderingMeta } from './questions/ordering';
import { quizAutoStartMeta, quizSubmissionMeta } from './quiz';
import { quizTimerMeta } from './timer';

export const initializeQuizInterface = () => {
  window.TutorComponentRegistry.registerAll({
    components: [quizTimerMeta, questionOrderingMeta, questionMatchingMeta, quizSubmissionMeta, quizAutoStartMeta],
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
