import { get_response_message } from "../helper/response";

/* 
    Alert
    --------------
    The script below is used for many things like lesson comment, course review, course archive, instructor list pagination. 
    Please change carefully. 
*/

window.jQuery(document).ready($=>{
    const {__} = wp.i18n;

    // Enable pagination click
    // Users are not supposed to click ajax based pagination before JS evenets assigned.
    // Because normal URL click will load unwanted page contents.
    $('[data-tutor_pagination_ajax]').addClass('is-ajax-pagination-enabled');

    $(document).on('click', '[data-tutor_pagination_ajax] a.page-numbers', function(e){
        e.preventDefault();

        let link_el = $(this);
        let content_container = $(this).closest('.tutor-pagination-wrapper-replacable');
        let content_container_html = content_container.html();

        if (!content_container.length) {
            return;
        }

        let url_string = $(this).attr('href');
        let url = new URL(url_string);
        let page_num = parseInt(url.searchParams.get("current_page"));

        let pagination_root = $(this).closest('[data-tutor_pagination_ajax]');
        let data = pagination_root.data('tutor_pagination_ajax');
        let layout = pagination_root.data('tutor_pagination_layout');

        typeof layout!='object' ? layout={} : 0;
        data.current_page = (isNaN(page_num) || page_num <= 1) ? 1 : page_num;

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                let {type} = layout || {};
                if(type=='load_more') {
                    link_el.addClass('is-loading');
                } else {
                    content_container.html('<div class="tutor-spinner-wrap"><span class="tutor-spinner" area-hidden="true"></span></div>');
                }
                
                // move to top
                $('html, body').animate({ scrollTop: content_container.offset().top }, 'fast');
            },

            success: function(resp) {
                let {success, data={}} = resp || {};
                let {html} = data;

                if (success) {
                    let append_container = content_container.find('.tutor-pagination-content-appendable');
                    if(append_container.length) {

                        if(!html) {
                            link_el.remove();
                            return;
                        }

                        // Append the conntent
                        append_container.append(html);

                        // Update pagination data since pagination template is not supposed to be loaded here
                        url.searchParams.set('current_page', page_num+1);
                        link_el.attr('href', url.toString());

                    } else {
                        content_container.html(html);
                    }

                    window.dispatchEvent(new Event(_tutorobject.content_change_event));

                } else {
                    tutor_toast(__('Error', 'tutor'), get_response_message(data), 'error');
                }

                $('[data-tutor_pagination_ajax]').addClass('is-ajax-pagination-enabled');
            },
            error: function() {
                content_container.html(content_container_html);
                tutor_toast(__('Error', 'tutor'), __('Something went wrong', 'tutor'), 'error');
            },
            complete: function(){
                link_el.removeClass('is-loading');
            }
        });
    });
});
