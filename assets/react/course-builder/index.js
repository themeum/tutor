import './assignment';
import './attachment';
import './content-drip';
import './instructor-multi';
import './lesson';
import './quiz';
import './topic';
import './video-picker';

/**
 * Re init required
 * Modal Loaded...
 */
const load_select2 = function() {
	if (jQuery().select2) {
		jQuery('.select2_multiselect').select2({
			dropdownCssClass: 'increasezindex',
		});
	}
};
window.addEventListener('DOMContentLoaded', load_select2);
window.addEventListener(_tutorobject.content_change_event, load_select2);
window.addEventListener(_tutorobject.content_change_event, () => console.log(_tutorobject.content_change_event));

/**
 * Get the remaining length of input limit
 *
 * @return {Number}
 */

function getRemainingLength(maxLength = 255, inputElement) {
	return maxLength - (((inputElement || {}).value || {}).length || 0);
}

/**
 * Update the course title input tooltip value in 'keyup'
 * and set the data initially
 */
const maxLength = 255;
const courseCreateTitle = document.getElementById('tutor-course-create-title');
const courseTitleTooltip = courseCreateTitle?.previousElementSibling;
const courseCreateTitleTooptip = document.querySelector('#tutor-course-create-title-tooltip-wrapper .tooltip-txt');

if (courseTitleTooltip) {
	courseTitleTooltip.innerHTML = getRemainingLength(maxLength, courseCreateTitle);
}

if(courseCreateTitle && courseCreateTitleTooptip) {

	document.addEventListener('click', (e) => { 
		if (e.target === courseCreateTitle) {
			if(courseCreateTitle === document.activeElement) {
				courseCreateTitleTooptip.style.opacity = '1';
				courseCreateTitleTooptip.style.visibility = 'visible';
			} 
		} else {
			courseCreateTitleTooptip.style.opacity = '0';
			courseCreateTitleTooptip.style.visibility = 'hidden';
		}
	})
	
	courseCreateTitle.addEventListener('keyup', (e) => {
		const remainingLength = getRemainingLength(maxLength, courseCreateTitle);
		courseTitleTooltip.innerHTML = remainingLength;
	});
}