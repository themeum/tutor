import { type TutorComponentRegistry } from '@Core/ts/ComponentRegistry';
import { calendarMeta } from '@Core/ts/components/calendar';
import { selectMeta } from '@Core/ts/components/select';
import { starRatingMeta } from '@Core/ts/components/star-rating';
import { statusSelectMeta } from '@Core/ts/components/status-select';
import { timeInputMeta } from '@Core/ts/components/time-input';

export const registerCoreFormControlsPack = (registry: typeof TutorComponentRegistry): void => {
  registry.registerAll({
    components: [starRatingMeta, calendarMeta, selectMeta, statusSelectMeta, timeInputMeta],
  });
};
