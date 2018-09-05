jQuery(document).ready(function($){
    'use strict';

    /**
     * Option Settings Nav Tab
     */
    $('.lms-option-nav-tabs li a').click(function(e){
        e.preventDefault();
        var tab_page_id = $(this).attr('href');
        $('.option-nav-item').removeClass('current');
        $(this).closest('li').addClass('current');
        $('.lms-option-nav-page').hide();
        $(tab_page_id).addClass('current-page').show();
    });

    $('#save_lms_option').click(function (e) {
        e.preventDefault();
        $(this).closest('form').submit();
    });
    $('#lms-option-form').submit(function(e){
        e.preventDefault();

        var $form = $(this);
        var data = $form.serialize();

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $form.find('.button').addClass('updating-message');
            },
            success: function (data) {
                //
            },
            complete: function () {
                $form.find('.button').removeClass('updating-message');
            }
        });
    });

    $('.lms-field-slider').each(function(){
        var $slider = $(this);
        var $input = $slider.closest('.lms-field-type-slider').find('input[type="hidden"]');
        var $showVal = $slider.closest('.lms-field-type-slider').find('.lms-field-type-slider-value');
        var min = parseFloat($slider.closest('.lms-field-type-slider').attr('data-min'));
        var max = parseFloat($slider.closest('.lms-field-type-slider').attr('data-max'));

        $slider.slider({
            range: "max",
            min: min,
            max: max,
            value: $input.val(),
            slide: function( event, ui ) {
                $showVal.text(ui.value);
                $input.val(ui.value);
            }
        });
    })



});

