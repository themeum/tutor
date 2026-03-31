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
    // Users are not supposed to click ajax based pagination before JS events assigned.
    // Because normal URL click will load unwanted page contents.
    $('[data-tutor_pagination_ajax]').addClass('is-ajax-pagination-enabled');

    $(document).on('click', '[data-tutor_pagination_ajax] a.page-numbers', function(e){
        e.preventDefault();

        let link_el = $(this);
        let content_container = $(this).closest('.tutor-pagination-wrapper-replaceable');
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

                // Push state link
                let push_link = link_el.closest('[data-push_state_link]').attr('data-push_state_link');
                if(push_link){
                    const new_url = new URL(push_link);
                    new_url.searchParams.append('current_page', data.current_page);
                    window.history.pushState({}, '', new_url);
                }

                if(type=='load_more') {
                    // Add loading icon if it's loading button for appendable content
                    link_el.addClass('is-loading');
                } else {
                    // Otherwise replace the content container with loading icon
                    content_container.html('<div class="tutor-spinner-wrap"><span class="tutor-spinner" area-hidden="true"></span></div>');
                }
                
                // move to top
                if (type !== 'load_more') {
                    $('html, body').animate({ scrollTop: content_container.offset().top }, 'fast');
                }
            },

            success: function(resp) {
                let {success, data={}} = resp || {};
                let {html} = data;
                let {type} = layout || {};

                if (success) {
                    if( 'load_more' === type ) {
                        // remain collapsed reply boxes when load more
                        setTimeout(() => jQuery('.tutor-qa-reply, .tutor-reply-msg').css('display', 'none'))
                    }

                    let append_container = content_container.find('.tutor-pagination-content-appendable');
                    if(append_container.length) {

                        if(!html) {
                            link_el.remove();
                            return;
                        }
                        // Append the content
                        append_container.append(html);

                        // Update pagination data since pagination template is not supposed to be loaded here
                        url.searchParams.set('current_page', page_num+1);
                        link_el.attr('href', url.toString());
                        // Element will be mounted only when it should hide.
                        var hide = content_container.find('#tutor-hide-comment-load-more-btn');
                        if (hide.length) {
                            var loadMoreBtn = document.querySelector('.tutor-btn.page-numbers');
                            loadMoreBtn.remove();
                        }

                        /**
                         * Init tinyMCE for Q&A load more list
                         * 
                         * @since v2.1.0
                         */
                        if (e.target.classList.contains('tutor-qna-load-more') && _tutorobject.tutor_pro_url) {
                            const ids = document.querySelectorAll('.tutor-load-more-qna-ids');
                            let lastNode = ids[ids.length - 1];
                            const lastNodeIds = lastNode ? lastNode.getAttribute('value') : ''
                            const lastIdsArr = lastNodeIds.split(',');
                            setTimeout(() => {
                                lastIdsArr.forEach(element => {
                                    let editorId = `tutor_qna_reply_editor_${element}`;
                                    tinymce.execCommand(
                                        'mceAddEditor',
                                        false,
                                        editorId
                                    );
                                });
                            }, 1000) 
                        }
                    } else {
                        content_container.html(html);
                    }

                    window.dispatchEvent(new Event(_tutorobject.content_change_event));

                } else {
                    tutor_toast(__('Error', 'tutor'), get_response_message(data), 'error');
                }
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
