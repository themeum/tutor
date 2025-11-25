import type { AlpineComponentMeta } from '@Core/ts/types';
import { DragDropManager, Draggable, Droppable, KeyboardSensor, PointerSensor } from '@dnd-kit/dom';
import { Sortable } from '@dnd-kit/dom/sortable';

/**
 * Quiz Timer Component
 * Manages countdown timer for quiz attempts
 */
export const quizTimerMeta: AlpineComponentMeta<number> = {
  name: 'quizTimer',
  component: (duration: number) => ({
    total: duration,
    remaining: duration,
    timer: null as number | null,
    element: null as HTMLElement | null,

    init() {
      this.start();
    },

    start() {
      this.stop();
      this.element = (this as { $el: HTMLElement }).$el;

      this.timer = window.setInterval(() => {
        if (this.remaining > 0) {
          this.remaining--;
        } else {
          this.stop();
        }
      }, 1000);

      this.element.classList.add('tutor-quiz-progress-animate');
    },

    stop() {
      if (this.timer) {
        clearInterval(this.timer);
        this.timer = null;
        this.element?.classList.remove('tutor-quiz-progress-animate');
      }
    },

    get minutes() {
      return String(Math.floor(this.remaining / 60)).padStart(2, '0');
    },

    get seconds() {
      return String(this.remaining % 60).padStart(2, '0');
    },

    get progress() {
      return ((this.total - this.remaining) / this.total) * 100;
    },
  }),
};

/**
 * Question Ordering Component
 * Handles drag-and-drop reordering of quiz question options
 */
export const questionOrderingMeta: AlpineComponentMeta = {
  name: 'questionOrdering',
  component: () => ({
    _sortables: [] as Sortable[],
    initialized: false,

    init() {
      if (!this.initialized) {
        this.setupDrag();
        this.initialized = true;
      }
    },

    setupDrag() {
      const container = (this as { $el: HTMLElement }).$el;
      if (!container) {
        return;
      }

      const manager = new DragDropManager({
        sensors: [PointerSensor, KeyboardSensor],
      });

      const items = Array.from(
        container.querySelectorAll<HTMLElement>('.tutor-quiz-question-option[data-option="draggable"]'),
      );

      this._sortables = [];

      items.forEach((element, idx) => {
        const handle = element.querySelector('[data-grab-handle]') as HTMLElement | null;
        const id = element.dataset.id ?? String(idx);

        const sortable = new Sortable(
          {
            id,
            index: idx,
            element: element,
            handle: handle ?? undefined,
          },
          manager,
        );

        this._sortables.push(sortable);
      });

      manager.monitor.addEventListener('dragstart', (event) => {
        const operation = event.operation;
        if (!operation.source) {
          return;
        }

        const sourceElement = operation.source.element;
        if (!sourceElement) {
          return;
        }

        sourceElement.setAttribute('data-option', 'dragging');
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

        sourceElement.setAttribute('data-option', 'draggable');
      });
    },

    // @TODO: Will be removed if not needed
    getOrder() {
      const container = (this as { $el: HTMLElement }).$el;
      if (!container) {
        return [];
      }
      return Array.from(container.querySelectorAll<HTMLElement>('.tutor-quiz-question-option')).map(
        (el) => el.dataset.id,
      );
    },

    destroy() {
      this._sortables.forEach((s: Sortable) => s.destroy());
      this._sortables = [];

      this.initialized = false;
    },
  }),
};

/**
 * Question Matching Component
 * Handles drag-and-drop matching of quiz question options to drop zones
 */
export const questionMatchingMeta: AlpineComponentMeta = {
  name: 'questionMatching',
  component: () => ({
    _draggables: [] as Draggable[],
    _dropZones: [] as Droppable[],
    _matches: {} as Record<string, string>,
    initialized: false,

    init() {
      if (!this.initialized) {
        this.setupDrag();
        this.initialized = true;
      }
    },

    setupDrag() {
      const container = (this as { $el: HTMLElement }).$el;
      if (!container) {
        return;
      }

      const manager = new DragDropManager({
        sensors: [PointerSensor, KeyboardSensor],
      });

      const draggableEls = Array.from(
        container.querySelectorAll<HTMLElement>('.tutor-quiz-question-option[data-option="draggable"]'),
      );

      draggableEls.forEach((element, idx) => {
        const handle = element.querySelector('[data-grab-handle]') as HTMLElement | null;
        const id = element.dataset.id ?? String(idx);

        const draggable = new Draggable(
          {
            id,
            element: element,
            handle: handle ?? undefined,
            feedback: 'clone',
          },
          manager,
        );

        this._draggables.push(draggable);
      });

      const dropZoneElements = Array.from(
        container.querySelectorAll<HTMLElement>('.tutor-quiz-question-option-drop-zone'),
      );

      dropZoneElements.forEach((element, idx) => {
        const id = element.dataset.id ?? String(idx);

        const droppable = new Droppable(
          {
            id,
            element: element,
          },
          manager,
        );

        this._dropZones.push(droppable);
      });

      manager.monitor.addEventListener('dragstart', (event) => {
        const operation = event.operation;
        if (!operation.source) {
          return;
        }

        const sourceElement = operation.source.element;
        if (sourceElement) {
          sourceElement.setAttribute('data-option', 'dragging');
        }
      });

      manager.monitor.addEventListener('dragend', (event) => {
        const operation = event.operation;
        if (!operation.source) {
          return;
        }

        const sourceElement = operation.source.element;
        const targetDropZone = operation.target;

        if (sourceElement) {
          sourceElement.setAttribute('data-option', 'draggable');
        }

        if (targetDropZone) {
          const dropZoneEl = targetDropZone.element;
          const sourceId = operation.source.id;

          const clone = document.createElement('div');
          clone.setAttribute('data-option', 'dropped');
          clone.setAttribute('data-id', String(sourceId));
          clone.textContent = sourceElement?.textContent ?? '';

          const placeholder = dropZoneEl?.querySelector('[data-drop-placeholder]');
          const droppedOption = dropZoneEl?.querySelector('[data-option="dropped"]');
          if (placeholder) {
            placeholder.replaceWith(clone);
          } else if (droppedOption) {
            droppedOption.replaceWith(clone);
          }

          this._matches[targetDropZone.id] = String(sourceId);
        }
      });
    },

    // @TODO: Will be removed if not needed
    getMatches() {
      return this._matches;
    },

    // @TODO: Will be removed if not needed
    reset() {
      this._dropZones.forEach((zone: Droppable) => {
        const dropZone = zone.element;
        while (dropZone?.firstChild) {
          dropZone.removeChild(dropZone.firstChild);
        }
      });

      this._draggables.forEach((draggable: Draggable) => {
        draggable.element?.setAttribute('data-option', 'draggable');
        draggable.element?.classList.remove('dropped');
      });

      this._matches = {};
    },

    destroy() {
      this._draggables.forEach((draggable: Draggable) => draggable.destroy());
      this._draggables = [];

      this._dropZones.forEach((dropZone: Droppable) => dropZone.destroy());
      this._dropZones = [];

      this._matches = {};

      this.initialized = false;
    },
  }),
};

/**
 * Initialize quiz interface (if needed for additional setup)
 * Components are now registered via ComponentRegistry
 */
export const initializeQuizInterface = () => {
  // Page-specific initialization can go here if needed
  // Components are registered through ComponentRegistry in index.ts
};
