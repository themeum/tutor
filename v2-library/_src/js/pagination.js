import { get_response_message } from "../../../assets/react/helper/response";

window.jQuery(document).ready($=>{    
    const {__} = wp.i18n;

    $('[data-tutor_pagination_ajax]').css('display', 'flex');
    
    $(document).on('click', '[data-tutor_pagination_ajax] a.page-numbers', function(e){
        e.preventDefault();

        let link_el = $(this);
        let replace_me = $(this).closest('.tutor-pagination-wrapper-replacable');

        if(link_el.find('.tutor-updating-message').length) {
            // Prevent duplicate click
            return;
        }

        let url_string = $(this).attr('href');
        let url = new URL(url_string);
        let page_num = url.searchParams.get("current_page");

        let data = $(this).closest('[data-tutor_pagination_ajax]').data('tutor_pagination_ajax');
        data.current_page = page_num;

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function() {
                link_el.prepend('<span class="tutor-updating-message" style="font-style: initial; vertical-align: middle; display: inherit;"></span>');
            },
            success: function(resp) {
                let {success, data={}} = resp || {};
                let {html} = data;

                if(success && html) {
                    replace_me.replaceWith(html);
                    $('[data-tutor_pagination_ajax]').css('display', 'flex');
                    window.dispatchEvent(new Event(_tutorobject.content_change_event));

                } else {
                    tutor_toast(__('Error', 'tutor'), get_response_message(data), 'error');
                }
            },
            error: function() {
                tutor_toast(__('Error', 'tutor'), 'Something went wrong', 'error');
            },
            complete: function() {
                link_el.find('.tutor-updating-message').remove();
            }
        })
    });
});
