import { __ } from '@wordpress/i18n';
import dayjs from 'dayjs';

import { DateFormats } from '@Core/ts/date-formats';
import { type AlpineComponentMeta } from '@Core/ts/types';
import { tutorConfig } from '@TutorShared/config/config';
import { type Calendar, type Options, Calendar as VanillaCalendar } from 'vanilla-calendar-pro';

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

const TUTOR_CALENDAR_SELECTORS = {
  form: 'form[x-data*="tutorForm"]',
  modalContent: '.tutor-modal-content',
  actionButton: '[data-calendar-action]',
  presetButton: '[data-preset]',
} as const;

const VC_CALENDAR_SELECTORS = {
  navigationControls: '[data-vc="controls"]',
  dateCell: '[data-vc-date]',
  presetButtonsContainer: '.vc-presets [data-preset]',
} as const;

const TUTOR_CALENDAR_DATA_ATTRS = {
  action: 'data-calendar-action',
  preset: 'data-preset',
  active: 'data-active',
  modalCalendar: 'data-tutor-modal-calendar',
} as const;

const VC_CALENDAR_DATA_ATTRS = {
  date: 'data-vc-date',
  calendarHidden: 'data-vc-calendar-hidden',
} as const;

const TUTOR_CALENDAR_VALUES = {
  apply: 'apply',
  clear: 'clear',
  calendarZIndex: '100001',
  themeAttrDetect: '[data-tutor-theme]',
  calendarClasses: 'vc tutor-vc-calendar',
} as const;

const TUTOR_DOM_VALUES = {
  fixed: 'fixed',
  auto: 'auto',
} as const;

const TUTOR_CALENDAR_EVENTS = {
  click: 'click',
  focus: 'focus',
  pointerDown: 'pointerdown',
  mouseDown: 'mousedown',
  popstate: 'popstate',
  calendarClear: 'tutor-calendar:clear',
} as const;

const TUTOR_CALENDAR_QUERY_PARAMS = {
  startDate: 'start_date',
  endDate: 'end_date',
  date: 'date',
  currentPage: 'current_page',
} as const;

