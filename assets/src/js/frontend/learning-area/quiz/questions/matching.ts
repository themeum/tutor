import type { Draggable, Droppable } from '@dnd-kit/dom';
import { __ } from '@wordpress/i18n';

import type { AlpineComponentMeta } from '@Core/ts/types';

const loadDndKit = async () => {
  const dom = await import(/* webpackChunkName: "tutor-dnd-kit" */ '@dnd-kit/dom');

  return {
    DragDropManager: dom.DragDropManager,
    Draggable: dom.Draggable,
    Droppable: dom.Droppable,
    KeyboardSensor: dom.KeyboardSensor,
    PointerSensor: dom.PointerSensor,
  };
};

const QUESTION_MATCHING_CONSTANTS = {
  CLASSES: {
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
    DROP_ZONE_ID: 'dropZoneId',
    DROP_PLACEHOLDER_TEXT: 'dropPlaceholderText',
  },
} as const;

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
    const dropZone = target?.closest(`.${QUESTION_MATCHING_CONSTANTS.CLASSES.DROP_ZONE}`) as HTMLElement | null;
    if (!dropZone) {
      return;
    }

    const droppedOption = dropZone.querySelector(
      `[${QUESTION_MATCHING_CONSTANTS.ATTRS.OPTION}="${QUESTION_MATCHING_CONSTANTS.VALUES.DROPPED}"]`,
    );
    if (droppedOption) {
      droppedOption.remove();
    }

    this._restoreDropPlaceholder(dropZone);

    const dropZoneId = dropZone.dataset[QUESTION_MATCHING_CONSTANTS.DATASET.DROP_ZONE_ID];
    if (dropZoneId && this._matches[dropZoneId]) {
      delete this._matches[dropZoneId];
    }

    const values = this._getValuesFromMatches();
    this._callbacks.onClear?.(values);
  },

  _restoreDropPlaceholder(dropZoneEl: HTMLElement) {
    const existingPlaceholder = dropZoneEl.querySelector(`[${QUESTION_MATCHING_CONSTANTS.ATTRS.DROP_PLACEHOLDER}]`);
    if (existingPlaceholder) {
      return;
    }

    const placeholder = document.createElement('span');
    placeholder.setAttribute(QUESTION_MATCHING_CONSTANTS.ATTRS.DROP_PLACEHOLDER, '');
    placeholder.className = QUESTION_MATCHING_CONSTANTS.CLASSES.TEXT_SUBDUED;
    placeholder.textContent =
      dropZoneEl.dataset[QUESTION_MATCHING_CONSTANTS.DATASET.DROP_PLACEHOLDER_TEXT] || __('Drop here', 'tutor');
    dropZoneEl.prepend(placeholder);
  },

  _animateDropSnap(dropZoneEl: HTMLElement, droppedOptionEl: HTMLElement) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }

    dropZoneEl.animate([{ transform: 'scale(0.985)' }, { transform: 'scale(1.02)' }, { transform: 'scale(1)' }], {
      duration: 220,
      easing: 'cubic-bezier(0.2, 0.9, 0.2, 1)',
    });

    droppedOptionEl.animate(
      [
        { transform: 'scale(0.94)', opacity: 0.86 },
        { transform: 'scale(1.015)', opacity: 1 },
        { transform: 'scale(1)', opacity: 1 },
      ],
      {
        duration: 180,
        easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
      },
    );
  },

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

    const { DragDropManager, Draggable, Droppable, KeyboardSensor, PointerSensor } = await loadDndKit();
    if (!this.initialized) {
      return;
    }

    const manager = new DragDropManager({
      sensors: [PointerSensor, KeyboardSensor],
    });

    const draggableEls = Array.from(
      container.querySelectorAll<HTMLElement>(
        `.${QUESTION_MATCHING_CONSTANTS.CLASSES.QUESTION_OPTION}[${QUESTION_MATCHING_CONSTANTS.ATTRS.OPTION}="${QUESTION_MATCHING_CONSTANTS.VALUES.DRAGGABLE}"]`,
      ),
    );

    draggableEls.forEach((element, idx) => {
      const handle = element.querySelector(`[${QUESTION_MATCHING_CONSTANTS.ATTRS.GRAB_HANDLE}]`) as HTMLElement | null;
      const id = element.dataset[QUESTION_MATCHING_CONSTANTS.DATASET.ID] ?? String(idx);

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
      container.querySelectorAll<HTMLElement>(`.${QUESTION_MATCHING_CONSTANTS.CLASSES.DROP_ZONE}`),
    );

    dropZoneElements.forEach((element, idx) => {
      const id = element.dataset[QUESTION_MATCHING_CONSTANTS.DATASET.ID] ?? String(idx);
      element.dataset[QUESTION_MATCHING_CONSTANTS.DATASET.DROP_ZONE_ID] = id;
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
        sourceElement.setAttribute(
          QUESTION_MATCHING_CONSTANTS.ATTRS.OPTION,
          QUESTION_MATCHING_CONSTANTS.VALUES.DRAGGING,
        );
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
        sourceElement.setAttribute(
          QUESTION_MATCHING_CONSTANTS.ATTRS.OPTION,
          QUESTION_MATCHING_CONSTANTS.VALUES.DRAGGABLE,
        );
      }

      if (targetDropZone) {
        const dropZoneEl = targetDropZone.element;
        const sourceId = operation.source.id;

        if (!dropZoneEl) {
          return;
        }

        const clone = document.createElement('div');
        clone.setAttribute(QUESTION_MATCHING_CONSTANTS.ATTRS.OPTION, QUESTION_MATCHING_CONSTANTS.VALUES.DROPPED);
        clone.setAttribute(QUESTION_MATCHING_CONSTANTS.ATTRS.ID, String(sourceId));
        clone.textContent = sourceElement?.textContent ?? '';

        const placeholder = dropZoneEl?.querySelector(`[${QUESTION_MATCHING_CONSTANTS.ATTRS.DROP_PLACEHOLDER}]`);
        const droppedOption = dropZoneEl?.querySelector(
          `[${QUESTION_MATCHING_CONSTANTS.ATTRS.OPTION}="${QUESTION_MATCHING_CONSTANTS.VALUES.DROPPED}"]`,
        );
        if (placeholder) {
          placeholder.replaceWith(clone);
        } else if (droppedOption) {
          droppedOption.replaceWith(clone);
        }

        this._animateDropSnap(dropZoneEl as HTMLElement, clone);

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
      draggable.element?.setAttribute(
        QUESTION_MATCHING_CONSTANTS.ATTRS.OPTION,
        QUESTION_MATCHING_CONSTANTS.VALUES.DRAGGABLE,
      );
      draggable.element?.classList.remove(QUESTION_MATCHING_CONSTANTS.CLASSES.DROPPED);
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
