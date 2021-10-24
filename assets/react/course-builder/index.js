import './topic';
import './lesson';
import './quiz';
import './assignment';
import './attachment';

window.jQuery(document).ready(function($) {
    $('.tutor-certificate-template-tab [data-tutor-tab-target]').click(function(){
        $(this).addClass('is-active').siblings().removeClass('is-active');
        $('#'+$(this).data('tutor-tab-target')).show().siblings().hide();
    });

    /* $('.').click(function() {
        $(this).siblings().filter('tutor-certificate-collapsible')
    }); */
});
