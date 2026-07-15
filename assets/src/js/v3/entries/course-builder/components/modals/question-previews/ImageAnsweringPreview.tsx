import { useState } from 'react';
import { __ } from '@wordpress/i18n';

import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const ImageAnsweringPreview = ({ answers }: { answers: QuizQuestionOption[] }) => {
  const [values, setValues] = useState<Record<string, string>>({});

  return (
    <div className="tutor-quiz-question-options">
      <For each={answers}>
        {(answer, index) => {
          const answerId = String(answer.answer_id || index);
          return (
            <div key={answer.answer_id || index} className="tutor-quiz-question-option">
              <Show when={answer.image_url}>
                <img src={answer.image_url} alt={answer.answer_title} />
              </Show>
              <div className="tutor-input-field">
                <div className="tutor-input-wrapper">
                  <input
                    type="text"
                    className="tutor-input tutor-input-content-clear tutor-quiz-question-input"
                    placeholder={__('Write your answer here', 'tutor')}
                    value={values[answerId] ?? ''}
                    onChange={(event) =>
                      setValues((currentValues) => ({
                        ...currentValues,
                        [answerId]: event.target.value,
                      }))
                    }
                  />
                </div>
              </div>
            </div>
          );
        }}
      </For>
    </div>
  );
};

export default ImageAnsweringPreview;
