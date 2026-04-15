import { __ } from '@wordpress/i18n';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const TrueFalsePreview = ({ answers }: { answers: QuizQuestionOption[] }) => (
  <div className="tutor-quiz-question-options">
    {answers.map((answer) => (
      <label key={answer.answer_id} className="tutor-quiz-question-option">
        <SVGIcon name={__('True', 'tutor') === answer.answer_title ? 'check2' : 'cross'} width={20} height={20} />
        {answer.answer_title}
      </label>
    ))}
  </div>
);

export default TrueFalsePreview;
