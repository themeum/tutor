jQuery(document).ready(function($){
    'use strict';

    /*========================================================================
     * Tutor WP Editor Button
     *======================================================================== */
    function __(string) {
        return string;
    }
    tinymce.PluginManager.add('tutor_button', function( editor, url ) {
        editor.addButton( 'tutor_button', {
            text: __( 'Tutor ShortCode' ),
            icon: false,
            type: 'menubutton',
            menu: [
                {
                    text: __( 'Student Registration Form' ),
                    onclick: function() {
                        editor.insertContent('[tutor_student_registration_form]');
                    }
                },
                /*{
                    text: 'Student Dashboard',
                    onclick: function() {
                        editor.insertContent('[tutor_dashboard]');
                    }
                },*/{
                    text: __( 'Instructor Registration Form' ),
                    onclick: function() {
                        editor.insertContent('[tutor_instructor_registration_form]');
                    }
                },
               /* {
                    text: 'Courses',
                    onclick: function() {
                        editor.insertContent('[tutor_course]');
                    }
                },*/



                {
                    text: __( 'Courses' ),
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Courses Shortcode',
                            body: [
                                {
                                    type: 'textbox',
                                    name: 'id',
                                    label: __( 'Course id, separate by (,) comma' ),
                                    value: ''
                                },{
                                    type: 'textbox',
                                    name: 'exclude_ids',
                                    label: __( 'Exclude Course IDS' ),
                                    value: ''
                                },
                                {
                                    type: 'textbox',
                                    name: 'category',
                                    label: __( 'Category IDS' ),
                                    value: ''
                                },
                                {type: 'listbox',
                                    name: 'orderby',
                                    label: __( 'Order By :' ),
                                    onselect: function(e) {

                                    },
                                    'values': [
                                        {text: 'ID', value: 'ID'},
                                        {text: 'title', value: 'title'},
                                        {text: 'rand', value: 'rand'},
                                        {text: 'date', value: 'date'},
                                        {text: 'menu_order', value: 'menu_order'},
                                        {text: 'post__in', value: 'post__in'},
                                    ]
                                },
                                {type: 'listbox',
                                    name: 'order',
                                    label: __( 'Order :' ),
                                    onselect: function(e) {

                                    },
                                    'values': [
                                        {text: 'DESC', value: 'DESC'},
                                        {text: 'ASC', value: 'ASC'}
                                    ]
                                },
                                ,{
                                    type: 'textbox',
                                    name: 'count',
                                    label: __( 'Count' ),
                                    value: '6',
                                }
                            ],
                            onsubmit: function( e ) {
                                editor.insertContent( '[tutor_course id="' + e.data.id + '" exclude_ids="'+e.data.exclude_ids+'" category="'+e.data.category+'" orderby="'+e.data.orderby+'" order="'+e.data.order+'" count="'+e.data.count+'"]');
                            }
                        });
                    }
                }


            ]
        });
    });

});
