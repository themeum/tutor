import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { tutorConfig } from '@TutorShared/config/config';
import { colorTokens } from '@TutorShared/config/styles';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const DRAW_IMAGE_SCRIPT_ATTR = 'data-tutor-draw-image-preview-script';

/** Matches {@link FormDrawImage} Clear button icon (SVGIcon + brand color like quiz builder). */
const clearButtonIcon = css`
  color: ${colorTokens.text.brand};
`;

/**
 * Loads `tutor-pro/assets/js/draw-image-question.js` in the preview iframe (same pattern as
 * {@link ScalePreview}, {@link CoordinatesPreview} + Pro `learning-area/quiz/questions/draw-image.php`).
 */
const DrawImagePreview = ({ answers }: { answers: QuizQuestionOption[] }) => {
  const wrapperRef = useRef<HTMLDivElement>(null);
  const imageUrl = answers[0]?.image_url;
  const qId = answers[0]?.answer_id ?? 'preview';

  useEffect(() => {
    const wrapper = wrapperRef.current;
    if (!wrapper) {
      return;
    }

    const doc = wrapper.ownerDocument;
    if (!doc || doc === document) {
      return;
    }

    if (doc.head.querySelector(`[${DRAW_IMAGE_SCRIPT_ATTR}]`)) {
      const reinit = doc.createElement('script');
      reinit.textContent = 'if(window._tutorDrawImageInitAll){window._tutorDrawImageInitAll();}';
      doc.head.appendChild(reinit);
      return;
    }

    const siteUrl = tutorConfig.site_url.replace(/\/$/, '');
    const script = doc.createElement('script');
    script.setAttribute(DRAW_IMAGE_SCRIPT_ATTR, '1');
    script.textContent = `
			(function(){
				var scriptEl = document.createElement('script');
				scriptEl.src = '${siteUrl}/wp-content/plugins/tutor-pro/assets/js/draw-image-question.js';
				scriptEl.onload = function(){
					if(typeof window._tutorDrawImageInitAll === 'function'){
						window._tutorDrawImageInitAll();
					}
				};
				document.head.appendChild(scriptEl);
			})();
		`;
    doc.head.appendChild(script);
  }, [imageUrl, qId]);

  if (!imageUrl) {
    return (
      <div className="tutor-quiz-question-options">
        <p className="tutor-fs-7 tutor-color-secondary">
          {__('No background image configured for this Mark in the Image question.', 'tutor')}
        </p>
      </div>
    );
  }

  return (
    <div
      ref={wrapperRef}
      id={`tutor-draw-image-question-${qId}`}
      className="tutor-quiz-question-options tutor-draw-image-question"
      data-question-type="draw_image"
    >
      <div className="tutor-draw-image-actions tutor-mb-12">
        <button
          type="button"
          className="tutor-draw-image-clear-button tutor-hidden"
          aria-label={__('Clear drawing', 'tutor')}
        >
          <SVGIcon name="eraser" style={clearButtonIcon} width={18} height={18} />
          {__('Clear', 'tutor')}
        </button>
      </div>
      <div className="tutor-draw-image-wrapper">
        <img id={`tutor-draw-image-bg-${qId}`} src={imageUrl} alt={__('Draw on image question', 'tutor')} />
        <canvas id={`tutor-draw-image-canvas-${qId}`} className="tutor-draw-image-canvas" />
      </div>
      <input type="hidden" id={`tutor-draw-image-mask-${qId}`} name="preview[answers][mask]" defaultValue="" readOnly />
    </div>
  );
};

export default DrawImagePreview;
