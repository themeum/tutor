(function($) {
	document.addEventListener('click', function(e) {
		/**
		 * Table td/tr toggle
		 */
		const dataTdTarget = e.target.dataset.tdTarget;
		if (dataTdTarget) {
			e.target.classList.toggle('is-active');
			$(`#${dataTdTarget}`).toggle();
		}
	});
})(jQuery);
