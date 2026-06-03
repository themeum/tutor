import { useState } from 'react';

import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const MultipleChoicePreview = ({
  answers,
  hasMultipleCorrectAnswer,
}: {
  answers: QuizQuestionOption[];
  hasMultipleCorrectAnswer: boolean;
}) => {
  const [selectedAnswers, setSelectedAnswers] = useState<string[]>([]);

  const toggleAnswer = (answerId: string) => {
    setSelectedAnswers((currentAnswers) => {
      if (!hasMultipleCorrectAnswer) {
        return [answerId];
      }

      return currentAnswers.includes(answerId)
        ? currentAnswers.filter((currentAnswer) => currentAnswer !== answerId)
        : [...currentAnswers, answerId];
    });
  };

  return (
    <div className="tutor-quiz-question-options">
      <For each={answers}>
        {(answer, index) => {
          const hasImage = !!answer.image_url;
          const answerId = String(answer.answer_id || index);
          const inputId = `tutor-question-preview-answer-${answerId}`;
          const isChecked = selectedAnswers.includes(answerId);
          return (
            <label
              key={answer.answer_id || index}
              className="tutor-quiz-question-option"
              data-preview-selected={isChecked ? 'true' : undefined}
            >
              <Show
                when={!hasImage}
                fallback={
                  <>
                    <input
                      type={hasMultipleCorrectAnswer ? 'checkbox' : 'radio'}
                      className="tutor-hidden"
                      name="tutor-question-preview-answer"
                      value={answerId}
                      checked={isChecked}
                      onChange={() => toggleAnswer(answerId)}
                    />
                    <img src={answer.image_url} alt={answer.answer_title} />
                    <div data-title>{answer.answer_title}</div>
                  </>
                }
              >
                <div className="tutor-input-field">
                  <div className="tutor-input-wrapper">
                    <input
                      id={inputId}
                      type={hasMultipleCorrectAnswer ? 'checkbox' : 'radio'}
                      className={hasMultipleCorrectAnswer ? 'tutor-checkbox' : 'tutor-radio'}
                      name="tutor-question-preview-answer"
                      value={answerId}
                      checked={isChecked}
                      onChange={() => toggleAnswer(answerId)}
                    />
                    <label htmlFor={inputId}>{answer.answer_title}</label>
                  </div>
                </div>
              </Show>
            </label>
          );
        }}
      </For>
    </div>
  );
};

export default MultipleChoicePreview;
