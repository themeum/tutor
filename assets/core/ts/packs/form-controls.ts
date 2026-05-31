import { type TutorComponentRegistry } from '@Core/ts/ComponentRegistry';
import { starRatingMeta } from '@Core/ts/components/star-rating';
import { calendarMeta } from '@Core/ts/components/calendar';
import { selectMeta } from '@Core/ts/components/select';
import { selectDropdownMeta } from '@Core/ts/components/select-dropdown';
import { statusSelectMeta } from '@Core/ts/components/status-select';
import { timeInputMeta } from '@Core/ts/components/time-input';
import { locationServiceMeta } from '@Core/ts/services/Location';

export const registerCoreFormControlsPack = (registry: typeof TutorComponentRegistry): void => {
  registry.registerAll({
    components: [starRatingMeta, calendarMeta, selectMeta, selectDropdownMeta, statusSelectMeta, timeInputMeta],
    services: [locationServiceMeta],
  });
};
