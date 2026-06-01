import { DragDropManager, Draggable, Droppable, KeyboardSensor, PointerSensor } from '@dnd-kit/dom';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useRef, useState } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Show from '@TutorShared/controls/Show';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const getAnswerId = (answer: QuizQuestionOption, index: number) => String(answer.answer_id || index);

const MatchingPreview = ({ answers, isImageMatching }: { answers: QuizQuestionOption[]; isImageMatching: boolean }) => {
  const wrapperRef = useRef<HTMLDivElement>(null);
  const [matches, setMatches] = useState<Record<string, string>>({});
  const draggableAnswers = [...answers].sort((first, second) => first.answer_order - second.answer_order);
  const answersById = useMemo(
    () =>
      answers.reduce<Record<string, QuizQuestionOption>>((map, answer, index) => {
        map[getAnswerId(answer, index)] = answer;
        return map;
      }, {}),
    [answers],
  );

  useEffect(() => {
    setMatches({});
  }, [answers, isImageMatching]);

  useEffect(() => {
    const wrapper = wrapperRef.current;

    if (!wrapper) {
      return;
    }

    const manager = new DragDropManager({
      sensors: [PointerSensor, KeyboardSensor],
    });

    const draggables = Array.from(
      wrapper.querySelectorAll<HTMLElement>('.tutor-quiz-question-option[data-option="draggable"]'),
    ).map((element, index) => {
      const handle = element.querySelector<HTMLElement>('[data-grab-handle]');

      return new Draggable(
        {
          id: element.dataset.id ?? String(index),
          element,
          handle: handle ?? undefined,
          feedback: 'clone',
        },
        manager,
      );
    });

    const droppables = Array.from(wrapper.querySelectorAll<HTMLElement>('.tutor-quiz-question-option-drop-zone')).map(
      (element, index) =>
        new Droppable(
          {
            id: element.dataset.dropZoneId ?? String(index),
            element,
          },
          manager,
        ),
    );

    manager.monitor.addEventListener('dragstart', (event) => {
      event.operation.source?.element?.setAttribute('data-option', 'dragging');
    });

    manager.monitor.addEventListener('dragend', (event) => {
      event.operation.source?.element?.setAttribute('data-option', 'draggable');

      const sourceId = event.operation.source?.id;
      const targetId = event.operation.target?.id;

      if (!sourceId || !targetId) {
        return;
      }

      setMatches((currentMatches) => ({
        ...currentMatches,
        [String(targetId)]: String(sourceId),
      }));
    });

    return () => {
      draggables.forEach((draggable) => draggable.destroy());
      droppables.forEach((droppable) => droppable.destroy());
    };
  }, [answers]);

  return (
    <div ref={wrapperRef} className="tutor-flex tutor-flex-column tutor-gap-7 tutor-sm-gap-5">
      <div className="tutor-quiz-question-options" data-image-matching={isImageMatching ? '1' : '0'}>
        {answers.map((answer, index) => {
          const dropZoneId = getAnswerId(answer, index);
          const matchedAnswer = matches[dropZoneId] ? answersById[matches[dropZoneId]] : null;
          const matchedTitle = matchedAnswer
            ? isImageMatching
              ? matchedAnswer.answer_title
              : matchedAnswer.answer_two_gap_match
            : '';

          return (
            <div key={answer.answer_id || index} className="tutor-quiz-question-option">
              <Show
                when={isImageMatching && answer.image_url}
                fallback={
                  <div data-title>
                    <div className="tutor-quiz-question-option-number">{answer.answer_order || index + 1}</div>
                    {answer.answer_title}
                  </div>
                }
              >
                <img src={answer.image_url} alt={answer.answer_title} />
              </Show>
              <div
                className="tutor-quiz-question-option-drop-zone"
                data-drop-placeholder-text={__('Drop here', 'tutor')}
                data-drop-zone-id={dropZoneId}
              >
                <Show
                  when={matchedAnswer}
                  fallback={
                    <span data-drop-placeholder className="tutor-text-subdued">
                      {__('Drop here', 'tutor')}
                    </span>
                  }
                >
                  <div data-option="dropped" data-id={matches[dropZoneId]}>
                    {matchedTitle}
                  </div>
                  <button
                    type="button"
                    className="tutor-preview-drop-clear"
                    aria-label={__('Clear matched answer', 'tutor')}
                    onClick={() =>
                      setMatches((currentMatches) => {
                        const nextMatches = { ...currentMatches };
                        delete nextMatches[dropZoneId];
                        return nextMatches;
                      })
                    }
                  >
                    <SVGIcon name="cross" width={16} height={16} />
                  </button>
                </Show>
              </div>
            </div>
          );
        })}
      </div>

      <div className="tutor-quiz-question-draggable">
        <div className="tutor-quiz-question-draggable-header">
          <SVGIcon name="drag" width={20} height={20} />
          <span className="tutor-text-small tutor-font-medium">{__('Drag from here', 'tutor')}</span>
        </div>
        <div className="tutor-quiz-question-options">
          {draggableAnswers.map((answer, index) => (
            <div
              key={answer.answer_id || index}
              className="tutor-quiz-question-option"
              data-option="draggable"
              data-id={answer.answer_id}
            >
              <div data-title>
                <Show when={isImageMatching} fallback={answer.answer_two_gap_match}>
                  {answer.answer_title}
                </Show>
              </div>
              <button type="button" data-grab-handle aria-label={answer.answer_title}>
                <SVGIcon name="grabHandle" width={24} height={24} />
              </button>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default MatchingPreview;
