import type { AlpineComponentMeta } from '@Core/ts/types';
import { DragDropManager, Draggable, Droppable, KeyboardSensor, PointerSensor } from '@dnd-kit/dom';
import { Sortable } from '@dnd-kit/dom/sortable';
import { __ } from '@wordpress/i18n';

const QUIZ_CONSTANTS = {
  CLASSES: {
    PROGRESS_ANIMATE: 'tutor-quiz-progress-animate',
    QUESTION_OPTION: 'tutor-quiz-question-option',
    DROP_ZONE: 'tutor-quiz-question-option-drop-zone',
    TEXT_SUBDUED: 'tutor-text-subdued',
    DROPPED: 'dropped',
  },
  ATTRS: {
    OPTION: 'data-option',
    ID: 'data-id',
    GRAB_HANDLE: 'data-grab-handle',
    DROP_ZONE_ID: 'data-drop-zone-id',
    DROP_PLACEHOLDER: 'data-drop-placeholder',
    DROP_PLACEHOLDER_TEXT: 'data-drop-placeholder-text',
  },
  VALUES: {
    DRAGGABLE: 'draggable',
    DRAGGING: 'dragging',
    DROPPED: 'dropped',
  },
  DATASET: {
    ID: 'id',
    OPTION: 'option',
    DROP_ZONE_ID: 'dropZoneId',
    DROP_PLACEHOLDER_TEXT: 'dropPlaceholderText',
  },
} as const;

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

    this.$el?.classList.add(QUIZ_CONSTANTS.CLASSES.PROGRESS_ANIMATE);
  },

  stop() {
    if (this.timer) {
      clearInterval(this.timer);
      this.timer = null;
      this.$el?.classList.remove(QUIZ_CONSTANTS.CLASSES.PROGRESS_ANIMATE);
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
const questionOrdering = (
  config: {
    questionId?: string;
    onOrder?: (values: string[]) => void;
  } = {},
) => ({
  _sortables: [] as Sortable[],
  _questionId: config.questionId,
  _callbacks: {
    onOrder: config.onOrder,
  },
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
      container.querySelectorAll<HTMLElement>(
        `.${QUIZ_CONSTANTS.CLASSES.QUESTION_OPTION}[${QUIZ_CONSTANTS.ATTRS.OPTION}="${QUIZ_CONSTANTS.VALUES.DRAGGABLE}"]`,
      ),
    );

    this._sortables = [];

    items.forEach((element, idx) => {
      const handle = element.querySelector(`[${QUIZ_CONSTANTS.ATTRS.GRAB_HANDLE}]`) as HTMLElement | null;
      const id = element.dataset[QUIZ_CONSTANTS.DATASET.ID] ?? String(idx);

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

      sourceElement.setAttribute(QUIZ_CONSTANTS.ATTRS.OPTION, QUIZ_CONSTANTS.VALUES.DRAGGING);
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

      sourceElement.setAttribute(QUIZ_CONSTANTS.ATTRS.OPTION, QUIZ_CONSTANTS.VALUES.DRAGGABLE);
      this._callbacks.onOrder?.(this.getOrder());
    });
  },

  getOrder() {
    const container = this.$el;
    if (!container) {
      return [];
    }

    const options = Array.from(
      container.querySelectorAll<HTMLElement>(
        `.${QUIZ_CONSTANTS.CLASSES.QUESTION_OPTION}[${QUIZ_CONSTANTS.ATTRS.OPTION}="${QUIZ_CONSTANTS.VALUES.DRAGGABLE}"]`,
      ),
    );

    return options.map((option) => option.dataset[QUIZ_CONSTANTS.DATASET.ID] ?? '').filter(Boolean);
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
const questionMatching = (
  config: {
    questionId?: string;
    onDrop?: (values: string[]) => void;
    onClear?: (values: string[]) => void;
  } = {},
) => ({
  _draggables: [] as Draggable[],
  _dropZones: [] as Droppable[],
  _matches: {} as Record<string, string>,
  _dropZoneOrder: [] as string[],
  _questionId: config.questionId,
  _callbacks: {
    onDrop: config.onDrop,
    onClear: config.onClear,
  },
  $el: null as HTMLElement | null,
  $event: null as Event | null,
  initialized: false,

  _getValuesFromMatches(): string[] {
    return this._dropZoneOrder.map((id) => this._matches[id] ?? '');
  },
  clearDropZone() {
    const target = this.$event?.target as HTMLElement | null;
    const dropZone = target?.closest(`.${QUIZ_CONSTANTS.CLASSES.DROP_ZONE}`) as HTMLElement | null;
    if (!dropZone) {
      return;
    }

    const droppedOption = dropZone.querySelector(`[${QUIZ_CONSTANTS.ATTRS.OPTION}="${QUIZ_CONSTANTS.VALUES.DROPPED}"]`);
    if (droppedOption) {
      droppedOption.remove();
    }

    this._restoreDropPlaceholder(dropZone);

    const dropZoneId = dropZone.dataset[QUIZ_CONSTANTS.DATASET.DROP_ZONE_ID];
    if (dropZoneId && this._matches[dropZoneId]) {
      delete this._matches[dropZoneId];
    }

    const values = this._getValuesFromMatches();
    this._callbacks.onClear?.(values);
  },

  _restoreDropPlaceholder(dropZoneEl: HTMLElement) {
    const existingPlaceholder = dropZoneEl.querySelector(`[${QUIZ_CONSTANTS.ATTRS.DROP_PLACEHOLDER}]`);
    if (existingPlaceholder) {
      return;
    }

    const placeholder = document.createElement('span');
    placeholder.setAttribute(QUIZ_CONSTANTS.ATTRS.DROP_PLACEHOLDER, '');
    placeholder.className = QUIZ_CONSTANTS.CLASSES.TEXT_SUBDUED;
    placeholder.textContent =
      dropZoneEl.dataset[QUIZ_CONSTANTS.DATASET.DROP_PLACEHOLDER_TEXT] || __('Drop here', 'tutor');
    dropZoneEl.prepend(placeholder);
  },

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
      container.querySelectorAll<HTMLElement>(
        `.${QUIZ_CONSTANTS.CLASSES.QUESTION_OPTION}[${QUIZ_CONSTANTS.ATTRS.OPTION}="${QUIZ_CONSTANTS.VALUES.DRAGGABLE}"]`,
      ),
    );

    draggableEls.forEach((element, idx) => {
      const handle = element.querySelector(`[${QUIZ_CONSTANTS.ATTRS.GRAB_HANDLE}]`) as HTMLElement | null;
      const id = element.dataset[QUIZ_CONSTANTS.DATASET.ID] ?? String(idx);

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
      container.querySelectorAll<HTMLElement>(`.${QUIZ_CONSTANTS.CLASSES.DROP_ZONE}`),
    );

    dropZoneElements.forEach((element, idx) => {
      const id = element.dataset[QUIZ_CONSTANTS.DATASET.ID] ?? String(idx);
      element.dataset[QUIZ_CONSTANTS.DATASET.DROP_ZONE_ID] = id;
      this._dropZoneOrder.push(id);

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
        sourceElement.setAttribute(QUIZ_CONSTANTS.ATTRS.OPTION, QUIZ_CONSTANTS.VALUES.DRAGGING);
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
        sourceElement.setAttribute(QUIZ_CONSTANTS.ATTRS.OPTION, QUIZ_CONSTANTS.VALUES.DRAGGABLE);
      }

      if (targetDropZone) {
        const dropZoneEl = targetDropZone.element;
        const sourceId = operation.source.id;

        const clone = document.createElement('div');
        clone.setAttribute(QUIZ_CONSTANTS.ATTRS.OPTION, QUIZ_CONSTANTS.VALUES.DROPPED);
        clone.setAttribute(QUIZ_CONSTANTS.ATTRS.ID, String(sourceId));
        clone.textContent = sourceElement?.textContent ?? '';

        const placeholder = dropZoneEl?.querySelector(`[${QUIZ_CONSTANTS.ATTRS.DROP_PLACEHOLDER}]`);
        const droppedOption = dropZoneEl?.querySelector(
          `[${QUIZ_CONSTANTS.ATTRS.OPTION}="${QUIZ_CONSTANTS.VALUES.DROPPED}"]`,
        );
        if (placeholder) {
          placeholder.replaceWith(clone);
        } else if (droppedOption) {
          droppedOption.replaceWith(clone);
        }

        this._matches[targetDropZone.id] = String(sourceId);
        const values = this._getValuesFromMatches();
        this._callbacks.onDrop?.(values);
      }
    });
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
      draggable.element?.setAttribute(QUIZ_CONSTANTS.ATTRS.OPTION, QUIZ_CONSTANTS.VALUES.DRAGGABLE);
      draggable.element?.classList.remove(QUIZ_CONSTANTS.CLASSES.DROPPED);
    });

    this._matches = {};
    this._dropZoneOrder = [];
  },

  destroy() {
    this._draggables.forEach((draggable: Draggable) => draggable.destroy());
    this._draggables = [];

    this._dropZones.forEach((dropZone: Droppable) => dropZone.destroy());
    this._dropZones = [];

    this._matches = {};
    this._dropZoneOrder = [];

    this.initialized = false;
  },
});

export const questionMatchingMeta: AlpineComponentMeta = {
  name: 'questionMatching',
  component: questionMatching,
};

export const initializeQuizInterface = () => {
  window.TutorComponentRegistry.registerAll({
    components: [quizTimerMeta, questionOrderingMeta, questionMatchingMeta],
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
