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
                /*{
                    text: 'Student Dashboard',
                    onclick: function() {
                        editor.insertContent('[tutor_dashboard]');
                    }
                },*/{
                    text: 'Instructor Registration Form',
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
                    text: 'Courses',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Courses Shortcode',
                            body: [
                                {
                                    type: 'textbox',
                                    name: 'id',
                                    label: 'Course id, seperate by (,) comma',
                                    value: ''
                                },{
                                    type: 'textbox',
                                    name: 'exclude_ids',
                                    label: 'Exclude Course IDS',
                                    value: ''
                                },
                                {
                                    type: 'textbox',
                                    name: 'category',
                                    label: 'Category IDS',
                                    value: ''
                                },
                                {type: 'listbox',
                                    name: 'orderby',
                                    label: 'Order By :',
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
                                    label: 'Order :',
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
                                    label: 'Count',
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
