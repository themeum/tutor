import {
  closestCorners,
  DndContext,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  type UniqueIdentifier,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import { restrictToWindowEdges } from '@dnd-kit/modifiers';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { useMemo, useState } from 'react';
import { createPortal } from 'react-dom';

import CourseItem from '@BundleBuilderComponents/course-bundle/CourseItem';
import { type Course } from '@BundleBuilderServices/bundle';

import For from '@Controls/For';
import Show from '@Controls/Show';
import { droppableMeasuringStrategy } from '@Utils/dndkit';
import { noop } from '@Utils/util';

interface SelectedCourseListProps {
  courses: Course[];
  onRemove: (index: number) => void;
  onSort: (activeIndex: number, overIndex: number) => void;
}

const SelectedCourseList = ({ courses, onRemove, onSort }: SelectedCourseListProps) => {
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);

  const activeSortItem = useMemo(() => {
    return courses.find((course) => course.id === activeSortId);
  }, [activeSortId, courses]);

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    }),
  );
  return (
    <DndContext
      sensors={sensors}
      collisionDetection={closestCorners}
      measuring={droppableMeasuringStrategy}
      modifiers={[restrictToWindowEdges]}
      onDragStart={(event) => {
        setActiveSortId(event.active.id);
      }}
      onDragEnd={(event) => {
        const { active, over } = event;

        if (!over || active.id === over.id) {
          setActiveSortId(null);
          return;
        }

        const activeIndex = courses.findIndex((course) => course.id === active.id);
        const overIndex = courses.findIndex((course) => course.id === over.id);
        onSort(activeIndex, overIndex);
      }}
    >
      <SortableContext items={courses} strategy={verticalListSortingStrategy}>
        <For each={courses}>
          {(item, index) => (
            <CourseItem key={item.id} course={item} index={index + 1} onRemove={() => onRemove(index)} />
          )}
        </For>
      </SortableContext>
      {createPortal(
        <DragOverlay>
          <Show when={activeSortItem}>
            {(course) => <CourseItem course={course} index={0} onRemove={noop} isOverlay />}
          </Show>
        </DragOverlay>,
        document.body,
      )}
    </DndContext>
  );
};

export default SelectedCourseList;
