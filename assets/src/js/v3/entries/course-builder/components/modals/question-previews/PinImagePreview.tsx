import { __ } from '@wordpress/i18n';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const PinImagePreview = ({ answers }: { answers: QuizQuestionOption[] }) => {
  const imageUrl = answers[0]?.image_url;
  if (!imageUrl) {
    return (
      <div className="tutor-quiz-question-options">
        <p className="tutor-fs-7 tutor-color-secondary">
          {__('No background image configured for this Pin question.', 'tutor')}
        </p>
      </div>
    );
  }
  return (
    <div className="tutor-quiz-question-options tutor-pin-image-question">
      <div className="tutor-pin-image-wrapper">
        <img src={imageUrl} alt={__('Pin on image question', 'tutor')} />
        <span className="tutor-pin-image-marker" aria-hidden="true" />
      </div>
    </div>
  );
};

export default PinImagePreview;
