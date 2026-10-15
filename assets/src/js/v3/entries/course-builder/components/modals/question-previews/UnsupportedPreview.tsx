import { __ } from '@wordpress/i18n';

const UnsupportedPreview = () => (
  <div className="tutor-quiz-question-options">
    <div className="tutor-quiz-question-option">
      {__('Preview is not available for this question type yet.', 'tutor')}
    </div>
  </div>
);

export default UnsupportedPreview;
