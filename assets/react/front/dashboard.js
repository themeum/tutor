window.jQuery(document).ready(function($){

    // Toggle menu in mobile view
    $('.tutor-dashboard .tutor-dashboard-menu-toggler').click(function(){
        var el = $('.tutor-dashboard-left-menu');
        el.closest('.tutor-dashboard').toggleClass('is-sidebar-expanded');

        if(el.css('display')!=='none') {
            el.get(0).scrollIntoView({block:'start'});
        }
    });


    // Register International phone number picker
    var telInput = $("#phone"),
    errorMsg = $("#error-msg"),
    validMsg = $("#valid-msg");

    // initialise plugin
    telInput.intlTelInput({

    allowExtensions: true,
    formatOnDisplay: true,
    autoFormat: true,
    autoHideDialCode: true,
    autoPlaceholder: true,
    defaultCountry: "auto",
    ipinfoToken: "yolo",

    nationalMode: false,
    numberType: "MOBILE",
    //onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
    preferredCountries: ['sa', 'ae', 'qa','om','bh','kw','ma'],
    preventInvalidNumbers: true,
    separateDialCode: true,
    initialCountry: "auto",
    geoIpLookup: function(callback) {
    $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
    var countryCode = (resp && resp.country) ? resp.country : "";
    callback(countryCode);
    });
    },
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.9/js/utils.js"
    });

    var reset = function() {
    telInput.removeClass("error");
    errorMsg.addClass("hide");
    validMsg.addClass("hide");
    };

    // on blur: validate
    telInput.blur(function() {
    reset();
    if ($.trim(telInput.val())) {
    if (telInput.intlTelInput("isValidNumber")) {
        validMsg.removeClass("hide");
    } else {
        telInput.addClass("error");
        errorMsg.removeClass("hide");
    }
    }
    });

    // on keyup / change flag: reset
    telInput.on("keyup change", reset);



});