jQuery(document).ready(function ($) {
    'use strict';

    const player = new Plyr('#player', {
        autoplay: true,
        muted: true,
        volume: 2,
        controls: false
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
        speed: 1000,
        centerMode: true,
        centerPadding: "19.5%",
        slidesToShow: 1,
        arrows: false,
        dots: true,
        responsive: [{
                breakpoint: 768,
                settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: "50px",
                    slidesToShow: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: "30px",
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
                //window.location = $(this).data('url');
            }
        });
    });


    $(function () {
        $('.tooltip-btn').on('click', function (e) {
            $(this).toggleClass('active');
        });

        /*  Input Label Toggle Color */
        //initail input on/of emphasizing
        var inputswitch = $(".input-switchbox");
        inputswitch.each(function () {
            inputCheckEmphasizing($(this));
        });

        function isChecked(th) {
            return th.prop('checked') ? true : false;
        }

        function inputCheckEmphasizing(th) {
            var checkboxRoot = th.parent().parent();
            if (isChecked(th)) {
                checkboxRoot.find('.label-on').addClass("active");
                checkboxRoot.find('.label-off').removeClass("active");
            } else {
                checkboxRoot.find('.label-on').removeClass("active");
                checkboxRoot.find('.label-off').addClass("active");
            }
        }

        // on/of emphasizing after input check click
        $(".input-switchbox").click(function () {
            inputCheckEmphasizing($(this));
        });

    })

    /* Grade Calculation Dropdwon  */
    $(function () {
        const selected = document.querySelector(".selected");
        const optionsContainer = document.querySelector(".options-container");

        const optionsList = document.querySelectorAll(".option");

        selected.addEventListener("click", () => {
            optionsContainer.classList.toggle("active");
        });

        optionsList.forEach(option => {
            option.addEventListener("click", () => {
                selected.innerHTML = option.querySelector("label").innerHTML;
                optionsContainer.classList.remove("active");
            });
        });
    });

    /* Time Limit sliders */
    $(function () {
        $('.range-input').on('mousemove', function (e) {
            let rangeInput = $(this).val();
            let rangeValue = $(this).parent().parent().find(".range-value");

            rangeValue.text(rangeInput);
        });
    });

    $(function () {
        $('#attempts-allowed-1').on('click', function (e) {
            if ($('#attempts-allowed-numer').prop("disabled", true)) {
                $(this).parent().parent().parent().addClass('active')
                $('#attempts-allowed-numer').prop("disabled", false);
            }
        });
        $('#attempts-allowed-2').on('click', function (e) {
            if ($('#attempts-allowed-2').is(':checked')) {
                $(this).parent().parent().parent().removeClass('active')
                $('#attempts-allowed-numer').prop("disabled", true);
            }
        });
    });

});