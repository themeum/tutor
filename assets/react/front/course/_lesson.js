window.jQuery(document).ready($=>{
    $(document).on('click', '.tutor-single-course-lesson-comments button[type="submit"]', function(e){
        e.preventDefault();
        const {__} = wp.i18n;
        let btn = $(this);
        let form = btn.closest('form');
        let data = form.serialize();
        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function(){
                btn.addClass('is-loading').prop('disabled', true);
            },
            complete: function() {
                btn.removeClass('is-loading');
                btn.removeAttr('disabled');
            },
            success: function(response){
                var attr = form.attr('tutor-comment-reply');
                // If it's reply then just prepend element.
                if (typeof attr !== 'undefined' && attr !== false) {
                    form.before(
                        response.data.html
                    );
                } else {
                    // Render comments section for new comment.
                    const wrapper = document.querySelector('.tutor-course-spotlight-comments');
                    wrapper.innerHTML = response.data.html;
                }
                $(".tutor-comment-line").css('height', 'calc(100% - 308px)');
                // Clear text area.
                $("textarea").val('');
            },
            error: function(e){
                btn.removeClass('is-loading').prop('disabled', false);
            }
        });
    });
});