window.jQuery(document).ready(function($) {
    const {__} = wp.i18n;
    
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
});