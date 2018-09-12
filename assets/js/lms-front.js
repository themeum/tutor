jQuery(document).ready(function($){
    'use strict';

    $(document).on('change', '.lms-course-filter-form', function(e){
        e.preventDefault();
        $(this).closest('form').submit();
    });

});

