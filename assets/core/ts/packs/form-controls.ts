import { type TutorComponentRegistry } from '@Core/ts/ComponentRegistry';
import { calendarMeta } from '@Core/ts/components/calendar';
import { selectMeta } from '@Core/ts/components/select';
import { selectDropdownMeta } from '@Core/ts/components/select-dropdown';
import { statusSelectMeta } from '@Core/ts/components/status-select';
import { stepperDropdownMeta } from '@Core/ts/components/stepper-dropdown';
import { timeInputMeta } from '@Core/ts/components/time-input';
import { locationServiceMeta } from '@Core/ts/services/Location';

export const registerCoreFormControlsPack = (registry: typeof TutorComponentRegistry): void => {
  registry.registerAll({
    components: [calendarMeta, selectMeta, selectDropdownMeta, statusSelectMeta, stepperDropdownMeta, timeInputMeta],
    services: [locationServiceMeta],
  });
};
