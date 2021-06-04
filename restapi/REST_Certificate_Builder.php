<?php
namespace TUTOR;
use WP_REST_Request;

if (!defined('ABSPATH')) {
    exit;
}

class REST_Certificate_Builder {

	use REST_Response;

	private $user_id;
    private $certificate_hash;

	/** 
     * Require certificate_hash and user id
     * 
     * @return json object with certificate detail 
     */
	public function certificate_detail(WP_REST_Request $request) {
		$this->user_id = $request->get_param('id');
        $this->certificate_hash = $request->get_param('certificate_hash');
		global $wpdb;
		$table = $wpdb->prefix."table_name"; 
        // Todo:: Fatch data here
		//author obj
		$data = array();
		
		$response = array(
			'status_code'=> 'invalid_id',
			'message'=> __('success','tutor'),
			'data'=> $data
		);

		return self::send($response);		
	}

    /** 
     * Require certificate hash and user id
     * 
     * @return json object with certificate detail 
     */
	public function certificate_template_update(WP_REST_Request $request) {
		$this->user_id = $request->get_param('id');
        $this->certificate_hash = $request->get_param('certificate_hash');
		global $wpdb;
		$table = $wpdb->prefix."table_name"; 
        // Todo:: Insert certificate data here
		//author obj
		$data = array();
		
		$response = array(
			'status_code'=> 'invalid_id',
			'message'=> __('Certificate inserted successfully','tutor'),
			'data'=> $data
		);

		return self::send($response);		
	}
}
