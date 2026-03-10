import { courseCompletionChart, overviewChart, statCard } from './home-charts';
import { sortSections } from './sort-sections';
// import { sectionCheckbox } from './section-checkbox';

const sortableSectionsMeta = {
  name: 'sortableSections',
  component: sortSections,
};

// const sectionCheckboxMeta = {
//   name: 'sectionCheckbox',
//   component: sectionCheckbox,
// };

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
