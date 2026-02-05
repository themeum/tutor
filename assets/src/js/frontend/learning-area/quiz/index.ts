import { questionMatchingMeta } from './questions/matching';
import { questionOrderingMeta } from './questions/ordering';
import { quizSubmissionMeta } from './quiz';
import { quizTimerMeta } from './timer';

export const initializeQuizInterface = () => {
  window.TutorComponentRegistry.registerAll({
    components: [quizTimerMeta, questionOrderingMeta, questionMatchingMeta, quizSubmissionMeta],
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
