import { __ } from '@wordpress/i18n';

import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type QueryState } from '@Core/ts/services/Query';
import { type AjaxResponse } from '@Core/ts/types';

export interface LazySectionProps {
  section: string;
  dateDependent?: boolean;
  sortDependent?: boolean;
  type?: string;
}

export type JsonPrimitive = string | number | boolean | null;
export type JsonValue = JsonPrimitive | { [key: string]: JsonValue } | JsonValue[];

export interface SectionResponseData {
  html?: string;
  chart_data?: Record<string, JsonValue>;
  has_data?: boolean;
}

export interface LazySectionRefs {
  contentContainer?: HTMLElement;
  [key: string]: HTMLElement | undefined;
}

export interface AlpineMagics<
  TRefs extends Record<string, HTMLElement | undefined> = Record<string, HTMLElement | undefined>,
> {
  $el: HTMLElement;
  $refs: TRefs;
  $nextTick: (callback: () => void) => void;
  $dispatch: (event: string, detail?: JsonValue) => void;
  $watch: <T>(property: string | (() => T), callback: (value: T, oldValue?: T) => void) => void;
}

export interface LazySectionState {
  section: string;
  dateDependent: boolean;
  sortDependent: boolean;
  startDate: string;
  endDate: string;
  sortType: string;
  lastFetchedKey: string;
  query: QueryState<AjaxResponse<SectionResponseData>> | null;
  dateChangeHandler: ((e: Event) => void) | null;
  sortChangeHandler: ((e: Event) => void) | null;
  watch?: (name: string) => boolean | undefined;
  get isVisible(): boolean;
  get isLoading(): boolean;
  get hasError(): boolean;
  get hasData(): boolean;
  get content(): string;
}

export interface LazySectionMethods {
  init(this: LazySectionContext): void;
  destroy(this: LazySectionContext): void;
  fetchSection(this: LazySectionContext): void;
  checkAndFetch(this: LazySectionContext): void;
  reinitTargetTree(this: LazySectionContext): void;
}

export type LazySectionComponent = LazySectionState & LazySectionMethods;

export type LazySectionContext = LazySectionState & LazySectionMethods & AlpineMagics<LazySectionRefs>;

/**
 * Type guard for checking if data conforms to SectionResponseData
 */
function isSectionResponseData(data: unknown): data is SectionResponseData {
  return typeof data === 'object' && data !== null && 'html' in data;
}

