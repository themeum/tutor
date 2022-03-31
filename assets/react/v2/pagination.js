import { get_response_message } from "../helper/response";

window.jQuery(document).ready($=>{
    const {__} = wp.i18n;

    $('[data-tutor_pagination_ajax]').css('display', 'flex');

    $(document).on('click', '[data-tutor_pagination_ajax] a.page-numbers', function(e){
        e.preventDefault();

        let link_el = $(this);
        const innerSpan = link_el.find('span');
        let replace_me = $(this).closest('.tutor-pagination-wrapper-replacable');

        if(link_el.find('.is-loading').length) {
            // Prevent duplicate click
            return;
        }

        let url_string = $(this).attr('href');
        let url = new URL(url_string);
        let page_num = parseInt(url.searchParams.get("current_page"));

        let data = $(this).closest('[data-tutor_pagination_ajax]').data('tutor_pagination_ajax');
        data.current_page = (isNaN(page_num) || page_num<=1) ? 1 : page_num;

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                if (innerSpan.hasClass('tutor-icon-angle-right')) {
                    innerSpan.removeClass('tutor-icon-angle-right').addClass('tutor-icon-spinner');
                } else if (innerSpan.hasClass('tutor-icon-angle-left')) {
                    innerSpan.removeClass('tutor-icon-angle-left').addClass('tutor-icon-spinner');
                }
            },
            success: function(resp) {
                let {success, data={}} = resp || {};
                let {html} = data;

                if(success) {
                    let append_root = replace_me.find('.tutor-pagination-content-appendable');
                    if(append_root.length) {

                        if(!html) {
                            link_el.remove();
                            return;
                        }

                        // Append the conntent
                        append_root.append(html);

                        // Update pagination data since pagination template is not supposed to be loaded here
                        url.searchParams.set('current_page', page_num+1);
                        link_el.attr('href', url.toString());
                    } else {
                        replace_me[replace_me.hasClass('replace-inner-contents') ? 'html' : 'replaceWith'](html);
                    }

                    $('[data-tutor_pagination_ajax]').css('display', 'flex');
                    window.dispatchEvent(new Event(_tutorobject.content_change_event));

                } else {
                    tutor_toast(__('Error', 'tutor'), get_response_message(data), 'error');
                }
            },
            error: function() {
                tutor_toast(__('Error', 'tutor'), 'Something went wrong', 'error');
            },
            complete: function () {
                if (innerSpan.hasClass('tutor-icon-angle-right')) {
                    innerSpan.removeClass('tutor-icon-spinner').addClass('tutor-icon-angle-right');
                } else if (innerSpan.hasClass('tutor-icon-angle-left')) {
                    innerSpan.removeClass('tutor-icon-spinner').addClass('tutor-icon-angle-left');
                }
            }
        })
    });
});
