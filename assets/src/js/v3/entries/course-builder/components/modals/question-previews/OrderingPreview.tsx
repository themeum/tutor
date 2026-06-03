import { DragDropManager, KeyboardSensor, PointerSensor } from '@dnd-kit/dom';
import { Sortable } from '@dnd-kit/dom/sortable';
import { useEffect, useMemo, useRef, useState } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import For from '@TutorShared/controls/For';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const getAnswerId = (answer: QuizQuestionOption, index: number) => String(answer.answer_id || index);

const OrderingPreview = ({ answers }: { answers: QuizQuestionOption[] }) => {
  const containerRef = useRef<HTMLDivElement>(null);
  const [orderedAnswerIds, setOrderedAnswerIds] = useState(() => answers.map(getAnswerId));
  const answersById = useMemo(
    () =>
      answers.reduce<Record<string, QuizQuestionOption>>((map, answer, index) => {
        map[getAnswerId(answer, index)] = answer;
        return map;
      }, {}),
    [answers],
  );

  useEffect(() => {
    setOrderedAnswerIds(answers.map(getAnswerId));
  }, [answers]);

  useEffect(() => {
    const container = containerRef.current;

    if (!container) {
      return;
    }

    const manager = new DragDropManager({
      sensors: [PointerSensor, KeyboardSensor],
    });

    const sortables = Array.from(
      container.querySelectorAll<HTMLElement>('.tutor-quiz-question-option[data-option="draggable"]'),
    ).map((element, index) => {
      const handle = element.querySelector<HTMLElement>('[data-grab-handle]');

      return new Sortable(
        {
          id: element.dataset.id ?? String(index),
          index,
          element,
          handle: handle ?? undefined,
        },
        manager,
      );
    });

    manager.monitor.addEventListener('dragstart', (event) => {
      event.operation.source?.element?.setAttribute('data-option', 'dragging');
    });

    manager.monitor.addEventListener('dragend', (event) => {
      event.operation.source?.element?.setAttribute('data-option', 'draggable');

      requestAnimationFrame(() => {
        const nextOrder = Array.from(
          container.querySelectorAll<HTMLElement>('.tutor-quiz-question-option[data-id]'),
        ).flatMap((option) => (option.dataset.id ? [option.dataset.id] : []));

        setOrderedAnswerIds([...new Set(nextOrder)]);
      });
    });

    return () => {
      sortables.forEach((sortable) => sortable.destroy());
    };
  }, [orderedAnswerIds]);

  return (
    <div ref={containerRef} className="tutor-quiz-question-options">
      <For each={orderedAnswerIds}>
        {(answerId, index) => {
          const answer = answersById[answerId];
          if (!answer) {
            return null;
          }

          return (
            <div key={answerId} className="tutor-quiz-question-option" data-option="draggable" data-id={answerId}>
              <div data-option-order>{index + 1}</div>
              <div data-title>
                {answer.image_url ? <img src={answer.image_url} alt={answer.answer_title} /> : null}
                <div data-question-title>{answer.answer_title}</div>
              </div>
              <button type="button" data-grab-handle aria-label={answer.answer_title}>
                <SVGIcon name="grabHandle" width={24} height={24} />
              </button>
            </div>
          );
        }}
      </For>
    </div>
  );
};

export default OrderingPreview;
