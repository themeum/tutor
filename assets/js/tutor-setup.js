jQuery(document).ready(function($){
    'use strict';

        const player = new Plyr('#player', {autoplay: true, muted: true, volume: 2});
        player.on('ended', event => {
            $('.tutor-wrapper-video').removeClass('active');
            $('.tutor-wrapper-type').addClass('active');
        });


        $('.tutor-type-next, .tutor-type-skip').on( 'click', function(e) {
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
    
        
        

        $( '.tutor-boarding-next, .tutor-boarding-skip' ).on( 'click', function(e) {
            e.preventDefault();
            $('.tutor-wrapper-boarding').removeClass('active');
            $('.tutor-wrapper-settings').addClass('active');
        });


        $( 'ul.tutor-setup-title li' ).on( 'click', function(e) {
            $( 'ul.tutor-setup-title li' ).removeClass('active');
            $(this).addClass('active');
        });


        // Reset Total Form
        $( 'ul.tutor-setup-title li' ).on( 'click', function(e) {
            //$(selector)[0].reset();
        });
        
        


});