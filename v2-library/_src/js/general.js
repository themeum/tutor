document.addEventListener('click', function(e) {
	/**
	 * Table td/tr toggle
	 */
	const dataTdTarget = e.target.dataset.tdTarget;
	if (dataTdTarget) {
		e.target.closest('td').classList.toggle('is-active');
		document.getElementById(dataTdTarget).classList.toggle('is-active');
	}
});