export function calendar({ options, hidePopover }: { options: Options; hidePopover?: () => void }) {
  return {
    $el: undefined as HTMLElement | HTMLInputElement | undefined,
    $nextTick: null as unknown as (callback: () => void) => void,
    calendar: null as Calendar | null,
    calendarRootElement: null as HTMLElement | null,
    cleanupCalendarRootListeners: null as (() => void) | null,
    calendarClearHandler: null as (() => void) | null,
    popStateHandler: null as (() => void) | null,

    parseInputValue(inputValue: string): { selectedDates: string[]; selectedTime: string } {
      const normalizedValue = inputValue.trim();

      if (!normalizedValue) {
        return { selectedDates: [], selectedTime: '' };
      }

      const [datePart, ...timeParts] = normalizedValue.split(' ');
      const selectedDates = datePart ? [datePart] : [];
      const selectedTime = timeParts.join(' ').trim();

      return { selectedDates, selectedTime };
    },

    syncCalendarWithInputValue(inputValue?: string): void {
      if (!this.calendar || !this.calendar.context.inputElement) {
        return;
      }

      const value = inputValue ?? this.calendar.context.inputElement.value;
      const { selectedDates, selectedTime } = this.parseInputValue(value);

      this.calendar.set({
        selectedDates,
        selectedTime: selectedTime || undefined,
      });
    },

    setupFormIntegration(): void {
      if (!options.inputMode || !(this.$el instanceof HTMLInputElement)) {
        return;
      }

      const inputElement = this.$el;
      const fieldName = inputElement.name;
      if (!fieldName) {
        return;
      }

      const formElement = inputElement.closest(TUTOR_CALENDAR_SELECTORS.form) as HTMLElement | null;
      if (!formElement) {
        return;
      }

      try {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        const alpineData = window.Alpine?.$data(formElement) as any;
        if (!alpineData || typeof alpineData.setValue !== 'function') {
          return;
        }

        const inputValue = inputElement.value?.trim() ?? '';
        const formValue = alpineData.values?.[fieldName];
        const normalizedFormValue = formValue == null ? '' : String(formValue).trim();

        // Keep initial input value as the form baseline default when form has no value yet.
        if (!normalizedFormValue && inputValue) {
          alpineData.setValue(fieldName, inputValue, {
            shouldValidate: false,
            shouldTouch: false,
            shouldDirty: false,
          });

          if (alpineData.fields?.[fieldName]) {
            alpineData.fields[fieldName].defaultValue = inputValue;
          }
        } else if (normalizedFormValue && normalizedFormValue !== inputValue) {
          inputElement.value = normalizedFormValue;
          this.syncCalendarWithInputValue(normalizedFormValue);
        }

        // Watch external form updates (setValue/reset/setValues) and keep calendar state in sync.
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        const component = this as unknown as { $watch?: (path: string, cb: (value: any) => void) => void };
        component.$watch?.(`values['${fieldName}']`, (newValue) => {
          const normalizedValue = newValue == null ? '' : String(newValue).trim();
          if (inputElement.value !== normalizedValue) {
            inputElement.value = normalizedValue;
          }

          this.syncCalendarWithInputValue(normalizedValue);
        });
      } catch (error) {
        // eslint-disable-next-line no-console
        console.warn('Failed to integrate calendar with form:', error);
      }
    },

    getCalendarRootElement(): HTMLElement | null {
      const calendarContext = this.calendar?.context as { mainElement?: HTMLElement } | undefined;
      const mainElement = calendarContext?.mainElement ?? null;
      if (!mainElement) {
        return null;
      }

      // In input mode, before first open, mainElement points to the input itself.
      if (mainElement instanceof HTMLInputElement) {
        return null;
      }

      return mainElement;
    },

    setupCalendarRootListeners(): void {
      const rootElement = this.getCalendarRootElement();
      if (!rootElement) {
        return;
      }

      this.cleanupCalendarRootListeners?.();

      this.calendarRootElement = rootElement;

      const clickHandler = (event: Event) => {
        this.handleNavigationClick(event);
        this.handlePresetClick(event);
        this.handleActionClick(event);
      };

      // Keep modal outside-click handlers from receiving calendar clicks.
      const pointerDownHandler = (event: Event) => {
        event.stopPropagation();
      };

      rootElement.addEventListener(TUTOR_CALENDAR_EVENTS.click, clickHandler);
      rootElement.addEventListener(TUTOR_CALENDAR_EVENTS.pointerDown, pointerDownHandler);
      rootElement.addEventListener(TUTOR_CALENDAR_EVENTS.mouseDown, pointerDownHandler);

      this.cleanupCalendarRootListeners = () => {
        rootElement.removeEventListener(TUTOR_CALENDAR_EVENTS.click, clickHandler);
        rootElement.removeEventListener(TUTOR_CALENDAR_EVENTS.pointerDown, pointerDownHandler);
        rootElement.removeEventListener(TUTOR_CALENDAR_EVENTS.mouseDown, pointerDownHandler);
      };
    },

    integrateWithModalContext(): void {
      if (!options.inputMode || !(this.$el instanceof HTMLInputElement)) {
        return;
      }

      const inputElement = this.$el;
      const modalContent = inputElement.closest(TUTOR_CALENDAR_SELECTORS.modalContent) as HTMLElement | null;
      if (!modalContent) {
        return;
      }

      const rootElement = this.getCalendarRootElement();
      if (!rootElement) {
        return;
      }

      rootElement.setAttribute(TUTOR_CALENDAR_DATA_ATTRS.modalCalendar, 'true');
      if (!modalContent.contains(rootElement)) {
        modalContent.appendChild(rootElement);
      }

      // Keep viewport-based coordinates after moving into modal DOM.
      rootElement.removeAttribute(VC_CALENDAR_DATA_ATTRS.calendarHidden);
      rootElement.style.pointerEvents = TUTOR_DOM_VALUES.auto;
      rootElement.style.position = TUTOR_DOM_VALUES.fixed;
      rootElement.style.zIndex = TUTOR_CALENDAR_VALUES.calendarZIndex;
    },

    init(): void {
      if (!this.$el) return;

      this.$nextTick(() => {
        const el = this.$el!;
        const url = new URL(window.location.href);

        const selectedDates: string[] = [];
        let selectedTime = '';

        // For input mode
        if (options.inputMode && (el as HTMLInputElement).value) {
          const parsedValue = this.parseInputValue((el as HTMLInputElement).value);
          selectedDates.push(...parsedValue.selectedDates);
          selectedTime = parsedValue.selectedTime;
        } else {
          // For filter mode
          const startDate = url.searchParams.get(TUTOR_CALENDAR_QUERY_PARAMS.startDate);
          const endDate = url.searchParams.get(TUTOR_CALENDAR_QUERY_PARAMS.endDate);
          const singleDate = url.searchParams.get(TUTOR_CALENDAR_QUERY_PARAMS.date);

          if (startDate && endDate) {
            selectedDates.push(startDate, endDate);
          } else if (singleDate) {
            selectedDates.push(singleDate);
          }
        }

        const userOnInit = options.onInit;
        const userOnShow = options.onShow;

        this.calendar = new VanillaCalendar(el, {
          ...options,
          themeAttrDetect: TUTOR_CALENDAR_VALUES.themeAttrDetect,
          locale: tutorConfig.local?.replace('_', '-') ?? 'en-US',
          enableJumpToSelectedDate: true,
          selectedWeekends: [],
          displayDatesOutside: false,
          ...(selectedDates.length ? { selectedDates } : {}),
          ...(selectedTime ? { selectedTime } : {}),
          onClickDate: (self, event) => this.handleDateClick(self, event as MouseEvent),
          onInit: (self) => {
            this.setupCalendarRootListeners();
            this.integrateWithModalContext();
            userOnInit?.(self);
          },
          onShow: (self) => {
            this.setupCalendarRootListeners();
            this.integrateWithModalContext();
            userOnShow?.(self);
          },
          styles: {
            calendar: TUTOR_CALENDAR_VALUES.calendarClasses,
          },
          layouts: {
            multiple: `
              <aside class="vc-presets">
                ${(Object.entries(PRESET_LABELS) as [Preset, string][])
                  .map(
                    ([key, label]) => `
                    <button type="button" ${TUTOR_CALENDAR_DATA_ATTRS.preset}="${key}">
                      <span>${label}</span>
                      <span class="vc-preset-icon" x-data="tutorIcon({ name: 'check-2' })"></span>
                    </button>
                  `,
                  )
                  .join('')}
              </aside>

              <div class="vc-controls" data-vc="controls" role="toolbar" aria-label="Calendar Navigation">
                <#ArrowPrev />
                <#ArrowNext />
              </div>

              <div class="vc-grid" data-vc="grid">
                <#Multiple>
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
                      </div>
                    </div>
                  </div>
                <#/Multiple>
                <#DateRangeTooltip />
              </div>

              <#ControlTime />

              <div class="vc-footer tutor-flex tutor-justify-end tutor-gap-5 tutor-mt-6">
                <button type="button" ${TUTOR_CALENDAR_DATA_ATTRS.action}="${TUTOR_CALENDAR_VALUES.clear}" class="tutor-btn tutor-btn-secondary tutor-btn-small">
                ${__('Clear Selection', 'tutor')}
                </button>
                <button type="button" ${TUTOR_CALENDAR_DATA_ATTRS.action}="${TUTOR_CALENDAR_VALUES.apply}" class="tutor-btn tutor-btn-primary tutor-btn-small">
                ${__('Apply', 'tutor')}
                </button>
              </div>
            `,
          },
        });

        this.calendar.init();
        if (options.inputMode) {
          this.syncCalendarWithInputValue((el as HTMLInputElement).value);
          this.setupFormIntegration();
        }
        this.updateActivePreset();

        el.addEventListener(TUTOR_CALENDAR_EVENTS.focus, () => this.syncCalendarWithInputValue());
        el.addEventListener(TUTOR_CALENDAR_EVENTS.click, () => this.syncCalendarWithInputValue());

        this.popStateHandler = () => this.updateActivePreset();
        this.calendarClearHandler = () => this.clear();
        window.addEventListener(TUTOR_CALENDAR_EVENTS.popstate, this.popStateHandler);
        window.addEventListener(TUTOR_CALENDAR_EVENTS.calendarClear, this.calendarClearHandler);
      });
    },

    destroy() {
      this.cleanupCalendarRootListeners?.();
      this.cleanupCalendarRootListeners = null;
      this.calendarRootElement = null;
      this.calendar?.destroy();
      this.calendar = null;

      if (this.popStateHandler) {
        window.removeEventListener(TUTOR_CALENDAR_EVENTS.popstate, this.popStateHandler);
        this.popStateHandler = null;
      }

      if (this.calendarClearHandler) {
        window.removeEventListener(TUTOR_CALENDAR_EVENTS.calendarClear, this.calendarClearHandler);
        this.calendarClearHandler = null;
      }
    },

    handleActionClick(e: Event) {
      const target = (e.target as HTMLElement).closest(TUTOR_CALENDAR_SELECTORS.actionButton);
      if (!target) return;

      const action = target.getAttribute(TUTOR_CALENDAR_DATA_ATTRS.action);
      if (action === TUTOR_CALENDAR_VALUES.apply) {
        this.applyRange();
      } else if (action === TUTOR_CALENDAR_VALUES.clear) {
        this.clear();
      }
    },

    handleNavigationClick(e: Event) {
      const target = (e.target as HTMLElement).closest(VC_CALENDAR_SELECTORS.navigationControls);
      if (!target) return;

      e.stopPropagation();
    },

    handlePresetClick(e: Event) {
      const target = (e.target as HTMLElement).closest(TUTOR_CALENDAR_SELECTORS.presetButton);
      if (!target) return;

      const preset = target.getAttribute(TUTOR_CALENDAR_DATA_ATTRS.preset) as Preset | null;
      if (preset) {
        this.applyPreset(preset);
      }
    },

    handleDateClick(self: Calendar, event: MouseEvent) {
      event.stopPropagation();
      if (self.context.inputElement) {
        this.handleInputSelection(self);
      } else if (self.selectionDatesMode === 'multiple-ranged') {
        const date = (event.target as HTMLElement)
          .closest(VC_CALENDAR_SELECTORS.dateCell)
          ?.getAttribute(VC_CALENDAR_DATA_ATTRS.date);

        if (date && self.context.selectedDates.length === 2 && !self.context.selectedDates[0]) {
          this.calendar?.set({ selectedDates: [date, date] });
        }
      } else {
        this.handleSingleDateSelection(self);
      }
    },

    handleInputSelection(self: Calendar) {
      const selectedDate = self.context.selectedDates[0] ?? '';
      const selectedTime = self.context.selectedTime ?? '';
      const inputValue = `${selectedDate}${selectedTime ? ` ${selectedTime}` : ''}`.trim();

      if (self.context.inputElement) {
        self.context.inputElement.value = inputValue;
        self.context.inputElement.dispatchEvent(new Event('input', { bubbles: true }));
        self.context.inputElement.dispatchEvent(new Event('change', { bubbles: true }));
        self.context.inputElement.dispatchEvent(new Event('blur', { bubbles: true }));
        self.hide();
      }
    },

    applyRange() {
      if (!this.calendar || this.calendar.context.selectedDates.length !== 2) return;

      hidePopover?.();
      this.navigateWithParams({
        [TUTOR_CALENDAR_QUERY_PARAMS.startDate]: this.calendar.context.selectedDates[0],
        [TUTOR_CALENDAR_QUERY_PARAMS.endDate]: this.calendar.context.selectedDates[1],
      });
    },

    handleSingleDateSelection(self: Calendar) {
      hidePopover?.();
      this.navigateWithParams({
        [TUTOR_CALENDAR_QUERY_PARAMS.date]: self.context.selectedDates[0],
      });
    },

    navigateWithParams(params: Record<string, string | null>) {
      const url = new URL(window.location.href);

      // Always reset pagination when the date filter changes.
      url.searchParams.delete(TUTOR_CALENDAR_QUERY_PARAMS.currentPage);

      // Also strip any additional caller-specified params.
      if (Array.isArray((options as Record<string, unknown>).clearParams)) {
        ((options as Record<string, unknown>).clearParams as string[]).forEach((key) => {
          url.searchParams.delete(key);
        });
      }

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
        [TUTOR_CALENDAR_QUERY_PARAMS.startDate]: null,
        [TUTOR_CALENDAR_QUERY_PARAMS.endDate]: null,
        [TUTOR_CALENDAR_QUERY_PARAMS.date]: null,
      });
    },

    getPresetDates(preset: Preset): string[] {
      const today = dayjs().startOf('day');

      switch (preset) {
        case PRESETS.ALL_TIME:
          return [];
        case PRESETS.YESTERDAY:
          return [
            today.subtract(1, 'day').format(DateFormats.yearMonthDay),
            today.subtract(1, 'day').format(DateFormats.yearMonthDay),
          ];
        case PRESETS.LAST_7:
          return [today.subtract(6, 'day').format(DateFormats.yearMonthDay), today.format(DateFormats.yearMonthDay)];
        case PRESETS.LAST_14:
          return [today.subtract(13, 'day').format(DateFormats.yearMonthDay), today.format(DateFormats.yearMonthDay)];
        case PRESETS.LAST_30:
          return [today.subtract(29, 'day').format(DateFormats.yearMonthDay), today.format(DateFormats.yearMonthDay)];
        case PRESETS.THIS_MONTH:
          return [
            today.startOf('month').format(DateFormats.yearMonthDay),
            today.endOf('month').format(DateFormats.yearMonthDay),
          ];
        case PRESETS.LAST_MONTH:
          return [
            today.subtract(1, 'month').startOf('month').format(DateFormats.yearMonthDay),
            today.subtract(1, 'month').endOf('month').format(DateFormats.yearMonthDay),
          ];
        case PRESETS.LAST_YEAR:
          return [
            today.subtract(1, 'year').startOf('year').format(DateFormats.yearMonthDay),
            today.subtract(1, 'year').endOf('year').format(DateFormats.yearMonthDay),
          ];
        default:
          return [];
      }
    },

    applyPreset(preset: Preset) {
      if (!this.calendar) return;

      const dates = this.getPresetDates(preset);

      if (dates.length) {
        this.navigateWithParams({
          [TUTOR_CALENDAR_QUERY_PARAMS.startDate]: dates[0],
          [TUTOR_CALENDAR_QUERY_PARAMS.endDate]: dates[1],
        });
      } else {
        this.navigateWithParams({
          [TUTOR_CALENDAR_QUERY_PARAMS.startDate]: null,
          [TUTOR_CALENDAR_QUERY_PARAMS.endDate]: null,
        });
      }
    },

    updateActivePreset() {
      if (!this.$el) return;

      const url = new URL(window.location.href);
      const startDate = url.searchParams.get(TUTOR_CALENDAR_QUERY_PARAMS.startDate);
      const endDate = url.searchParams.get(TUTOR_CALENDAR_QUERY_PARAMS.endDate);

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

      const buttons = this.$el.querySelectorAll(VC_CALENDAR_SELECTORS.presetButtonsContainer);
      buttons.forEach((btn) => {
        if (activePreset && btn.getAttribute(TUTOR_CALENDAR_DATA_ATTRS.preset) === activePreset) {
          btn.setAttribute(TUTOR_CALENDAR_DATA_ATTRS.active, '');
        } else {
          btn.removeAttribute(TUTOR_CALENDAR_DATA_ATTRS.active);
        }
      });
    },
  };
}

export const calendarMeta: AlpineComponentMeta = {
  name: 'calendar',
  component: calendar,
};
