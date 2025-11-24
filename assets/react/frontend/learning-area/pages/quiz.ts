import { DragDropManager, Draggable, Droppable, KeyboardSensor, PointerSensor } from '@dnd-kit/dom';
import { Sortable } from '@dnd-kit/dom/sortable';
import Alpine from 'alpinejs';

export const initializeQuizInterface = () => {
  Alpine.data('tutorQuizTimer', (duration: number) => ({
    total: duration,
    remaining: duration,
    timer: null as number | null,
    element: null as HTMLElement | null,

    // Automatically runs when component is initialized
    init() {
      this.start();
    },

    start() {
      this.stop(); // safety
      this.element = (this as { $el: HTMLElement }).$el;

      this.timer = window.setInterval(() => {
        if (this.remaining > 0) {
          this.remaining--;
        } else {
          this.stop();
          // TODO: auto-submit or lock the quiz here
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

    // Reactive computed values
    get minutes() {
      return String(Math.floor(this.remaining / 60)).padStart(2, '0');
    },

    get seconds() {
      return String(this.remaining % 60).padStart(2, '0');
    },

    get progress() {
      return ((this.total - this.remaining) / this.total) * 100;
    },
  }));

  Alpine.data('tutorQuestionOrdering', () => ({
    _sortables: [] as Array<{ id: string; index: number; element: HTMLElement }>,
    initialized: false, // **guard**

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

      // Track sortable items internally
      this._sortables = [];

      items.forEach((el, idx) => {
        const handle = el.querySelector('[data-grab-handle]') as HTMLElement | null;
        const id = el.dataset.id ?? String(idx);

        // Store item metadata
        this._sortables.push({ id, index: idx, element: el });

        new Sortable(
          {
            id: Math.random().toString(),
            index: idx,
            element: el,
            handle: handle ?? undefined,
            feedback: 'clone',
          },
          manager,
        );
      });

      // on drag start add [data-option='dragging']
      manager.monitor.addEventListener('dragstart', (event) => {
        const op = event.operation;
        if (!op.source) return;

        const el = op.source.element;
        if (!el) return;

        el.setAttribute('data-option', 'dragging');
      });

      // on drag end remove [data-option='dragging']
      manager.monitor.addEventListener('dragend', (event) => {
        const op = event.operation;
        if (!op.source) return;

        const el = op.source.element;
        if (!el) return;

        el.setAttribute('data-option', 'draggable');
      });
    },

    getOrder() {
      const container = (this as { $el: HTMLElement }).$el;
      if (!container) return [];
      return Array.from(container.querySelectorAll<HTMLElement>('.tutor-quiz-question-option')).map(
        (el) => el.dataset.id,
      );
    },
  }));

  Alpine.data('tutorQuestionMatching', () => ({
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

      // Create DragDropManager
      const manager = new DragDropManager({
        sensors: [PointerSensor, KeyboardSensor],
      });

      // Initialize draggable items
      const draggableEls = Array.from(
        container.querySelectorAll<HTMLElement>('.tutor-quiz-question-option[data-option="draggable"]'),
      );

      draggableEls.forEach((el, idx) => {
        const handle = el.querySelector('[data-grab-handle]') as HTMLElement | null;
        const id = el.dataset.id ?? String(idx);

        const draggable = new Draggable(
          {
            id,
            element: el,
            handle: handle ?? undefined,
            feedback: 'clone',
          },
          manager,
        );

        this._draggables.push(draggable);
      });

      // Initialize drop zones
      const dropZoneEls = Array.from(container.querySelectorAll<HTMLElement>('.tutor-quiz-question-option-drop-zone'));

      dropZoneEls.forEach((el, idx) => {
        const id = el.dataset.id ?? String(idx);

        const droppable = new Droppable(
          {
            id,
            element: el,
          },
          manager,
        );

        this._dropZones.push(droppable);
      });

      // Listen for drag events
      manager.monitor.addEventListener('dragstart', (event) => {
        const sourceElement = event.operation.source?.element;
        if (sourceElement) {
          sourceElement.setAttribute('data-option', 'dragging');
        }
      });

      manager.monitor.addEventListener('dragend', (event) => {
        const sourceElement = event.operation.source?.element;
        const targetDropZone = event.operation.target;

        if (sourceElement) {
          // Keep dragging state on overlay
          sourceElement.setAttribute('data-option', 'draggable');
        }

        // Handle successful drop
        if (targetDropZone && event.operation.source) {
          const dropZoneEl = targetDropZone.element;
          const sourceId = event.operation.source.id;

          // get text content from source element
          const clone = document.createElement('div');
          clone.setAttribute('data-option', 'dropped');
          clone.textContent = sourceElement?.textContent ?? '';

          // Replace [data-drop-placeholder] with clone
          const placeholder = dropZoneEl?.querySelector('[data-drop-placeholder]');
          const droppedOption = dropZoneEl?.querySelector('[data-option="dropped"]');
          if (placeholder) {
            placeholder.replaceWith(clone);
          } else if (droppedOption) {
            droppedOption.replaceWith(clone);
          }

          // Save match
          this._matches[targetDropZone.id] = String(sourceId);
        }
      });
    },

    getMatches() {
      // Returns { dropZoneId: draggableId }
      return this._matches;
    },

    reset() {
      // Clear all drop zones
      this._dropZones.forEach((zone) => {
        const el = zone.element;
        while (el?.firstChild) {
          el.removeChild(el.firstChild);
        }
      });

      // Reset all draggables to draggable state
      this._draggables.forEach((d) => {
        d.element?.setAttribute('data-option', 'draggable');
        d.element?.classList.remove('dropped');
      });

      // Clear matches
      this._matches = {};
    },

    destroy() {
      // Clean up all draggables
      this._draggables.forEach((d) => d.destroy());
      this._draggables = [];

      // Clean up all drop zones
      this._dropZones.forEach((d) => d.destroy());
      this._dropZones = [];

      // Clear matches
      this._matches = {};

      this.initialized = false;
    },
  }));
};
