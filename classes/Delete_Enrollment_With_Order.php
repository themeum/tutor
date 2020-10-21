<?php

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Delete_Enrollment_With_Order {
    function __construct(){
        add_action('before_delete_post', array($this, 'delete_associated_enrollment'));
    }

    public function delete_associated_enrollment($post_id){
        global $wpdb;

        $enroll_id = $wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_tutor_enrolled_by_order_id' AND meta_value={$post_id}");
        
        if(is_numeric($enroll_id) && $enroll_id>0){

            $course_id = get_post_field('post_parent', $enroll_id);
            $user_id = get_post_field('post_author', $enroll_id);

            tutils()->cancel_course_enrol($course_id, $user_id);
        }
    }
}
