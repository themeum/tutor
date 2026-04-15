import Show from '@TutorShared/controls/Show';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const MultipleChoicePreview = ({
  answers,
  hasMultipleCorrectAnswer,
}: {
  answers: QuizQuestionOption[];
  hasMultipleCorrectAnswer: boolean;
}) => (
  <div className="tutor-quiz-question-options">
    {answers.map((answer, index) => {
      const hasImage = !!answer.image_url;

      return (
        <label key={answer.answer_id || index} className="tutor-quiz-question-option">
          <Show
            when={!hasImage}
            fallback={
              <>
                <img src={answer.image_url} alt={answer.answer_title} />
                <div data-title>{answer.answer_title}</div>
              </>
            }
          >
            <div className="tutor-input-field">
              <div className="tutor-input-wrapper">
                <input
                  type={hasMultipleCorrectAnswer ? 'checkbox' : 'radio'}
                  className={hasMultipleCorrectAnswer ? 'tutor-checkbox' : 'tutor-radio'}
                  disabled
                />
                <label>{answer.answer_title}</label>
              </div>
            </div>
          </Show>
        </label>
      );
    })}
  </div>
);

export default MultipleChoicePreview;
