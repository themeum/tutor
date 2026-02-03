import { questionMatchingMeta } from './questions/matching';
import { questionOrderingMeta } from './questions/ordering';
import { quizTimerMeta } from './timer';

export const initializeQuizInterface = () => {
  window.TutorComponentRegistry.registerAll({
    components: [quizTimerMeta, questionOrderingMeta, questionMatchingMeta],
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
