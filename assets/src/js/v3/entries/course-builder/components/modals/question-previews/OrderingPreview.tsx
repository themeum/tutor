import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const OrderingPreview = ({ answers }: { answers: QuizQuestionOption[] }) => (
  <div className="tutor-quiz-question-options">
    {answers.map((answer, index) => (
      <div
        key={answer.answer_id || index}
        className="tutor-quiz-question-option"
        data-option="draggable"
        data-id={answer.answer_id}
      >
        <div data-option-order>{answer.answer_order || index + 1}</div>
        <div data-title>
          {answer.image_url ? <img src={answer.image_url} alt={answer.answer_title} /> : null}
          {answer.answer_title}
        </div>

        <button type="button" data-grab-handle disabled>
          <SVGIcon name="grabHandle" width={24} height={24} />
        </button>
      </div>
    ))}
  </div>
);

export default OrderingPreview;
