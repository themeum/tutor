window.jQuery(document).ready($=>{
    const {__} = wp.i18n;
    
    $(document).on('click', '.tutor-course-wishlist-btn', function (e) {
        e.preventDefault();

        var $that = $(this);
        var course_id = $that.attr('data-course-id');

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: { 
                course_id, 
                'action': 'tutor_course_add_to_wishlist' 
            },
            beforeSend: function () {
                $that.addClass('tutor-updating-message tutor-m-0');
            },
            success: function (data) {
                if (data.success) {
                    if (data.data.status === 'added') {
                        $that.find('i').addClass('ttr-fav-full-filled').removeClass('ttr-fav-line-filled');
                    } else {
                        $that.find('i').addClass('ttr-fav-line-filled').removeClass('ttr-fav-full-filled');
                    }
                } else {
                    window.location = data.data.redirect_to;
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message tutor-m-0');
            }
        });
    });
})