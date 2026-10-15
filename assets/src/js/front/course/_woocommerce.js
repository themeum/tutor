window.jQuery(document).ready($=>{
    // course archive page added_to_cart event change view cart html
    $(document).on('added_to_cart', function (event, fragments, cart_hash, $button) {
        $button.removeClass('is-loading');
        $button.siblings('a.added_to_cart')
            .addClass('tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block')
            .prepend(`<span class="tutor-icon-cart-line tutor-mr-8"></span>`);
    });
    $(document).on('adding_to_cart', function (e, $button) {
        $button.addClass('is-loading');
        setTimeout(() => {
            $button.removeClass('is-loading');
        }, 4000);
    });

});