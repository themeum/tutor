import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from 'react';

import { tutorConfig } from '@TutorShared/config/config';
import { type QuizQuestionOption } from '@TutorShared/utils/types';

const PIN_IMAGE_SCRIPT_ATTR = 'data-tutor-pin-image-preview-script';

/**
 * Loads `tutor-pro/assets/js/pin-image-question.js` in the preview iframe (same pattern as
 * {@link ScalePreview}, {@link CoordinatesPreview} + Pro `learning-area/quiz/questions/pin-image.php`).
 */
const PinImagePreview = ({ answers }: { answers: QuizQuestionOption[] }) => {
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

    if (doc.head.querySelector(`[${PIN_IMAGE_SCRIPT_ATTR}]`)) {
      const reinit = doc.createElement('script');
      reinit.textContent = 'if(window._tutorPinImageInitAll){window._tutorPinImageInitAll();}';
      doc.head.appendChild(reinit);
      return;
    }

    const siteUrl = tutorConfig.site_url.replace(/\/$/, '');
    const script = doc.createElement('script');
    script.setAttribute(PIN_IMAGE_SCRIPT_ATTR, '1');
    script.textContent = `
			(function(){
				var scriptEl = document.createElement('script');
				scriptEl.src = '${siteUrl}/wp-content/plugins/tutor-pro/assets/js/pin-image-question.js';
				scriptEl.onload = function(){
					if(typeof window._tutorPinImageInitAll === 'function'){
						window._tutorPinImageInitAll();
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
          {__('No background image configured for this Pin question.', 'tutor')}
        </p>
      </div>
    );
  }

  return (
    <div
      ref={wrapperRef}
      id={`tutor-pin-image-question-${qId}`}
      className="tutor-quiz-question-options tutor-pin-image-question"
      data-question-type="pin_image"
    >
      <div className="tutor-pin-image-wrapper">
        <img id={`tutor-pin-image-bg-${qId}`} src={imageUrl} alt={__('Pin on image question', 'tutor')} />
        <span className="tutor-pin-image-marker" aria-hidden="true" />
      </div>
      <input type="hidden" id={`tutor-pin-image-x-${qId}`} name="preview[answers][pin][x]" defaultValue="" readOnly />
      <input type="hidden" id={`tutor-pin-image-y-${qId}`} name="preview[answers][pin][y]" defaultValue="" readOnly />
    </div>
  );
};

export default PinImagePreview;
