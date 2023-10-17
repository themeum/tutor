window.jQuery(document).ready($ => {

    const {__} = wp.i18n;

    function toggle_star_(star){
        star.add(star.prevAll()).filter('i').addClass('tutor-icon-star-bold').removeClass('tutor-icon-star-line');
        star.nextAll().filter('i').removeClass('tutor-icon-star-bold').addClass('tutor-icon-star-line');
    }

    /**
     * Hover tutor rating and set value
     */
    $(document).on('mouseover', '[tutor-ratings-selectable] i', function () {
        toggle_star_($(this));
    });

    $(document).on('click', '[tutor-ratings-selectable] i', function () {
        var rating = $(this).attr('data-rating-value');
        $(this).closest('[tutor-ratings-selectable]').find('input[name="tutor_rating_gen_input"]').val(rating);
        
        toggle_star_($(this));
    });

    $(document).on('mouseout', '[tutor-ratings-selectable]', function(){
        var value = $(this).find('input[name="tutor_rating_gen_input"]').val();
        var rating = parseInt(value);
        
        var selected = $(this).find('[data-rating-value="'+rating+'"]');
        (rating && selected && selected.length>0) ? toggle_star_(selected) : $(this).find('i').removeClass('tutor-icon-star-bold').addClass('tutor-icon-star-line');
    });


    /**
     * On review popup dismiss, clear the review popup data.
     *
     * @since 2.4.0
     */
    $(document).on('click','.tutor-course-review-popup-form .tutor-modal-close-o, .tutor-course-review-popup-form .tutor-review-popup-cancel', function() {
		let modal = $(this).closest('.tutor-modal');
		let course_id = modal.find('input[name="course_id"]').val();
		let data = {
			action: 'tutor_clear_review_popup_data',
			course_id: course_id
		}

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: data,
            beforeSend: function () {
                modal.removeClass('tutor-is-active');
            },
            success: function (res) {
                if (!res.success) {
                    console.warn('review popup data clear error');
                }
            }
        });
	})

    $(document).on('click', '.tutor_submit_review_btn', function (e) {
        // Prevent normal submission to validate input
        e.preventDefault();

        // Collect input
        var $that = $(this);
        var form = $that.closest('form');
        var rating = form.find('input[name="tutor_rating_gen_input"]').val();
        var review = (form.find('textarea[name="review"]').val() || '').trim();
        var course_id = form.find('input[name="course_id"]').val();
        var review_id = form.find('input[name="review_id"]').val();

        var data = form.serializeObject();
        
        // Validate
        if(!rating || rating==0 || !review) {
            alert(__('Rating and review required', 'tutor'));
            return;
        }

        const btnInnerHtml = $that.html().trim();

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $that.html(__('Updating...', 'tutor')).attr('disabled', 'disabled').addClass('is-loading');
            },
            success: function (response) {
                const {success, data={}} = response || {};
                const {message=__('Something Went Wrong!', 'tutor')} = data;

                if(!success) {
                    tutor_toast(__('Error!', 'tutor'), message, 'error');
                    return;
                }

                // Show thank you
                tutor_toast(review_id ? __('Updated successfully!', 'tutor') : __('Thank You for Rating The Course!', 'tutor'), review_id ?  __('Updated rating will now be visible in the course page', 'tutor') : __('Your rating will now be visible in the course page', 'tutor'), 'success');

                /**
                 * After review submit success, clear review popup data
                 *
                 * @since 2.4.0
                 */
                $.ajax({
                    url: _tutorobject.ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'tutor_clear_review_popup_data',
			            course_id: course_id
                    },
                    success: function (res) {
                        if ( ! res.success ) {
                            console.warn('review popup data clear error');	
                        }
                    }
                });

                setTimeout(function(){
                    location.reload();
                }, 3000);
            },
            complete: function() {
                $that.html(btnInnerHtml).removeAttr('disabled').removeClass('is-loading');
            }
        });
    });

    // Show review form on opn (Single course)
    $(document).on('click', '.write-course-review-link-btn', function (e) {
        e.preventDefault();
        $(this).closest('.tutor-pagination-wrapper-replaceable')
                .next()
                .filter('.tutor-course-enrolled-review-wrap')
                .find('.tutor-write-review-form').slideToggle();
    });
});