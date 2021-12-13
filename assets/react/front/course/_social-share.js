window.jQuery(document).ready($=>{

    /**
     * Share Link enable
     *
     * @since v.1.0.4
     */
     if ($.fn.ShareLink) {
        var $social_share_wrap = $('.tutor-social-share-wrap');
        $social_share_wrap.prev().click(function(e) {
            e.preventDefault();
            $social_share_wrap.toggle();
        });

        if ($social_share_wrap.length) {
            var share_config = JSON.parse($social_share_wrap.attr('data-social-share-config'));

            $('.tutor_share').ShareLink({
                title: share_config.title,
                text: share_config.text,
                image: share_config.image,
                class_prefix: 's_',
                width: 640,
                height: 480,
            });
        }
    }
});