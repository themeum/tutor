import { type AlpineComponentMeta } from '@Core/ts/types';
import { tutorConfig } from '@TutorShared/config/config';
import { DateFormats } from '@TutorShared/config/constants';
import { __ } from '@wordpress/i18n';
import {
  endOfMonth,
  endOfYear,
  format,
  startOfMonth,
  startOfToday,
  startOfYear,
  subDays,
  subMonths,
  subYears,
} from 'date-fns';
import { type Calendar, Calendar as VanillaCalendar } from 'vanilla-calendar-pro';
import type OptionsCalendar from 'vanilla-calendar-pro/options';
// @ts-ignore
import 'vanilla-calendar-pro/styles/index.css';

const PRESETS = {
  ALL_TIME: 'all-time',
  YESTERDAY: 'yesterday',
  LAST_7: 'last-7',
  LAST_14: 'last-14',
  LAST_30: 'last-30',
  THIS_MONTH: 'this-month',
  LAST_MONTH: 'last-month',
  LAST_YEAR: 'last-year',
} as const;

type Preset = (typeof PRESETS)[keyof typeof PRESETS];

const PRESET_LABELS: Record<Preset, string> = {
  [PRESETS.ALL_TIME]: __('All Time', 'tutor'),
  [PRESETS.YESTERDAY]: __('Yesterday', 'tutor'),
  [PRESETS.LAST_7]: __('Last 7 days', 'tutor'),
  [PRESETS.LAST_14]: __('Last 14 days', 'tutor'),
  [PRESETS.LAST_30]: __('Last 30 days', 'tutor'),
  [PRESETS.THIS_MONTH]: __('This month', 'tutor'),
  [PRESETS.LAST_MONTH]: __('Last month', 'tutor'),
  [PRESETS.LAST_YEAR]: __('Last year', 'tutor'),
};

