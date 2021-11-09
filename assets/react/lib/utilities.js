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
        let $that = $(this);
        let prompt = $(this).data('prompt');
        let del = $(this).data('delete_element_id');
        let redirect = $(this).data('redirect_to');
        var data = $(this).data('request_data') || {};
        typeof data=='string' ? data=JSON.parse(data) : 0;
        
        if(prompt && !window.confirm(prompt)) {
            return;
        }

        $.ajax({
            url: _tutorobject.ajaxurl, 
            type: 'POST', 
            data: data,
            beforeSend: function () {
                $that.addClass('updating-icon');
            },
            success: function(data) {
                if(data.success) {
                    if(del) {
                        $('#'+del).fadeOut(function(){
                            $(this).remove();
                        });
                    }

                    if(redirect) {
                        window.location.assign(redirect);
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
                $that.removeClass('updating-icon');
            }
        })
    });

    // Textarea auto height
    $(document).on('input', '.tutor-textarea-auto-height', function () {
        this.style.height = "auto";
        this.style.height = (this.scrollHeight) + "px";
    });
    
    $('.tutor-textarea-auto-height').trigger('input');
});