export const lazySection = ({
  section,
  dateDependent = false,
  sortDependent = false,
  type = 'revenue',
}: LazySectionProps): LazySectionComponent => {
  const { wpPost } = window.TutorCore.api;
  const { toast } = window.TutorCore;
  const { convertToErrorMessage } = window.TutorCore.error;

  return {
    section,
    dateDependent,
    sortDependent,
    startDate: '',
    endDate: '',
    sortType: type,
    lastFetchedKey: '',
    query: null,
    dateChangeHandler: null,
    sortChangeHandler: null,

    get isVisible(): boolean {
      if (typeof this.watch === 'function') {
        return !!this.watch(this.section);
      }
      return true;
    },

    get isLoading(): boolean {
      if (!this.isVisible) return false;
      return this.query ? this.query.isLoading || this.query.isFetching : true;
    },

    get hasError(): boolean {
      return !!this.query?.error;
    },

    get hasData(): boolean {
      const res = this.query?.data;
      if (!res) return false;
      if ('data' in res && res.data) return res.data.has_data !== false;
      if (isSectionResponseData(res)) return res.has_data !== false;
      return true;
    },

    get content(): string {
      const res = this.query?.data;
      if (!res) return '';
      if ('data' in res && res.data) return res.data.html || '';
      if (isSectionResponseData(res)) return res.html || '';
      return '';
    },

    reinitTargetTree(this: LazySectionContext) {
      this.$nextTick(() => {
        const target = this.$refs.contentContainer || this.$el;
        if (target && window.Alpine) {
          window.Alpine.initTree(target);
        }
      });
    },

    checkAndFetch(this: LazySectionContext) {
      if (!this.isVisible) {
        return;
      }

      const currentKey = `${this.startDate}_${this.endDate}_${this.sortType}`;
      if (this.lastFetchedKey !== currentKey || !this.query?.data) {
        this.lastFetchedKey = currentKey;
        this.fetchSection();
      }
    },

    init(this: LazySectionContext) {
      const params = new URLSearchParams(window.location.search);
      this.startDate = params.get('start_date') || '';
      this.endDate = params.get('end_date') || '';
      this.sortType = params.get('top_performing_course') || params.get('type') || type;

      // Watch for content updates from QueryService and re-initialize Alpine child tree
      this.$watch('content', (newContent: string) => {
        if (newContent) {
          this.reinitTargetTree();
        }
      });

      // Watch for query errors and display toast notifications
      this.$watch('hasError', (isErr: boolean) => {
        if (isErr && this.query?.error) {
          const errorMessage = convertToErrorMessage(this.query.error);
          toast.error(errorMessage || __('Failed to load section data.', 'tutor'));
        }
      });

      // Watch visibility changes (e.g. toggled in Customize View popover)
      this.$watch(
        () => this.isVisible,
        (visible?: boolean) => {
          if (visible) {
            this.checkAndFetch();
          }
        },
      );

      // Only fetch on initial load if the section is currently visible
      if (this.isVisible) {
        this.checkAndFetch();
      }

      if (this.dateDependent) {
        this.dateChangeHandler = (e: Event) => {
          const detail =
            e instanceof CustomEvent ? (e.detail as { startDate?: string; endDate?: string } | undefined) : undefined;
          const newStart = detail?.startDate || '';
          const newEnd = detail?.endDate || '';

          // Guard: Avoid refetch if the filter dates have not changed
          if (this.startDate === newStart && this.endDate === newEnd) {
            return;
          }

          this.startDate = newStart;
          this.endDate = newEnd;

          // If currently visible, fetch immediately. If hidden, checkAndFetch() will run when made visible.
          if (this.isVisible) {
            this.checkAndFetch();
          }
        };
        window.addEventListener(TUTOR_CUSTOM_EVENTS.DATE_FILTER_CHANGED, this.dateChangeHandler);
      }

      if (this.sortDependent) {
        this.sortChangeHandler = (e: Event) => {
          const detail = e instanceof CustomEvent ? (e.detail as { type?: string } | undefined) : undefined;
          const newType = detail?.type || type;

          // Guard: Avoid refetch if sort type has not changed
          if (this.sortType === newType) {
            return;
          }

          this.sortType = newType;

          // If currently visible, fetch immediately. If hidden, checkAndFetch() will run when made visible.
          if (this.isVisible) {
            this.checkAndFetch();
          }
        };
        window.addEventListener(TUTOR_CUSTOM_EVENTS.SORT_CHANGED, this.sortChangeHandler);
      }
    },

    destroy(this: LazySectionContext) {
      if (this.dateChangeHandler) {
        window.removeEventListener(TUTOR_CUSTOM_EVENTS.DATE_FILTER_CHANGED, this.dateChangeHandler);
      }
      if (this.sortChangeHandler) {
        window.removeEventListener(TUTOR_CUSTOM_EVENTS.SORT_CHANGED, this.sortChangeHandler);
      }
    },

    fetchSection(this: LazySectionContext) {
      const queryKey = ['dashboard-section', this.section, this.startDate, this.endDate, this.sortType];
      const queryService = window.TutorCore.query;

      if (queryService) {
        this.query = queryService.useQuery<AjaxResponse<SectionResponseData>>(
          queryKey,
          () =>
            wpPost<AjaxResponse<SectionResponseData>>('tutor_get_dashboard_section', {
              section: this.section,
              start_date: this.startDate,
              end_date: this.endDate,
              top_performing_course: this.sortType,
              type: this.sortType,
            }),
          { staleTime: 5 * 60 * 1000 },
        );

        // If data is already available from fresh cache, trigger tree init immediately
        if (this.query.data) {
          this.reinitTargetTree();
        }
      }
    },
  };
};

export const initializeLazySection = () => {
  if (window.TutorComponentRegistry) {
    window.TutorComponentRegistry.register({
      type: 'component',
      meta: {
        name: 'lazySection',
        component: lazySection,
      },
    });
    window.TutorComponentRegistry.initWithAlpine(window.Alpine);
  }
};