export function calendar({ options, hidePopover }: { options: OptionsCalendar; hidePopover?: () => void }) {
  return {
    $el: undefined as HTMLElement | HTMLInputElement | undefined,
    $nextTick: null as unknown as (callback: () => void) => void,
    calendar: null as Calendar | null,

    init(): void {
      if (!this.$el) return;

      this.$nextTick(() => {
        const el = this.$el!;
        const url = new URL(window.location.href);

        const selectedDates: string[] = [];
        let selectedTime = '';

        // For input mode
        if (options.inputMode && (el as HTMLInputElement).value) {
          const parts = (el as HTMLInputElement).value.split(' ');
          selectedDates.push(parts[0]);
          if (parts.length > 1) {
            selectedTime = parts.slice(1).join(' ');
          }
        } else {
          // For filter mode
          const startDate = url.searchParams.get('start_date');
          const endDate = url.searchParams.get('end_date');
          const singleDate = url.searchParams.get('date');

          if (startDate && endDate) {
            selectedDates.push(startDate, endDate);
          } else if (singleDate) {
            selectedDates.push(singleDate);
          }
        }

        this.calendar = new VanillaCalendar(el, {
          ...options,
          themeAttrDetect: 'html[data-theme]',
          selectedTheme: 'light',
          locale: tutorConfig.local?.replace('_', '-') ?? 'en-US',
          enableJumpToSelectedDate: true,
          selectedWeekends: [],
          displayDatesOutside: false,
          ...(selectedDates.length ? { selectedDates } : {}),
          ...(selectedTime ? { selectedTime } : {}),
          onClickDate: (self) => this.handleDateClick(self),
          styles: {
            calendar: 'vc tutor-vc-calendar',
          },
          layouts: {
            multiple: `
              <div class="vc-layout">
                <aside class="vc-presets">
                  ${(Object.entries(PRESET_LABELS) as [Preset, string][])
                    .map(([key, label]) => `<button type="button" data-preset="${key}">${label}</button>`)
                    .join('')}
                </aside>

                <div class="vc-main">
                  <div class="vc-controls" data-vc="controls" role="toolbar" aria-label="Calendar Navigation">
                    <#ArrowPrev />
                    <#ArrowNext />
                  </div>

                  <div class="vc-grid" data-vc="grid">
                    <div class="vc-column" data-vc="column" role="region">
                      <div class="vc-header" data-vc="header" role="toolbar">
                        <div class="vc-header__content" data-vc-header="content">
                          <#Month /> <#Year />
                        </div>
                      </div>
                      <div class="vc-wrapper" data-vc="wrapper">
                        <#WeekNumbers />
                        <div class="vc-content" data-vc="content">
                          <#Week />
                          <#Dates />
                          <#DateRangeTooltip />
                        </div>
                      </div>
                    </div>

                    <div class="vc-column" data-vc="column" role="region">
                      <div class="vc-header" data-vc="header" role="toolbar">
                        <div class="vc-header__content" data-vc-header="content">
                          <#Month /> <#Year />
                        </div>
                      </div>
                      <div class="vc-wrapper" data-vc="wrapper">
                        <#WeekNumbers />
                        <div class="vc-content" data-vc="content">
                          <#Week />
                          <#Dates />
                          <#DateRangeTooltip />
                        </div>
                      </div>
                    </div>
                  </div>

                  <#ControlTime />

                  <div class="vc-footer tutor-flex tutor-justify-end tutor-gap-5 tutor-mt-6">
                    <button type="button" data-calendar-action="clear" class="tutor-btn tutor-btn-secondary tutor-btn-small">
                    ${__('Clear Selection', 'tutor')}
                    </button>
                    <button type="button" data-calendar-action="apply" class="tutor-btn tutor-btn-primary tutor-btn-small">
                    ${__('Apply', 'tutor')}
                    </button>
                  </div>
                </div>
              </div>
            `,
          },
        });

        this.calendar.init();
        this.updateActivePreset();

        el.addEventListener('click', (e) => this.handlePresetClick(e));
        el.addEventListener('click', (e) => this.handleActionClick(e));
        window.addEventListener('popstate', this.updateActivePreset);
        window.addEventListener('tutor-calendar:clear', () => this.clear());
      });
    },

    destroy() {
      this.calendar?.destroy();
      this.calendar = null;
      window.removeEventListener('popstate', this.updateActivePreset);
      window.removeEventListener('tutor-calendar:clear', () => this.clear());
    },

    handleActionClick(e: Event) {
      const target = (e.target as HTMLElement).closest('[data-calendar-action]');
      if (!target) return;

      const action = target.getAttribute('data-calendar-action');
      if (action === 'apply') {
        this.applyRange();
      } else if (action === 'clear') {
        this.clear();
      }
    },

    handlePresetClick(e: Event) {
      const target = (e.target as HTMLElement).closest('[data-preset]');
      if (!target) return;

      const preset = target.getAttribute('data-preset') as Preset | null;
      if (preset) {
        this.applyPreset(preset);
      }
    },

    handleDateClick(self: Calendar) {
      if (self.context.inputElement) {
        this.handleInputSelection(self);
      } else if (self.selectionDatesMode === 'single') {
        this.handleSingleDateSelection(self);
      }
    },

    handleInputSelection(self: Calendar) {
      let inputValue = '';

      if (self.context.selectedDates[0]) {
        inputValue = `${self.context.selectedDates[0]} ${self.context.selectedTime ?? ''}`;
      }

      if (self.context.inputElement) {
        self.context.inputElement.value = inputValue;
        self.context.inputElement.dispatchEvent(new Event('change', { bubbles: true }));
        self.hide();
      }
    },

    applyRange() {
      if (!this.calendar || this.calendar.context.selectedDates.length !== 2) return;

      hidePopover?.();
      this.navigateWithParams({
        start_date: this.calendar.context.selectedDates[0],
        end_date: this.calendar.context.selectedDates[1],
      });
    },

    handleSingleDateSelection(self: Calendar) {
      hidePopover?.();
      this.navigateWithParams({
        date: self.context.selectedDates[0],
      });
    },

    navigateWithParams(params: Record<string, string | null>) {
      const url = new URL(window.location.href);

      Object.entries(params).forEach(([key, value]) => {
        if (value === null) {
          url.searchParams.delete(key);
        } else {
          url.searchParams.set(key, value);
        }
      });

      window.location.href = url.toString();
    },

    clear() {
      this.navigateWithParams({
        start_date: null,
        end_date: null,
        date: null,
      });
    },

    getPresetDates(preset: Preset): string[] {
      const today = startOfToday();

      switch (preset) {
        case PRESETS.ALL_TIME:
          return [];
        case PRESETS.YESTERDAY:
          return [
            format(subDays(today, 1), DateFormats.yearMonthDay),
            format(subDays(today, 1), DateFormats.yearMonthDay),
          ];
        case PRESETS.LAST_7:
          return [format(subDays(today, 6), DateFormats.yearMonthDay), format(today, DateFormats.yearMonthDay)];
        case PRESETS.LAST_14:
          return [format(subDays(today, 13), DateFormats.yearMonthDay), format(today, DateFormats.yearMonthDay)];
        case PRESETS.LAST_30:
          return [format(subDays(today, 29), DateFormats.yearMonthDay), format(today, DateFormats.yearMonthDay)];
        case PRESETS.THIS_MONTH:
          return [
            format(startOfMonth(today), DateFormats.yearMonthDay),
            format(endOfMonth(today), DateFormats.yearMonthDay),
          ];
        case PRESETS.LAST_MONTH:
          return [
            format(startOfMonth(subMonths(today, 1)), DateFormats.yearMonthDay),
            format(endOfMonth(subMonths(today, 1)), DateFormats.yearMonthDay),
          ];
        case PRESETS.LAST_YEAR:
          return [
            format(startOfYear(subYears(today, 1)), DateFormats.yearMonthDay),
            format(endOfYear(subYears(today, 1)), DateFormats.yearMonthDay),
          ];
        default:
          return [];
      }
    },

    applyPreset(preset: Preset) {
      if (!this.calendar) return;

      const dates = this.getPresetDates(preset);
      const url = new URL(window.location.href);

      if (dates.length) {
        url.searchParams.set('start_date', dates[0]);
        url.searchParams.set('end_date', dates[1]);
      } else {
        url.searchParams.delete('start_date');
        url.searchParams.delete('end_date');
      }

      window.location.href = url.toString();
    },

    updateActivePreset() {
      if (!this.$el) return;

      const url = new URL(window.location.href);
      const startDate = url.searchParams.get('start_date');
      const endDate = url.searchParams.get('end_date');

      let activePreset: Preset | '' = '';

      if (!startDate && !endDate) {
        activePreset = PRESETS.ALL_TIME;
      } else if (startDate && endDate) {
        const presets = (Object.values(PRESETS) as Preset[]).filter((key) => key !== PRESETS.ALL_TIME);

        for (const preset of presets) {
          const [start, end] = this.getPresetDates(preset);
          if (start === startDate && end === endDate) {
            activePreset = preset;
            break;
          }
        }
      }

      const buttons = this.$el.querySelectorAll('.vc-presets [data-preset]');
      buttons.forEach((btn) => {
        if (activePreset && btn.getAttribute('data-preset') === activePreset) {
          btn.setAttribute('data-active', '');
        } else {
          btn.removeAttribute('data-active');
        }
      });
    },
  };
}

export const calendarMeta: AlpineComponentMeta = {
  name: 'calendar',
  component: calendar,
};
