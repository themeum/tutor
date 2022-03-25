document.addEventListener('click', function(e) {
	/**
	 * Table td/tr toggle
	 */
	const dataTdTarget = e.target.dataset.tdTarget;
	if (dataTdTarget) {
		e.target.closest('td').classList.toggle('is-active');
		document.getElementById(dataTdTarget).classList.toggle('is-active');
	}

	/**
	 * Course details showmore toggle
	 */
	const dataShowmore = e.target.dataset.showmore;
	if (dataShowmore) {
		e.target.closest('.tutor-has-showmore').classList.toggle('is-active');
	}
});
