import { __ } from '@wordpress/i18n';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const renderFillInBlankText = (answer: QuizQuestionOption, index: number) => {
  const parts = (answer.answer_title || '').split('{dash}');

  return parts.flatMap((part, partIndex) => {
    const nodes = [];

    if (part) {
      nodes.push(<span key={`${answer.answer_id || index}-text-${partIndex}`}>{part}</span>);
    }

    if (partIndex < parts.length - 1) {
      nodes.push(
        <input
          key={`${answer.answer_id || index}-input-${partIndex}`}
          type="text"
          className="tutor-quiz-question-input"
          placeholder={__('Type your answer here', 'tutor')}
          disabled
        />,
      );
    }

    return nodes;
  });
};

const FillInTheBlankPreview = ({ answers }: { answers: QuizQuestionOption[] }) => (
  <div className="tutor-quiz-question-options">
    {answers.map((answer, index) => (
      <div key={answer.answer_id || index} className="tutor-quiz-question-option">
        {renderFillInBlankText(answer, index)}
      </div>
    ))}
  </div>
);

export default FillInTheBlankPreview;
