<?php
/**
 * Since 1.7.8
 * post type register
 * announcement management
 */
namespace  TUTOR;

class TutorAnnouncements {

    public function __construct(){
		/**
		 * register announcement page
		 */
        add_action('admin_menu', array($this,'register_menu'));
        add_action("wp_ajax_tutor_announcement_create", array($this,'create_or_update_annoucement'));
        add_action("wp_ajax_tutor_announcement_delete", array($this,'delete_annoucement'));
    }

    
    public function register_menu() {
        add_submenu_page('tutor', __('Announcements', 'tutor-pro'), __('Announcements', 'tutor-pro'), 'manage_tutor', 'tutor_announcements', array($this, 'tutor_announcements'));
    }
    


	public function tutor_announcements(){
		include tutor()->path . 'views/pages/tutor_announcements.php';
    }
    
    public function create_or_update_annoucement(){   

        //prepare alert message
        $create_success_msg = __("Announcement created successfully",'tutor');
        $update_success_msg = __("Announcement updated successfully",'tutor');
        $create_fail_msg = __("Announcement creation failed",'tutor');
        $update_fail_msg = __("Announcement update failed",'tutor');

        $error = array();
        $response = array();
        tutils()->checking_nonce();
        
        //set data and sanitize it
        $form_data = array(
          
            'post_type' => 'tutor_announcements',
            'post_title' => sanitize_text_field($_POST['tutor_annoument_title']),
            'post_content' => sanitize_textarea_field($_POST['tutor_annoument_summary']),
            'post_parent' => sanitize_text_field($_POST['tutor_announcement_course']),
            'post_status' => 'publish'
        );

        if(isset($_POST['announcement_id'])){
            $form_data['ID'] = sanitize_text_field($_POST['announcement_id']);
        }

        //validation message set
        if(empty($form_data['post_parent'])){
            $error['post_parent'] = __('Course name required','tutor'); 

        }
        if(empty($form_data['post_title'])){
            $error['post_title'] = __('Announcement title required','tutor'); 
        }
        if(empty($form_data['post_content'])){
            $error['post_content'] = __('Announcement summary required','tutor'); 

        }

        if(count($error)>0){
            $response['status']     = 'validation_error';
            $response['message']    = $error;
            wp_send_json($response);
        }
        else{
                    //insert or update post
            $post_id = wp_insert_post($form_data);
            if($post_id > 0){
                $response['status']     = 'success';
                //set reponse message as per action type
                if($_POST['action_type'] == 'create'){
                    $response['message'] = $create_success_msg;
                }
                if($_POST['action_type'] == 'update'){
                    $response['message'] = $update_success_msg;
                }

                //provide action hook
                if($_POST['tutor_notify_students']){
                    do_action('tutor_announcements/after/save',$post_id,$post_title,$post_parent);
                }
                
                wp_send_json($response);
            }
            else{
                //failure message
                $response['status']     = 'fail';
                if($_POST['action_type'] == 'create'){
                    $response['message'] = $create_fail_msg;
                }
                if($_POST['action_type'] == 'update'){
                    $response['message'] = $update_fail_msg;
                }
                wp_send_json($response);
            }

        }
        
    }

    public function delete_annoucement(){
        $announcement_id = sanitize_text_field($_POST['announcement_id']);
        $delete = wp_delete_post($announcement_id);
        if($delete){
            $response = array(
                'status'    => 'success',
                'message'   => __('Announcement deleted successfully','tutor')
            );
            wp_send_json($response);
        }
        else{
            $response = array(
                'status'    => 'fail',
                'message'   => __('Announcement delete failed','tutor')
            );      
            wp_send_json($response);     
        }
    }

}