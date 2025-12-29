import { type AlpineComponentMeta } from '@Core/ts/types';
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
import 'vanilla-calendar-pro/styles/index.css';

export function calendar({ options, hidePopover }: { options: OptionsCalendar; hidePopover?: () => void }) {
  return {
    $el: undefined as HTMLElement | undefined,
    calendar: null as Calendar | null,
    $nextTick: undefined as ((callback: () => void) => void) | undefined,

    init(): void {
      if (!this.$el) return;
      const el = this.$el;

      if (this.$nextTick) {
        this.$nextTick(() => {
          const url = new URL(window.location.href);
          const selectedDates: string[] = [];
          let selectedTime = '';
          const startDate = url.searchParams.get('start_date');
          const endDate = url.searchParams.get('end_date');
          const singleDate = url.searchParams.get('date');

          if (startDate && endDate) {
            selectedDates.push(startDate, endDate);
          } else if (singleDate) {
            selectedDates.push(singleDate);
          } else if ((el as HTMLInputElement).value) {
            const parts = (el as HTMLInputElement).value.split(' ');
            selectedDates.push(parts[0]);
            if (parts.length > 1) {
              selectedTime = parts.slice(1).join(' ');
            }
          }

          this.calendar = new VanillaCalendar(el, {
            ...options,
            themeAttrDetect: 'html[data-theme]',
            selectedTheme: 'light', // @TODO:: Need to update form _tutorobject
            enableJumpToSelectedDate: true,
            selectedWeekends: [],
            displayDatesOutside: false,
            ...(selectedDates.length ? { selectedDates } : {}),
            ...(selectedTime ? { selectedTime } : {}),
            onClickDate(self) {
              if (self.context.inputElement) {
                let inputValue = '';
                if (self.context.selectedDates[0]) {
                  inputValue = `${self.context.selectedDates[0]} ${self.context.selectedTime ?? ''}`;
                }

                self.context.inputElement.value = inputValue;
                self.context.inputElement.dispatchEvent(new Event('change', { bubbles: true }));
                self.hide();
              } else if (self.selectionDatesMode === 'multiple-ranged') {
                if (self.context.selectedDates.length === 2) {
                  hidePopover?.();
                  const url = new URL(window.location.href);
                  url.searchParams.set('start_date', self.context.selectedDates[0]);
                  url.searchParams.set('end_date', self.context.selectedDates[1]);
                  window.location.href = url.toString();
                }
              } else {
                hidePopover?.();
                const url = new URL(window.location.href);
                url.searchParams.set('date', self.context.selectedDates[0]);
                window.location.href = url.toString();
              }
            },
            layouts: {
              multiple: `
                <div class="vc-layout">
                    <aside class="vc-presets">
                        <button type="button" data-preset="all-time" data-active>${__('All Time', 'tutor')}</button>
                        <button type="button" data-preset="yesterday">${__('Yesterday', 'tutor')}</button>
                        <button type="button" data-preset="last-7">${__('Last 7 days', 'tutor')}</button>
                        <button type="button" data-preset="last-14">${__('Last 14 days', 'tutor')}</button>
                        <button type="button" data-preset="last-30">${__('Last 30 days', 'tutor')}</button>
                        <button type="button" data-preset="this-month">${__('This month', 'tutor')}</button>
                        <button type="button" data-preset="last-month">${__('Last month', 'tutor')}</button>
                        <button type="button" data-preset="last-year">${__('Last year', 'tutor')}</button>
                    </aside>

                    <!-- CALENDAR SIDE -->
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
                    </div>
                </div>
            `,
            },
            locale: {
              months: {
                long: [
                  __('January', 'tutor'),
                  __('February', 'tutor'),
                  __('March', 'tutor'),
                  __('April', 'tutor'),
                  __('May', 'tutor'),
                  __('June', 'tutor'),
                  __('July', 'tutor'),
                  __('August', 'tutor'),
                  __('September', 'tutor'),
                  __('October', 'tutor'),
                  __('November', 'tutor'),
                  __('December', 'tutor'),
                ],
                short: [
                  __('Jan', 'tutor'),
                  __('Feb', 'tutor'),
                  __('Mar', 'tutor'),
                  __('Apr', 'tutor'),
                  __('May', 'tutor'),
                  __('Jun', 'tutor'),
                  __('Jul', 'tutor'),
                  __('Aug', 'tutor'),
                  __('Sep', 'tutor'),
                  __('Oct', 'tutor'),
                  __('Nov', 'tutor'),
                  __('Dec', 'tutor'),
                ],
              },
              weekdays: {
                long: [
                  __('Sunday', 'tutor'),
                  __('Monday', 'tutor'),
                  __('Tuesday', 'tutor'),
                  __('Wednesday', 'tutor'),
                  __('Thursday', 'tutor'),
                  __('Friday', 'tutor'),
                  __('Saturday', 'tutor'),
                ],
                short: [
                  __('Sun', 'tutor'),
                  __('Mon', 'tutor'),
                  __('Tue', 'tutor'),
                  __('Wed', 'tutor'),
                  __('Thu', 'tutor'),
                  __('Fri', 'tutor'),
                  __('Sat', 'tutor'),
                ],
              },
            },
          });
          this.calendar.init();
          this.updateActivePreset();

          el.addEventListener('click', (e) => {
            const target = (e.target as HTMLElement).closest('[data-preset]');
            if (!target) return;

            const preset = target.getAttribute('data-preset');
            if (preset) {
              this.applyPreset(preset);
            }
          });
        });
      }
    },

    getPresetDates(preset: string): string[] {
      const today = startOfToday();
      switch (preset) {
        case 'all-time':
          return [];
        case 'yesterday':
          return [format(subDays(today, 1), 'yyyy-MM-dd'), format(subDays(today, 1), 'yyyy-MM-dd')];
        case 'last-7':
          return [format(subDays(today, 6), 'yyyy-MM-dd'), format(today, 'yyyy-MM-dd')];
        case 'last-14':
          return [format(subDays(today, 13), 'yyyy-MM-dd'), format(today, 'yyyy-MM-dd')];
        case 'last-30':
          return [format(subDays(today, 29), 'yyyy-MM-dd'), format(today, 'yyyy-MM-dd')];
        case 'this-month':
          return [format(startOfMonth(today), 'yyyy-MM-dd'), format(endOfMonth(today), 'yyyy-MM-dd')];
        case 'last-month':
          return [
            format(startOfMonth(subMonths(today, 1)), 'yyyy-MM-dd'),
            format(endOfMonth(subMonths(today, 1)), 'yyyy-MM-dd'),
          ];
        case 'last-year':
          return [
            format(startOfYear(subYears(today, 1)), 'yyyy-MM-dd'),
            format(endOfYear(subYears(today, 1)), 'yyyy-MM-dd'),
          ];
        default:
          return [];
      }
    },

    updateActivePreset() {
      if (!this.$el) return;
      const url = new URL(window.location.href);
      const startDate = url.searchParams.get('start_date');
      const endDate = url.searchParams.get('end_date');

      let activePreset = '';

      if (!startDate && !endDate) {
        activePreset = 'all-time';
      } else if (startDate && endDate) {
        const presets = ['yesterday', 'last-7', 'last-14', 'last-30', 'this-month', 'last-month', 'last-year'];
        for (const preset of presets) {
          const dates = this.getPresetDates(preset);
          if (dates[0] === startDate && dates[1] === endDate) {
            activePreset = preset;
            break;
          }
        }
      }

      const buttons = this.$el.querySelectorAll('[data-preset]');
      buttons.forEach((btn) => {
        if (activePreset && btn.getAttribute('data-preset') === activePreset) {
          btn.setAttribute('data-active', '');
        } else {
          btn.removeAttribute('data-active');
        }
      });
    },

    applyPreset(preset: string) {
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
  };
}

export const calendarMeta: AlpineComponentMeta = {
  name: 'calendar',
  component: calendar,
};
