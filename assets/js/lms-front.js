jQuery(document).ready(function($){
    'use strict';

    $(document).on('change', '.lms-course-filter-form', function(e){
        e.preventDefault();
        $(this).closest('form').submit();
    });

    if (typeof Plyr !== 'undefined') {
        const player = new Plyr('#lmsPlayer');
        player.on('timeupdate', function(event){
            const instance = event.detail.plyr;
            //console.log(instance);
            console.log(instance.duration);
        });
    }
});

