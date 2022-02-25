window.jQuery(document).ready($=>{
    // course archive page added_to_cart event change view cart html
    $(document).on('added_to_cart', function (event, fragments, cart_hash, $button) {
        $button.siblings('a.added_to_cart')
            .addClass('tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-md')
            .prepend($button[0].querySelector('.btn-icon'));
    });
});