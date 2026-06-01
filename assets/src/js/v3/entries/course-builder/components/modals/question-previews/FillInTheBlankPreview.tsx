import { __ } from '@wordpress/i18n';
import { useState } from 'react';

import For from '@TutorShared/controls/For';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const renderFillInBlankText = (
  answer: QuizQuestionOption,
  index: number,
  values: Record<string, string>,
  setValue: (inputId: string, value: string) => void,
) => {
  const parts = (answer.answer_title || '').split('{dash}');

  return parts.flatMap((part, partIndex) => {
    const nodes = [];
    const inputId = `${answer.answer_id || index}-${partIndex}`;

    if (part) {
      nodes.push(<span key={`${answer.answer_id || index}-text-${partIndex}`}>{part}</span>);
    }

    if (partIndex < parts.length - 1) {
      nodes.push(
        <input
          key={`${answer.answer_id || index}-input-${partIndex}`}
          type="text"
          className="tutor-quiz-question-input"
          placeholder={__('Type your answer here', 'tutor')}
          value={values[inputId] ?? ''}
          onChange={(event) => setValue(inputId, event.target.value)}
        />,
      );
    }

    return nodes;
  });
};

const FillInTheBlankPreview = ({ answers }: { answers: QuizQuestionOption[] }) => {
  const [values, setValues] = useState<Record<string, string>>({});
  const setValue = (inputId: string, value: string) => {
    setValues((currentValues) => ({
      ...currentValues,
      [inputId]: value,
    }));
  };

  return (
    <div className="tutor-quiz-question-options">
      <For each={answers}>
        {(answer, index) => (
          <div key={answer.answer_id || index} className="tutor-quiz-question-option">
            {renderFillInBlankText(answer, index, values, setValue)}
          </div>
        )}
      </For>
    </div>
  );
};

export default FillInTheBlankPreview;
