<?php
/**
 * Created by PhpStorm.
 * User: mhshohel
 * Date: 22/10/19
 * Time: 12:57 PM
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;


class MigrationLP {

	public function __construct() {
		add_filter('tutor_tool_pages', array($this, 'tutor_tool_pages'));

		add_action('tutor_option_save_after', array($this, 'tutor_option_save_after'));
		add_action('init', array($this, 'check_if_maintenance'));

		add_action('wp_ajax_lp_migrate_course_to_tutor', array($this, 'lp_migrate_course_to_tutor'));
		add_action('wp_ajax__get_lp_live_progress_course_migrating_info', array($this, '_get_lp_live_progress_course_migrating_info'));

		add_action('tutor_action_migrate_lp_orders', array($this, 'migrate_lp_orders'));
		add_action('tutor_action_migrate_lp_reviews', array($this, 'migrate_lp_reviews'));
	}

	public function tutor_tool_pages($pages){
		$hasLPdata = get_option('learnpress_version');

		if ($hasLPdata){
			$pages['migration_lp'] = __('LearnPress Migration', 'tutor');
		}

		return $pages;
	}

	public function lp_migrate_course_to_tutor(){
		global $wpdb;

		//$course_id = 1826;

		update_option('_tutor_migrated_course_count', 0);

		$lp_courses = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'lp_course';");
		if (tutils()->count($lp_courses)){
			$course_i = 0;
			foreach ($lp_courses as $lp_course){
				$course_i++;

				$this->migrate_course($lp_course->ID);

				update_option('_tutor_migrated_course_count', $course_i);
			}
		}

		wp_send_json_success();
	}


	/**
	 *
	 * Get Live Update about course migrating info
	 */

	public function _get_lp_live_progress_course_migrating_info(){
		$migrated_count = (int) get_option('_tutor_migrated_course_count');
		$progress_text = sprintf(__('Migrated %s course', 'tutor'), $migrated_count);

		wp_send_json_success(array('progress_text' => $progress_text, 'migrated_count' => $migrated_count ));
	}



	public function migrate_course($course_id){
		global $wpdb;

		$course = learn_press_get_course($course_id);

		if ( ! $course){
			return;
		}

		$curriculum = $course->get_curriculum() ;

		$lesson_post_type = tutor()->lesson_post_type;
		$course_post_type = tutor()->course_post_type;

		$tutor_course = array();
		$i = 0;
		foreach ( $curriculum as $section ) {
			$i++;

			$topic = array(
				'post_type'     => 'topics',
				'post_title'    => $section->get_title(),
				'post_content'  => $section->get_description(),
				'post_status'   => 'publish',
				'post_author'   => $course->get_author('id'),
				'post_parent'   => $course_id,
				'menu_order'    => $i,
				'items'         => array()
			);

			$lessons = $section->get_items();
			foreach ($lessons as $lesson){
				$item_post_type = learn_press_get_post_type( $lesson->get_id() );

				if ($item_post_type !== 'lp_lesson'){
					if ($item_post_type === 'lp_quiz'){
						$lesson_post_type = 'tutor_quiz';
					}
				}

				$tutor_lessons = array(
					'ID'    => $lesson->get_id(),
					'post_type'    => $lesson_post_type,
					'post_parent'  => '{topic_id}',
				);

				$topic['items'][] = $tutor_lessons;
			}

			$tutor_course[] = $topic;
		}


		if (tutils()->count($tutor_course)){
			foreach ($tutor_course as $course_topic){

				//Remove items from this topic
				$lessons = $course_topic['items'];
				unset($course_topic['items']);

				//Insert Topic post type
				$topic_id = wp_insert_post( $course_topic );

				//Update lesson from LearnPress to TutorLMS
				foreach ($lessons as $lesson){

					if ($lesson['post_type'] === 'tutor_quiz'){
						$quiz_id = tutils()->array_get('ID', $lesson);

						$questions = $wpdb->get_results("SELECT question_id, question_order, questions.ID, questions.post_content, questions.post_title, question_type_meta.meta_value as question_type, question_mark_meta.meta_value as question_mark
						FROM {$wpdb->prefix}learnpress_quiz_questions 
						LEFT JOIN {$wpdb->posts} questions on question_id = questions.ID 
						LEFT JOIN {$wpdb->postmeta} question_type_meta on question_id = question_type_meta.post_id AND question_type_meta.meta_key = '_lp_type'
						LEFT JOIN {$wpdb->postmeta} question_mark_meta on question_id = question_mark_meta.post_id AND question_mark_meta.meta_key = '_lp_mark'
						WHERE quiz_id = {$quiz_id}  ");

						if (tutils()->count($questions)){
							foreach ($questions as $question) {

								$question_type = null;
								if ($question->question_type === 'true_or_false'){
									$question_type = 'true_false';
								}
								if ($question->question_type === 'single_choice'){
									$question_type = 'single_choice';
								}
								if ($question->question_type === 'multiple_choice'){
									$question_type = 'multi_choice';
								}

								if ($question_type) {

									$new_question_data = array(
										'quiz_id'              => $quiz_id,
										'question_title'       => $question->post_title,
										'question_description' => $question->post_content,
										'question_type'        => $question_type,
										'question_mark'        => $question->question_mark,
										'question_settings'    => maybe_serialize( array() ),
										'question_order'       => $question->question_order,
									);

									$wpdb->insert($wpdb->prefix.'tutor_quiz_questions', $new_question_data);
									$question_id = $wpdb->insert_id;

									$answer_items = $wpdb->get_results("SELECT * from {$wpdb->prefix}learnpress_question_answers where question_id = {$question->question_id} ");

									if (tutils()->count($answer_items)){
										foreach ($answer_items as $answer_item){
											$answer_data = maybe_unserialize($answer_item->answer_data);

											$answer_data = array(
												'belongs_question_id'   => $question_id,
												'belongs_question_type' => $question_type,
												'answer_title'          => tutils()->array_get('text', $answer_data),
												'is_correct'            => tutils()->array_get('is_true', $answer_data) == 'yes' ? 1 : 0,
												'answer_order'          => $answer_item->answer_order,
											);

											$wpdb->insert($wpdb->prefix.'tutor_quiz_question_answers', $answer_data);
										}
									}
								}

							}


						}

					}


					$lesson['post_parent'] = $topic_id;
					wp_update_post($lesson);

					$lesson_id = tutils()->array_get('ID', $lesson);
					if ($lesson_id){
						update_post_meta( $lesson_id, '_tutor_course_id_for_lesson', $course_id );
					}

					$_lp_preview = get_post_meta($lesson_id, '_lp_preview', true);
					if ($_lp_preview === 'yes'){
						update_post_meta($lesson_id, '_is_preview', 1);
					}else{
						delete_post_meta($lesson_id, '_is_preview');
					}


				}
			}
		}

		//Migrate Course
		$tutor_course = array(
			'ID'            => $course_id,
			'post_type'     => $course_post_type,
		);
		wp_update_post($tutor_course);
		update_post_meta($course_id, '_was_lp_course', true);

		/**
		 * Create WC Product and attaching it with course
		 */

		$_lp_price = get_post_meta($course_id, '_lp_price', true);
		$_lp_sale_price = get_post_meta($course_id, '_lp_sale_price', true);

		if ($_lp_price){
			update_post_meta($course_id, '_tutor_course_price_type', 'paid');

			$product_id = wp_insert_post( array(
				'post_title' => $course->get_title().' Product',
				'post_content' => '',
				'post_status' => 'publish',
				'post_type' => "product",
			) );
			if ($product_id) {
				$product_metas = array(
					'_stock_status'      => 'instock',
					'total_sales'        => '0',
					'_regular_price'     => $_lp_price,
					'_sale_price'        => $_lp_sale_price,
					'_price'             => $_lp_price,
					'_sold_individually' => 'no',
					'_manage_stock'      => 'no',
					'_backorders'        => 'no',
					'_stock'             => '',
					'_virtual'           => 'yes',
					'_tutor_product'     => 'yes',
				);
				foreach ( $product_metas as $key => $value ) {
					update_post_meta( $product_id, $key, $value );
				}
			}

			/**
			 * Attaching product to course
			 */
			update_post_meta( $course_id, '_tutor_course_product_id', $product_id );
			$coursePostThumbnail = get_post_meta( $course_id, '_thumbnail_id', true );
			if ( $coursePostThumbnail ) {
				set_post_thumbnail( $product_id, $coursePostThumbnail );
			}
		}else{
			update_post_meta($course_id, '_tutor_course_price_type', 'free');
		}

		/**
		 * Enrollment Migration to this course
		 */
		$lp_enrollments = $wpdb->get_results( "SELECT lp_user_items.*,
        lp_order.ID as order_id,
        lp_order.post_date as order_time
          
        FROM {$wpdb->prefix}learnpress_user_items lp_user_items  
        LEFT JOIN {$wpdb->posts} lp_order ON lp_user_items.ref_id = lp_order.ID
        WHERE item_id = {$course_id} AND item_type = 'lp_course' AND status = 'enrolled'" );

		foreach ($lp_enrollments as $lp_enrollment){
			$user_id = $lp_enrollment->user_id;

			if ( ! tutils()->is_enrolled($course_id, $user_id)) {
				$order_time = strtotime($lp_enrollment->order_time);

				$title = __('Course Enrolled', 'tutor')." &ndash; ".date( get_option('date_format'), $order_time ).' @ '.date( get_option( 'time_format'), $order_time );
				$tutor_enrollment_data = array(
					'post_type'   => 'tutor_enrolled',
					'post_title'  => $title,
					'post_status' => 'completed',
					'post_author' => $user_id,
					'post_parent' => $course_id,
				);

				$isEnrolled = wp_insert_post( $tutor_enrollment_data );

				if ($isEnrolled){
					//Mark Current User as Students with user meta data
					update_user_meta( $user_id, '_is_tutor_student', $order_time );
				}
			}
		}
	}

	public function tutor_option_save_after(){
		$maintenance_mode = (bool) get_tutor_option('enable_tutor_maintenance_mode');
		if ($maintenance_mode){
			tutor_maintenance_mode(true);
		}else{
			tutor_maintenance_mode();
		}
	}

	public function check_if_maintenance(){
		if ( ! is_admin()) {
			$maintenance_mode = (bool) get_tutor_option( 'enable_tutor_maintenance_mode' );
			if ( ! $maintenance_mode){
				return;
			}

			header( 'Retry-After: 600' );
			tutor_alert(__('Briefly unavailable for scheduled maintenance. Check back in a minute.', 'tutor'));
			die();
		}
	}


	public function migrate_lp_orders(){
		global $wpdb;

		$lp_orders = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type = 'lp_order' AND post_status = 'lp-completed' ;");

		foreach ($lp_orders as $lp_order){
			
			$order_id = $lp_order->ID;

			$migrate_order_data = array(
				'ID'    => $order_id,
				'post_status'    => 'wc-completed',
				'post_type'    => 'shop_order',
			);

			wp_update_post($migrate_order_data);

			$_items = $this->get_lp_order_items($order_id);

			foreach ($_items as $item){

				$item_data = array(
					'order_item_name'   => $item->name,
					'order_item_type'   => 'line_item',
					'order_id'          => $order_id,
				);

				$wpdb->insert($wpdb->prefix.'woocommerce_order_items', $item_data);
				$order_item_id = (int) $wpdb->insert_id;

				$lp_item_metas = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->prefix}learnpress_order_itemmeta WHERE learnpress_order_item_id = {$item->id} ");

				$lp_formatted_metas = array();
				foreach ($lp_item_metas as $item_meta) {
					$lp_formatted_metas[$item_meta->meta_key] = $item_meta->meta_value;
				}

				$_course_id = tutils()->array_get('_course_id', $lp_formatted_metas);
				$_quantity = tutils()->array_get('_quantity', $lp_formatted_metas);
				$_subtotal = tutils()->array_get('_subtotal', $lp_formatted_metas);
				$_total = tutils()->array_get('_total', $lp_formatted_metas);

				$wc_item_metas = array(
					'_product_id'        => $_course_id,
					'_variation_id'      => 0,
					'_qty'               => $_quantity,
					'_tax_class'         => '',
					'_line_subtotal'     => $_subtotal,
					'_line_subtotal_tax' => 0,
					'_line_total'        => $_total,
					'_line_tax'          => 0,
					'_line_tax_data'     => maybe_serialize( array( 'total' => array(), 'subtotal' => array() ) ),
				);

				foreach ($wc_item_metas as $wc_item_meta_key => $wc_item_meta_value ){
					$wc_item_metas = array(
						'order_item_id' => $order_item_id,
						'meta_key'      => $wc_item_meta_key,
						'meta_value'    => $wc_item_meta_value,
					);
					$wpdb->insert($wpdb->prefix.'woocommerce_order_itemmeta', $wc_item_metas);
				}

			}

			update_post_meta($order_id, '_customer_user', get_post_meta($order_id, '_user_id', true));
			update_post_meta($order_id, '_customer_ip_address', get_post_meta($order_id, '_user_ip_address', true));
			update_post_meta($order_id, '_customer_user_agent', get_post_meta($order_id, '_user_agent', true));

			$user_email = $wpdb->get_var("SELECT user_email from {$wpdb->users} WHERE ID = {$lp_order->post_author} ");
			update_post_meta($order_id, '_billing_address_index', $user_email );
			update_post_meta($order_id, '_billing_email', $user_email );
		}

	}

	public function migrate_lp_reviews(){
		global $wpdb;

		$lp_review_ids = $wpdb->get_col("SELECT comments.comment_ID FROM {$wpdb->comments} comments INNER JOIN {$wpdb->commentmeta} cm ON cm.comment_id = comments.comment_ID AND cm.meta_key = '_lpr_rating' WHERE comments.comment_type = 'review';");


		if (tutils()->count($lp_review_ids)){
			foreach ($lp_review_ids as $lp_review_id){
				$review_migrate_data = array(
					'comment_approved'  => 'approved',
					'comment_type'      => 'tutor_course_rating',
					'comment_agent'     => 'TutorLMSPlugin',
				);

				$wpdb->update($wpdb->comments, $review_migrate_data, array( 'comment_ID' => $lp_review_id));

				$wpdb->update($wpdb->commentmeta, array('meta_key' => 'tutor_rating'), array( 'comment_id' => $lp_review_id, 'meta_key' => '_lpr_rating' ));
				$wpdb->delete($wpdb->commentmeta, array('comment_id' => $lp_review_id, 'meta_key' => '_lpr_review_title'));
			}
		}


	}

	public function get_lp_order_items($order_id){
		global $wpdb;

		$query = $wpdb->prepare( "
			SELECT order_item_id as id, order_item_name as name 
				, oim.meta_value as `course_id`
				# , oim2.meta_value as `quantity`
				# , oim3.meta_value as `total`
			FROM {$wpdb->learnpress_order_items} oi 
				INNER JOIN {$wpdb->learnpress_order_itemmeta} oim ON oi.order_item_id = oim.learnpress_order_item_id AND oim.meta_key='_course_id'
				# INNER JOIN {$wpdb->learnpress_order_itemmeta} oim2 ON oi.order_item_id = oim2.learnpress_order_item_id AND oim2.meta_key='_quantity'
				# INNER JOIN {$wpdb->learnpress_order_itemmeta} oim3 ON oi.order_item_id = oim3.learnpress_order_item_id AND oim3.meta_key='_total'
			WHERE order_id = %d 
		", $order_id );

		return $wpdb->get_results( $query );
	}


}