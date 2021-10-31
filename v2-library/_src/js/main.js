import './tutorModal';
import './tutorThumbnailPreview';
import './tutorPopupMenu';
import './tutorOffcanvas';
import './tutorNotificationTab';
import './tutorDefaultTab';
import './tutorPasswordStrengthChecker';

document.addEventListener('click', function(e) {
	const attr = 'data-td-target';
	const dataTdTarget = e.target.dataset.tdTarget;
	// console.log(document.getElementById(dataTdTarget));
	if (dataTdTarget) {
		e.target.closest('td').classList.toggle('is-active');
		document.getElementById(dataTdTarget).classList.toggle('active');

		// document
		// 	.getElementById(dataTdTarget)
		// 	.closest('tr')
		// 	.classList.toggle('active');
	}
});
