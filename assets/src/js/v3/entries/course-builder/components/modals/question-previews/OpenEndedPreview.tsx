import { __ } from '@wordpress/i18n';
import { useState } from 'react';

import { type QuizQuestionType } from '@TutorShared/utils/types';

const OpenEndedPreview = ({ questionType }: { questionType: QuizQuestionType }) => {
  const [answer, setAnswer] = useState('');

  return (
    <div className="tutor-quiz-question-options">
      <div className="tutor-input-field">
        <div className="tutor-input-wrapper">
          <textarea
            className="tutor-input tutor-text-area tutor-input-content-clear tutor-quiz-question-input"
            placeholder={
              questionType === 'short_answer'
                ? __('Type your short answer here', 'tutor')
                : __('Type your answer here', 'tutor')
            }
            rows={5}
            value={answer}
            onChange={(event) => setAnswer(event.target.value)}
          />
        </div>
      </div>
    </div>
  );
};

export default OpenEndedPreview;
