import { courseCompletionChart, overviewChart, statCard } from './home-charts';
import { sortSections } from './sort-sections';

const sortableSectionsMeta = {
  name: 'sortableSections',
  component: sortSections,
};

const statCardMeta = {
  name: 'statCard',
  component: statCard,
};

const overviewChartMeta = {
  name: 'overviewChart',
  component: overviewChart,
};

const courseCompletionChartMeta = {
  name: 'courseCompletionChart',
  component: courseCompletionChart,
};

export const initializeHome = () => {
  window.TutorComponentRegistry.registerAll({
    components: [courseCompletionChartMeta, overviewChartMeta, sortableSectionsMeta, statCardMeta],
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
