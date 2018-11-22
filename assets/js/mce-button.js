jQuery(document).ready(function($){
    'use strict';

    /*========================================================================
     * Dozent WP Editor Button
     *======================================================================== */

    tinymce.PluginManager.add('dozent_button', function( editor, url ) {
        editor.addButton( 'dozent_button', {
            text: 'Dozent ShortCode',
            icon: false,
            type: 'menubutton',
            menu: [
                {
                    text: 'Student Registration Form',
                    onclick: function() {
                        editor.insertContent('[dozent_student_registration_form]');
                    }
                },
                {
                    text: 'Student Dashboard',
                    onclick: function() {
                        editor.insertContent('[dozent_student_dashboard]');
                    }
                },{
                    text: 'Teacher Registration Form',
                    onclick: function() {
                        editor.insertContent('[dozent_teacher_registration_form]');
                    }
                },
            ]
        });
    });

});
