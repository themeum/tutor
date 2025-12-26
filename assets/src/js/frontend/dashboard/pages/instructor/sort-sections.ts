import { DragDropManager, KeyboardSensor, PointerSensor } from '@dnd-kit/dom';
import { RestrictToElement } from '@dnd-kit/dom/modifiers';
import { Sortable } from '@dnd-kit/dom/sortable';

export const sortSections = (sectionsIds: string[]) => ({
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

  setupDrag() {
    const container = this.$el;
    if (!container) {
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

    manager.monitor.addEventListener('dragend', (event) => {
      const operation = event.operation;
      if (!operation.source) {
        return;
      }

      const sourceElement = operation.source.element;
      if (!sourceElement) {
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
});
