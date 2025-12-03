import { DragDropManager, Draggable, Droppable, KeyboardSensor, PointerSensor } from '@dnd-kit/dom';
import { Sortable } from '@dnd-kit/dom/sortable';
import Alpine from 'alpinejs';

import { TutorComponentRegistry } from '@Core/ts';
import type { AlpineComponentMeta } from '@Core/ts/types';

/**
 * Quiz Timer Component
 * Manages countdown timer for quiz attempts
 */
const quizTimer = (duration: number) => ({
  total: duration,
  remaining: duration,
  timer: null as number | null,
  $el: null as HTMLElement | null,

  init() {
    this.start();
  },

  start() {
    this.stop();

    this.timer = window.setInterval(() => {
      if (this.remaining > 0) {
        this.remaining--;
      } else {
        this.stop();
      }
    }, 1000);

    this.$el?.classList.add('tutor-quiz-progress-animate');
  },

  stop() {
    if (this.timer) {
      clearInterval(this.timer);
      this.timer = null;
      this.$el?.classList.remove('tutor-quiz-progress-animate');
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
});

export const quizTimerMeta = {
  name: 'quizTimer',
  component: quizTimer,
};

/**
 * Question Ordering Component
 * Handles drag-and-drop reordering of quiz question options
 */
const questionOrdering = () => ({
  _sortables: [] as Sortable[],
  initialized: false,
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
    const container = this.$el;
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
});

export const questionOrderingMeta: AlpineComponentMeta = {
  name: 'questionOrdering',
  component: questionOrdering,
};

/**
 * Question Matching Component
 * Handles drag-and-drop matching of quiz question options to drop zones
 */
const questionMatching = () => ({
  _draggables: [] as Draggable[],
  _dropZones: [] as Droppable[],
  _matches: {} as Record<string, string>,
  $el: null as HTMLElement | null,
  initialized: false,

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
});

export const questionMatchingMeta: AlpineComponentMeta = {
  name: 'questionMatching',
  component: questionMatching,
};

export const initializeQuizInterface = () => {
  TutorComponentRegistry.registerAll({
    components: [quizTimerMeta, questionOrderingMeta, questionMatchingMeta],
  });

  TutorComponentRegistry.initWithAlpine(Alpine);
};
