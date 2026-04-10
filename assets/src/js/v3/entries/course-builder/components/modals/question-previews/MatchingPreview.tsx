import { __ } from '@wordpress/i18n';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Show from '@TutorShared/controls/Show';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const MatchingPreview = ({ answers, isImageMatching }: { answers: QuizQuestionOption[]; isImageMatching: boolean }) => {
  const draggableAnswers = [...answers].sort((first, second) => first.answer_order - second.answer_order);

  return (
    <div className="tutor-flex tutor-flex-column tutor-gap-7 tutor-sm-gap-5">
      <div className="tutor-quiz-question-options" data-image-matching={isImageMatching ? '1' : '0'}>
        {answers.map((answer, index) => (
          <div key={answer.answer_id || index} className="tutor-quiz-question-option">
            <Show
              when={isImageMatching && answer.image_url}
              fallback={
                <div data-title>
                  <div className="tutor-quiz-question-option-number">{answer.answer_order || index + 1}</div>
                  {answer.answer_title}
                </div>
              }
            >
              <img src={answer.image_url} alt={answer.answer_title} />
            </Show>
            <div className="tutor-quiz-question-option-drop-zone" data-drop-placeholder-text={__('Drop here', 'tutor')}>
              <span data-drop-placeholder className="tutor-text-subdued">
                {__('Drop here', 'tutor')}
              </span>
            </div>
          </div>
        ))}
      </div>

      <div className="tutor-quiz-question-draggable">
        <div className="tutor-quiz-question-draggable-header">
          <SVGIcon name="drag" width={20} height={20} />
          <span className="tutor-text-small tutor-font-medium">{__('Drag from here', 'tutor')}</span>
        </div>
        <div className="tutor-quiz-question-options">
          {draggableAnswers.map((answer, index) => (
            <div
              key={answer.answer_id || index}
              className="tutor-quiz-question-option"
              data-option="draggable"
              data-id={answer.answer_id}
            >
              <div data-title>
                <Show when={isImageMatching} fallback={answer.answer_two_gap_match}>
                  {answer.answer_title}
                </Show>
              </div>
              <button type="button" data-grab-handle disabled>
                <SVGIcon name="grabHandle" width={24} height={24} />
              </button>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default MatchingPreview;
