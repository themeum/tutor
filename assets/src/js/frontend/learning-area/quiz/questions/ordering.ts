import type { Sortable } from '@dnd-kit/dom/sortable';

import type { AlpineComponentMeta } from '@Core/ts/types';

const loadDndKit = async () => {
  const [dom, sortable] = await Promise.all([
    import(/* webpackChunkName: "tutor-dnd-kit" */ '@dnd-kit/dom'),
    import(/* webpackChunkName: "tutor-dnd-kit" */ '@dnd-kit/dom/sortable'),
  ]);

  return {
    DragDropManager: dom.DragDropManager,
    KeyboardSensor: dom.KeyboardSensor,
    PointerSensor: dom.PointerSensor,
    Sortable: sortable.Sortable,
  };
};

const QUESTION_ORDERING_CONSTANTS = {
  CLASSES: {
    QUESTION_OPTION: 'tutor-quiz-question-option',
  },
  ATTRS: {
    OPTION: 'data-option',
    ID: 'data-id',
  },
  VALUES: {
    DRAGGABLE: 'draggable',
    DRAGGING: 'dragging',
  },
  DATASET: {
    ID: 'id',
  },
} as const;

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

  async setupDrag() {
    const container = this.$el;
    if (!container) {
      return;
    }

    const { DragDropManager, KeyboardSensor, PointerSensor, Sortable } = await loadDndKit();
    if (!this.initialized) {
      return;
    }

    const manager = new DragDropManager({
      sensors: [PointerSensor, KeyboardSensor],
    });

    const items = Array.from(
      container.querySelectorAll<HTMLElement>(
        `.${QUESTION_ORDERING_CONSTANTS.CLASSES.QUESTION_OPTION}[${QUESTION_ORDERING_CONSTANTS.ATTRS.OPTION}="${QUESTION_ORDERING_CONSTANTS.VALUES.DRAGGABLE}"]`,
      ),
    );

    this._sortables = [];

    items.forEach((element, idx) => {
      const id = element.dataset[QUESTION_ORDERING_CONSTANTS.DATASET.ID] ?? String(idx);

      const sortable = new Sortable(
        {
          id,
          index: idx,
          element: element,
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

      sourceElement.setAttribute(QUESTION_ORDERING_CONSTANTS.ATTRS.OPTION, QUESTION_ORDERING_CONSTANTS.VALUES.DRAGGING);
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

      sourceElement.setAttribute(
        QUESTION_ORDERING_CONSTANTS.ATTRS.OPTION,
        QUESTION_ORDERING_CONSTANTS.VALUES.DRAGGABLE,
      );
      if (this.$el) {
        this.$el.dataset.hasInteraction = '1';
      }
      requestAnimationFrame(() => {
        this._callbacks.onOrder?.(this.getOrder());
      });
    });
  },

  getOrder(): string[] {
    const container = this.$el;
    if (!container) {
      return [];
    }

    const options = Array.from(
      container.querySelectorAll<HTMLElement>(`.${QUESTION_ORDERING_CONSTANTS.CLASSES.QUESTION_OPTION}`),
    );

    return [
      ...new Set(
        options
          .map((option) => option.dataset[QUESTION_ORDERING_CONSTANTS.DATASET.ID])
          .filter((id): id is string => typeof id === 'string'),
      ),
    ];
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
