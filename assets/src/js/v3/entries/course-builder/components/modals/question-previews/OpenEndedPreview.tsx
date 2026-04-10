import { __ } from '@wordpress/i18n';
import { type QuizQuestionType } from '@TutorShared/utils/types';

const OpenEndedPreview = ({ questionType }: { questionType: QuizQuestionType }) => (
  <div className="tutor-quiz-question-options">
    <textarea
      className="tutor-form-textarea tutor-quiz-question-input"
      placeholder={
        questionType === 'short_answer'
          ? __('Type your short answer here', 'tutor')
          : __('Type your answer here', 'tutor')
      }
      disabled
      rows={5}
    />
  </div>
);

export default OpenEndedPreview;
