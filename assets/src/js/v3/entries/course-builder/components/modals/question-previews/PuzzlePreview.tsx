import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from 'react';

import { tutorConfig } from '@TutorShared/config/config';
import { type ID, type QuizQuestionOption } from '@TutorShared/utils/types';

const PUZZLE_SCRIPT_ATTR = 'data-tutor-puzzle-preview-script';

const clampGridSize = (raw: number | string | undefined) => {
  const n = typeof raw === 'number' ? raw : Number(raw);
  const base = Number.isFinite(n) && n > 0 ? Math.round(n) : 4;
  return Math.max(2, Math.min(7, base));
};

interface PuzzlePreviewProps {
  answers: QuizQuestionOption[];
  gridSize?: number | string;
  /** Used for hidden input id parity with `puzzle.php` (`tutor-puzzle-state-{id}`). */
  questionId: ID;
}

/**
 * Course Builder puzzle preview — loads Tutor Pro `puzzle-question.js` in the preview iframe
 * (same pattern as {@link DrawImagePreview}, {@link PinImagePreview}, {@link ScalePreview}).
 */
const PuzzlePreview = ({ answers, gridSize: gridSizeProp, questionId }: PuzzlePreviewProps) => {
  const answer = answers[0];
  const imageUrl = answer?.image_url || answer?.answer_two_gap_match || '';
  const gridSize = clampGridSize(gridSizeProp);
  const wrapperRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!imageUrl) {
      return;
    }

    const wrapper = wrapperRef.current;
    if (!wrapper) {
      return;
    }

    const doc = wrapper.ownerDocument;
    if (!doc || doc === document) {
      return;
    }

    if (doc.head.querySelector(`[${PUZZLE_SCRIPT_ATTR}]`)) {
      const reinit = doc.createElement('script');
      reinit.textContent = 'if(window._tutorPuzzlePreviewInitAll){window._tutorPuzzlePreviewInitAll();}';
      doc.head.appendChild(reinit);
      return;
    }

    const siteUrl = tutorConfig.site_url.replace(/\/$/, '');
    const script = doc.createElement('script');
    script.setAttribute(PUZZLE_SCRIPT_ATTR, '1');
    script.textContent = `
			(function(){
				var scriptEl = document.createElement('script');
				scriptEl.src = '${siteUrl}/wp-content/plugins/tutor-pro/assets/js/puzzle-question.js';
				scriptEl.onload = function(){
					if(typeof window._tutorPuzzlePreviewInitAll === 'function'){
						window._tutorPuzzlePreviewInitAll();
					}
				};
				document.head.appendChild(scriptEl);
			})();
		`;
    doc.head.appendChild(script);
  }, [imageUrl, gridSize, questionId]);

  if (!imageUrl) {
    return (
      <div className="quiz-question-ans-choice-area tutor-mt-40 tutor-puzzle-question question-type-puzzle">
        <p className="tutor-fs-7 tutor-color-secondary">
          {__('No source image configured for this Puzzle question.', 'tutor')}
        </p>
      </div>
    );
  }

  if (!tutorConfig.tutor_pro_url) {
    return (
      <div className="quiz-question-ans-choice-area tutor-mt-40 tutor-puzzle-question question-type-puzzle">
        <p className="tutor-fs-7 tutor-color-secondary">{__('Puzzle preview requires Tutor LMS Pro.', 'tutor')}</p>
      </div>
    );
  }

  const hiddenInputId = `tutor-puzzle-state-preview-${questionId}`;

  return (
    <div
      ref={wrapperRef}
      className="quiz-question-ans-choice-area tutor-mt-40 tutor-puzzle-question question-type-puzzle"
      data-tutor-puzzle-defer-init="true"
      data-question-type="puzzle"
      data-question-id={String(questionId)}
      data-grid-size={String(gridSize)}
      data-image-url={imageUrl}
      data-puzzle-token=""
    >
      <div className="tutor-puzzle-playground">
        <img
          className="tutor-puzzle-reference-image"
          src={imageUrl}
          alt={__('Puzzle reference image', 'tutor')}
          style={{ opacity: 0.3 }}
        />
        <div className="tutor-puzzle-slots" />
      </div>
      <div className="tutor-puzzle-scatter" />
      <input type="hidden" id={hiddenInputId} name="" value="" />
    </div>
  );
};

export default PuzzlePreview;
