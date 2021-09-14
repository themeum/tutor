window.jQuery(document).ready(function($){

    /**
     * Confirmation for deleting Topic
     */
     $(document).on('click', '.topic-delete-btn a', function(e){
        var topic_id = $(this).attr('data-topic-id');

        if ( ! confirm( __( 'Are you sure to delete?', 'tutor' ) )){
            e.preventDefault();
        }
    });

    $(document).on('click', '.tutor-expand-all-topic', function (e) {
        e.preventDefault();
        $('.tutor-topics-body').slideDown();
        $('.expand-collapse-wrap i').removeClass('tutor-icon-light-down').addClass('tutor-icon-light-up');
    });

    $(document).on('click', '.tutor-collapse-all-topic', function (e) {
        e.preventDefault();
        $('.tutor-topics-body').slideUp();
        $('.expand-collapse-wrap i').removeClass('tutor-icon-light-up').addClass('tutor-icon-light-down');
    });

    $(document).on('click', '.topic-inner-title, .expand-collapse-wrap', function (e) {
        e.preventDefault();
        var $that = $(this);
        $that.closest('.tutor-topics-wrap').find('.tutor-topics-body').slideToggle();
        $that.closest('.tutor-topics-wrap').find('.expand-collapse-wrap i').toggleClass('tutor-icon-light-down tutor-icon-light-up');
    });
});