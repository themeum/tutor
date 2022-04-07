;(function($) {
	// Nav
	$.fn.tutorNav = function(options) {	
		this.each(function() {

			var nav = this;
			var elements = $(nav).find('> li:not(".tutor-nav-more")');
			
			var tutorNav = function() {
				this.init = function() {
					var that = this;
					this.buildList();
					this.setup();
					
					$(window).on('resize', function() {
						that.cleanList();
						that.setup();
					});
				};
				
				this.setup = function() {
					var firstPos = elements.first().position();
					var wrappedElements = $();
					var first = true;
					elements.each(function(i) {
						var el = $(this);
						var pos = el.position();
						
						if (pos.top !== firstPos.top) {
							wrappedElements = wrappedElements.add(el);
							if (first) {
								wrappedElements = wrappedElements.add(elements.eq(i-1));
								first = false;
							}
						}
					});
					
					// @todo: need to change active class selector
					if (wrappedElements.length) {
						var newSet = wrappedElements.clone();
						wrappedElements.addClass('tutor-d-none');
						$(nav).find('.tutor-nav-more-list').append(newSet);		
						$(nav).find('.tutor-nav-more').removeClass('tutor-d-none').addClass('tutor-d-inline-block');
						if($(nav).find('.tutor-nav-more-list > li.is-active').length) {
							$(nav).find('.tutor-nav-more').addClass('is-active');
						}
					}
				};
				
				this.cleanList = function() {
					$(nav).find('.tutor-nav-more-list').empty();
					$(nav).find('.tutor-nav-more').removeClass('tutor-d-inline-block is-active').addClass('tutor-d-none');
					elements.removeClass('tutor-d-none');
				};
				
				this.buildList = function() {
					$(nav).find('.tutor-nav-more-item').on('click', function(event) {
						event.preventDefault();
						if($(nav).find('.tutor-nav-more-list > li.is-active').length) {
							$(this).parent().addClass('is-active');
						}
						$(this).parent().toggleClass('tutor-nav-opened');
					});

					$(document).mouseup(e => {
                        if ($(nav).find('.tutor-nav-more-item').has(e.target).length === 0) {
							$(nav).find('.tutor-nav-more').removeClass('tutor-nav-opened');
						}
					});
				};
			};
			
			new tutorNav().init();
		});
	};

    $('[tutor-priority-nav]').tutorNav();
})(window.jQuery);