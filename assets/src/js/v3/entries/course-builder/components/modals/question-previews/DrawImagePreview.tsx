import { type QuizQuestionOption } from '@TutorShared/utils/types';
import { __ } from '@wordpress/i18n';

const DrawImagePreview = ({ answers }: { answers: QuizQuestionOption[] }) => {
  const imageUrl = answers[0]?.image_url;
  if (!imageUrl) {
    return (
      <div className="tutor-quiz-question-options">
        <p className="tutor-fs-7 tutor-color-secondary">
          {__('No background image configured for this Mark in the image question.', 'tutor')}
        </p>
      </div>
    );
  }
  return (
    <div className="tutor-quiz-question-options tutor-draw-image-question">
      <div className="tutor-draw-image-wrapper">
        <img src={imageUrl} alt={__('Draw on image question', 'tutor')} />
        <canvas className="tutor-draw-image-canvas" />
      </div>
    </div>
  );
};

export default DrawImagePreview;
