import { MeasuringStrategy } from '@dnd-kit/core';
import { type AnimateLayoutChanges, defaultAnimateLayoutChanges } from '@dnd-kit/sortable';

export const animateLayoutChanges: AnimateLayoutChanges = (args) =>
  defaultAnimateLayoutChanges({ ...args, wasDragging: true });

export const droppableMeasuringStrategy = {
  droppable: {
    strategy: MeasuringStrategy.Always,
  },
} as const;
