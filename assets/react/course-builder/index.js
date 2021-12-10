import './topic';
import './lesson';
import './quiz';
import './assignment';
import './attachment';
import './video-picker';
import './instructor-multi';
import './content-drip';

window.jQuery(document).ready(function($) {
    $('.tutor-certificate-template-tab [data-tutor-tab-target]').click(function(){
        $(this).addClass('is-active').siblings().removeClass('is-active');
        $('#'+$(this).data('tutor-tab-target')).show().siblings().hide();
    });

    /* $('.').click(function() {
        $(this).siblings().filter('tutor-certificate-collapsible')
    }); */
});


/**
 * Re init required
 * Modal Loaded...
 */
const load_select2 = function() {
    if (jQuery().select2) {
        jQuery('.select2_multiselect').select2({
            dropdownCssClass: 'increasezindex'
        });
    }
}
window.addEventListener('DOMContentLoaded', load_select2)
window.addEventListener(_tutorobject.content_change_event, load_select2);
window.addEventListener(_tutorobject.content_change_event, ()=>console.log(_tutorobject.content_change_event));