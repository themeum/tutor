window.jQuery(document).ready(function($){
    $('.tutor-dashboard .tutor-dashboard-menu-toggler').click(function(){
        var el = $('.tutor-dashboard-left-menu');
        el.closest('.tutor-dashboard').toggleClass('is-sidebar-expanded');

        if(el.css('display')!=='none') {
            el.get(0).scrollIntoView({block:'start'});
        }
    });
});