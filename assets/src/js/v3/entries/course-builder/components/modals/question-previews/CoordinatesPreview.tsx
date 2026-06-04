import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { tutorConfig } from '@TutorShared/config/config';
import { colorTokens } from '@TutorShared/config/styles';

const COORDINATES_SCRIPT_ATTR = 'data-tutor-coordinates-preview-script';

const resolveAxisRange = (raw?: number) => (raw === 20 ? 20 : 10);

const tutorIconsBase = `${String(tutorConfig.tutor_url || '').replace(/\/$/, '')}/assets/icons`;

const markerUrl = (name: 'graph-marker-hover' | 'graph-marker-selected' | 'graph-marker-wrong'): string =>
  `${tutorIconsBase}/${name}.svg`;

/** Matches Pro `coordinates.php` eraser + {@link DrawImagePreview} Clear (SVGIcon + brand). */
const clearButtonIcon = css`
  color: ${colorTokens.text.brand};
`;

interface CoordinatesPreviewProps {
  axisRange?: number;
}

/**
 * Learning-area parity: loads `tutor-pro/assets/js/coordinates-question.js` in the preview iframe
 * (same pattern as {@link ScalePreview} + Pro template `learning-area/quiz/questions/coordinates.php`).
 */
const CoordinatesPreview = ({ axisRange: axisRangeProp }: CoordinatesPreviewProps) => {
  const wrapperRef = useRef<HTMLDivElement>(null);
  const axisRange = resolveAxisRange(axisRangeProp);
  const qId = 'preview';
  const inputId = `tutor-coordinates-points-${qId}`;
  const canvasId = `tutor-coordinates-canvas-${qId}`;
  const instructionId = `tutor-coordinates-instruction-${qId}`;
  const hoverDisplayId = `tutor-coordinates-hover-${qId}`;

  useEffect(() => {
    const wrapper = wrapperRef.current;
    if (!wrapper) {
      return;
    }

    const doc = wrapper.ownerDocument;
    if (!doc || doc === document) {
      return;
    }

    if (doc.head.querySelector(`[${COORDINATES_SCRIPT_ATTR}]`)) {
      wrapper.removeAttribute('data-tutor-coordinates-init');
      const reinit = doc.createElement('script');
      reinit.textContent = 'if(window._tutorCoordinatesInitAll){window._tutorCoordinatesInitAll();}';
      doc.head.appendChild(reinit);
      return;
    }

    const siteUrl = tutorConfig.site_url.replace(/\/$/, '');
    const script = doc.createElement('script');
    script.setAttribute(COORDINATES_SCRIPT_ATTR, '1');
    script.textContent = `
      (function(){
        var scriptEl = document.createElement('script');
        scriptEl.src = '${siteUrl}/wp-content/plugins/tutor-pro/assets/js/coordinates-question.js';
        scriptEl.onload = function(){
          if(typeof window._tutorCoordinatesInitAll === 'function'){
            window._tutorCoordinatesInitAll();
          }
        };
        document.head.appendChild(scriptEl);
      })();
    `;
    doc.head.appendChild(script);
  }, []);

  return (
    <div
      ref={wrapperRef}
      id={`tutor-coordinates-question-${qId}`}
      className="tutor-quiz-question-options tutor-coordinates-question question-type-coordinates"
      data-question-type="coordinates"
      data-question-id={qId}
      data-axis-range={String(axisRange)}
      data-marker-hover={markerUrl('graph-marker-hover')}
      data-marker-selected={markerUrl('graph-marker-selected')}
      data-marker-wrong={markerUrl('graph-marker-wrong')}
    >
      <div className="tutor-coordinates-actions">
        <button
          type="button"
          className="tutor-coordinates-clear-prev tutor-coordinates-clear-button tutor-hidden"
          aria-label={__('Clear last point', 'tutor')}
        >
          <SVGIcon name="eraser" style={clearButtonIcon} width={18} height={18} />
          {__('Clear', 'tutor')}
        </button>
      </div>
      <div className="tutor-coordinates-grid-container">
        <canvas
          id={canvasId}
          className="tutor-coordinates-canvas"
          width={420}
          height={420}
          tabIndex={0}
          role="application"
          aria-describedby={`${instructionId} ${hoverDisplayId}`}
          aria-label={__('Coordinate grid: click or use arrow keys and Enter to select grid points.', 'tutor')}
        />
      </div>
      <p id={instructionId} className="tutor-quiz-a11y-sr-only">
        {__(
          'Use arrow keys to move the active grid point, Enter to add it, and Backspace or Delete to remove the last point.',
          'tutor',
        )}
      </p>
      <p
        id={hoverDisplayId}
        className="tutor-coordinates-hover-display tutor-fs-7 tutor-color-secondary tutor-mb-12"
        aria-live="polite"
      />
      <input type="hidden" id={inputId} name="preview[answers][coordinates][points]" defaultValue="" />
    </div>
  );
};

export default CoordinatesPreview;
