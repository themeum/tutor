import { __ } from '@wordpress/i18n';
import { useState } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import For from '@TutorShared/controls/For';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const TrueFalsePreview = ({ answers }: { answers: QuizQuestionOption[] }) => {
  const [selectedAnswer, setSelectedAnswer] = useState('');

  const handleKeyDown = (e: React.KeyboardEvent, value: string) => {
    if (e.key === ' ' || e.key === 'Enter') {
      e.preventDefault();
      setSelectedAnswer(value);
    }
  };

  return (
    <div className="tutor-quiz-question-options">
      <For each={answers}>
        {(answer, index) => {
          const answerId = String(answer.answer_id || index);
          return (
            <label
              key={answer.answer_id || index}
              className="tutor-quiz-question-option"
              data-preview-selected={selectedAnswer === answerId ? 'true' : undefined}
              tabIndex={0}
              onKeyDown={(e) => handleKeyDown(e, answerId)}
            >
              <input
                className="tutor-hidden"
                type="radio"
                name="tutor-question-preview-true-false"
                value={answerId}
                checked={selectedAnswer === answerId}
                onChange={() => setSelectedAnswer(answerId)}
              />
              <SVGIcon name={__('True', 'tutor') === answer.answer_title ? 'check2' : 'cross'} width={20} height={20} />
              {answer.answer_title}
            </label>
          );
        }}
      </For>
    </div>
  );
};

export default TrueFalsePreview;
