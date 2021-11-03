import './tutorModal';
import './tutorThumbnailPreview';
import './tutorPopupMenu';
import './tutorOffcanvas';
import './tutorNotificationTab';
import './tutorDefaultTab';
import './tutorPasswordStrengthChecker';

/**
 * Table td/tr toggle
 */
document.addEventListener('click', function(e) {
	const attr = 'data-td-target';
	const dataTdTarget = e.target.dataset.tdTarget;
	if (dataTdTarget) {
		e.target.closest('td').classList.toggle('is-active');
		document.getElementById(dataTdTarget).classList.toggle('is-active');
	}
});
