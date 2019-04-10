jQuery(document).ready(function($){
    'use strict';

    /*========================================================================
     * Tutor WP Editor Button
     *======================================================================== */

    tinymce.PluginManager.add('tutor_button', function( editor, url ) {
        editor.addButton( 'tutor_button', {
            text: 'Tutor ShortCode',
            icon: false,
            type: 'menubutton',
            menu: [
                {
                    text: 'Student Registration Form',
                    onclick: function() {
                        editor.insertContent('[tutor_student_registration_form]');
                    }
                },
                {
                    text: 'Student Dashboard',
                    onclick: function() {
                        editor.insertContent('[tutor_dashboard]');
                    }
                },{
                    text: 'Instructor Registration Form',
                    onclick: function() {
                        editor.insertContent('[tutor_instructor_registration_form]');
                    }
                },
            ]
        });
    });

});
