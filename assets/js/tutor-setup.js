jQuery(document).ready(function ($) {
    'use strict';

    const player = new Plyr('#player', {
        autoplay: true,
        muted: true,
        volume: 2
    });
    player.on('ended', event => {
        $('.tutor-wrapper-video').removeClass('active');
        $('.tutor-wrapper-type').addClass('active');
    });


    $('.tutor-type-next, .tutor-type-skip').on('click', function (e) {
        e.preventDefault();
        $('.tutor-wrapper-type').removeClass('active');
        $('.tutor-wrapper-boarding').addClass('active');
    });


    $(".tutor-boarding").slick({
        centerMode: true,
        centerPadding: "60px",
        slidesToShow: 1,
        responsive: [{
                breakpoint: 768,
                settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: "40px",
                    slidesToShow: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: "40px",
                    slidesToShow: 1
                }
            }
        ]
    });


    $('.tutor-boarding-next, .tutor-boarding-skip').on('click', function (e) {
        e.preventDefault();
        $('.tutor-wrapper-boarding').removeClass('active');
        $('.tutor-wrapper-settings').addClass('active');
    });


    $('ul.tutor-setup-title li').on('click', function (e) {
        $('ul.tutor-setup-title li').removeClass('active');
        $(this).addClass('active');

        $('ul.tutor-setup-content li').removeClass('active');
        $('ul.tutor-setup-content li').eq($(this).index()).addClass('active');
    });


    // Reset Total Form
    // $( 'ul.tutor-setup-title li' ).on( 'click', function(e) {
    //     //$(selector)[0].reset();
    // });

    // Redirect after Finished
    // $('.tutor-redirect').on('click', function(e){
    //     e.preventDefault();
    //     window.location = $(this).data('url');
    // });


    // Tutor Bottom Action Button
    $('.tutor-setup-previous').on('click', function (e) {
        e.preventDefault();
        const _index = $(this).closest('li').index()
        if (_index > 0) {
            $('ul.tutor-setup-title li').removeClass('active').eq(_index - 1).addClass('active');
            $('ul.tutor-setup-content li').removeClass('active').eq(_index - 1).addClass('active');
        }
    });
    $('.tutor-setup-skip, .tutor-setup-next').on('click', function (e) {
        e.preventDefault();
        const _index = $(this).closest('li').index() + 1
        $('ul.tutor-setup-title li').removeClass('active').eq(_index).addClass('active');
        $('ul.tutor-setup-content li').removeClass('active').eq(_index).addClass('active');
    });


    $(document).on('submit', '#tutor-setup-form', function (e) {
        const _form = $(this).serialize();
        // = (array) maybe_unserialize(get_option('tutor_option'));
        console.log('EMC->', _form);

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: _form,
            beforeSend: function () {
                // $form.find('.button').addClass('tutor-updating-message');
            },
            success: function (data) {
                console.log('EEE->', data);
                if (data.success) {
                    //window.location.reload();
                }
            },
            complete: function () {
                // $form.find('.button').removeClass('tutor-updating-message');
                window.location = $(this).data('url');
            }
        });
    });


    $(function () {
        $('.input-switch-label').on('click', function (e) {
            return $(this).toggleClass('checked');
        });
    })


});