import { get_response_message } from "../helper/response";

window.jQuery(document).ready($=>{
    const {__} = wp.i18n;

    $(document).on('click', '[data-tutor_pagination_ajax] a.page-numbers', function(e){
        e.preventDefault();

        let link_el = $(this);
        let content_container = $(this).closest('[tutor-course-list-container]');
        let content_container_html = content_container.html();

        if (!content_container.length) {
            return;
        }

        let url_string = $(this).attr('href');
        let url = new URL(url_string);
        let page_num = parseInt(url.searchParams.get("current_page"));

        let data = $(this).closest('[data-tutor_pagination_ajax]').data('tutor_pagination_ajax');
        data.current_page = (isNaN(page_num) || page_num <= 1) ? 1 : page_num;

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                content_container.html('<div class="tutor-spinner-wrap"><span class="tutor-spinner" area-hidden="true"></span></div>');
                // move to top
                $('html, body').animate({ scrollTop: content_container.offset().top }, 'fast');
            },

            success: function(resp) {
                let {success, data={}} = resp || {};
                let {html} = data;

                if (success) {
                    if (!html) {
                        link_el.remove();
                        return;
                    }

                    content_container.html(html);

                    // Update pagination data since pagination template is not supposed to be loaded here
                    url.searchParams.set('current_page', page_num + 1);
                    link_el.attr('href', url.toString());

                    window.dispatchEvent(new Event(_tutorobject.content_change_event));

                } else {
                    tutor_toast(__('Error', 'tutor'), get_response_message(data), 'error');
                }
            },
            error: function() {
                content_container.html(content_container_html);
                tutor_toast(__('Error', 'tutor'), __('Something went wrong', 'tutor'), 'error');
            }
        });
    });
});
