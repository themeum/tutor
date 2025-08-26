(function ($) {
	$(document).on('click', '[data-td-target]', function (e) {
		const $el = $(this);
		const dataTdTarget = $el.data('td-target');

		$el.toggleClass('is-active');
		$('#' + dataTdTarget).toggle();
	});
})(jQuery);
