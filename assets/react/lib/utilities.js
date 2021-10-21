window.jQuery(document).ready(function($) {
    const {__} = wp.i18n;
    
    // Copy text
    $(document).on('click', '.tutor-copy-text', function(e) {

        // Prevent default action
        e.stopImmediatePropagation();
        e.preventDefault();

        // Get the text
        let text = $(this).data('text');
        
        // Create input to place texts in
        var $temp = $("<input>");
        $("body").append($temp);

        $temp.val(text).select();

        document.execCommand("copy");
        $temp.remove();

        tutor_toast(__('Copied!', 'tutor'), text, 'success');
    });

    // Ajax action 
    $(document).on('click', '.tutor-list-ajax-action', function() {
        let url = $(this).data('url');
        let type = $(this).data('type') || 'GET';
        let prompt = $(this).data('prompt');
        let del = $(this).data('delete_id');

        console.log(prompt);

        if(prompt && !window.confirm(prompt)) {
            return;
        }

        $.ajax({
            url, 
            type, 
            success: function(data) {
                if(data.success) {
                    if(del) {
                        $('#'+del).fadeOut(function(){
                            $(this).remove();
                        });
                    }
                    return;
                }
                
                let {message=__('Something Went Wrong!', 'tutor')} = data.data || {};
                tutor_toast('Error!', message, 'error');
            },
            error: function() {
                tutor_toast('Error!', __('Something Went Wrong!', 'tutor'), 'error');
            },
            complete: function() {

            }
        })
    });
});