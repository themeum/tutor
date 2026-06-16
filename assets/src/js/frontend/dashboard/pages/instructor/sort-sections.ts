import type { Sortable } from '@dnd-kit/dom/sortable';
import { __ } from '@wordpress/i18n';

import { type AjaxResponse } from '@Core/ts/types';

const loadDndKit = async () => {
  const [dom, modifiers, sortable] = await Promise.all([
    import(/* webpackChunkName: "tutor-dnd-kit" */ '@dnd-kit/dom'),
    import(/* webpackChunkName: "tutor-dnd-kit" */ '@dnd-kit/dom/modifiers'),
    import(/* webpackChunkName: "tutor-dnd-kit" */ '@dnd-kit/dom/sortable'),
  ]);

  return {
    DragDropManager: dom.DragDropManager,
    KeyboardSensor: dom.KeyboardSensor,
    PointerSensor: dom.PointerSensor,
    RestrictToElement: modifiers.RestrictToElement,
    Sortable: sortable.Sortable,
  };
};

export const sortSections = (sectionsIds: string[]) => {
  const { toast, endpoints } = window.TutorCore;
  const { wpPost } = window.TutorCore.api;

  return {
    _sortables: [] as Sortable[],
    _sortableSections: [] as HTMLElement[],
    initialized: false,
    sectionsIds: sectionsIds,
    $el: null as HTMLElement | null,

    init() {
      if (!this.initialized) {
        this.setupDrag();
        this.initialized = true;
      }
    },

    async setupDrag() {
      const container = this.$el;
      if (!container) {
        return;
      }

      const { DragDropManager, KeyboardSensor, PointerSensor, RestrictToElement, Sortable } = await loadDndKit();
      if (!this.initialized) {
        return;
      }

      const manager = new DragDropManager({
        sensors: [PointerSensor, KeyboardSensor],
      });

      const items = Array.from(container.querySelectorAll<HTMLElement>('.tutor-popover-menu-item'));

      this._sortables = [];
      this._sortableSections = [];

      for (const [idx, element] of items.entries()) {
        const handle = element.querySelector('[data-grab]') as HTMLElement | null;
        const id = element.dataset.id ?? String(idx);

        const sortable = new Sortable(
          {
            id,
            index: idx,
            element: element,
            handle: handle ?? undefined,
            modifiers: [
              RestrictToElement.configure({
                element: this.$el,
              }),
            ],
          },
          manager,
        );

        this._sortables.push(sortable);
      }

      manager.monitor.addEventListener('dragstart', (event) => {
        const operation = event.operation;
        if (!operation.source) {
          return;
        }

        const sourceElement = operation.source.element;
        if (!sourceElement) {
          return;
        }
      });

      manager.monitor.addEventListener('dragend', async (event) => {
        const operation = event.operation;
        if (!operation.source) {
          return;
        }

        const sourceElement = operation.source.element;
        if (!sourceElement) {
          return;
        }

        const order = this.getOrder();
        try {
          wpPost<AjaxResponse>(endpoints.SAVE_INSTRUCTOR_HOME_SECTIONS_ORDER, { order }).then((response) => {
            if (!response.success) {
              toast.error((response?.data as string) || __('Failed to save instructor home section order.', 'tutor'));
              return;
            }
          });
        } catch (error) {
          const message = error instanceof Error ? error.message : __('Unknown error occurred.', 'tutor');
          toast.error(message);
          return;
        }

        if ('startViewTransition' in document) {
          setTimeout(() => {
            document.startViewTransition(() => this.updateDom());
          }, 260);
        } else {
          this.updateDom();
        }
      });
    },

    async handleCheckboxClick() {
      const items = Array.from(document.querySelectorAll<HTMLInputElement>('input[type="checkbox"]')).reduce(
        (acc, checkbox) => {
          acc[checkbox.name] = checkbox.checked;
          return acc;
        },
        {} as Record<string, boolean>,
      );

      try {
        wpPost<AjaxResponse>(endpoints.SAVE_INSTRUCTOR_HOME_SECTIONS_VISIBILITY, { items }).then((response) => {
          if (!response.success) {
            toast.error(
              (response?.data as string) || __('Failed to save instructor home section visibility.', 'tutor'),
            );
            return;
          }
        });
      } catch (error) {
        const message = error instanceof Error ? error.message : __('Unknown error occurred.', 'tutor');
        toast.error(message);
        return;
      }
    },

    updateDom() {
      const newOrder = this.getOrder();
      const sectionMap = this.getSortableSections();

      const firstSectionId = Object.keys(sectionMap)[0];
      const parentContainer = firstSectionId ? sectionMap[firstSectionId]?.parentElement : null;

      if (!parentContainer) {
        return;
      }

      const existingOrder = Array.from(
        new Set(
          Array.from(parentContainer.querySelectorAll<HTMLElement>('[data-section-id]'))
            .map((el) => el.dataset.sectionId)
            .filter((id): id is string => id !== undefined),
        ),
      );

      if (newOrder.length === existingOrder.length && newOrder.every((id, index) => id === existingOrder[index])) {
        return;
      }

      const fragment = document.createDocumentFragment();

      for (const id of newOrder) {
        const section = sectionMap[id];
        if (section) {
          section.remove();
          fragment.appendChild(section);
        }
      }

      parentContainer.appendChild(fragment);
    },

    getOrder() {
      const container = this.$el;
      if (!container) {
        return [];
      }
      const ids = Array.from(container.querySelectorAll<HTMLElement>('.tutor-popover-menu-item'))
        .map((el) => el.dataset.id)
        .filter((id): id is string => id !== undefined);

      return Array.from(new Set(ids));
    },

    getSortableSections() {
      const sections = document.querySelectorAll<HTMLElement>('[data-section-id]');

      return Array.from(sections).reduce(
        (acc, section) => {
          const sectionId = section.dataset.sectionId;
          if (sectionId) {
            acc[sectionId] = section;
          }
          return acc;
        },
        {} as Record<string, HTMLElement>,
      );
    },

    destroy() {
      for (const sortable of this._sortables) {
        sortable.destroy();
      }
      this._sortables = [];

      this.initialized = false;
    },
  };
};
