<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;


class Utils {

	/**
	 * @param null $key
	 * @param bool $default
	 *
	 * @return array|bool|mixed
	 *
	 * Get option data
	 *
	 * @since v.1.0.0
	 */
	public function get_option($key = null, $default = false){
		$option = (array) maybe_unserialize(get_option('lms_option'));

		if (empty($option) || ! is_array($option)){
			return $default;
		}
		if ( ! $key){
			return $option;
		}
		if (array_key_exists($key, $option)){
			return $option[$key];
		}

		//Access array value via dot notation, such as option->get('value.subvalue')
		if (strpos($key, '.')){
			$option_key_array = explode('.', $key);

			$new_option = $option;
			foreach ($option_key_array as $dotKey){
				if (isset($new_option[$dotKey])){
					$new_option = $new_option[$dotKey];
				}else{
					return $default;
				}
			}

			return $new_option;
		}

		return $default;
	}

	/**
	 * @param null $key
	 * @param array $array
	 *
	 * @return array|bool|mixed
	 *
	 * get array value by dot notation
	 *
	 * @since v.1.0.0
	 *
	 */

	public function avalue_dot($key = null, $array = array()){
		$array = (array) $array;
		if ( ! $key || ! count($array) ){
			return false;
		}
		$option_key_array = explode('.', $key);

		$value = $array;

		foreach ($option_key_array as $dotKey){
			if (isset($value[$dotKey])){
				$value = $value[$dotKey];
			}else{
				return false;
			}
		}
		return $value;
	}

	/**
	 * @return array
	 *
	 * Get all pages
	 */
	public function get_pages(){
		$pages = array();
		$wp_pages = get_pages();
		if (is_array($wp_pages) && count($wp_pages)){
			foreach ($wp_pages as $page){
				$pages[$page->ID] = $page->post_title;
			}
		}
		return $pages;
	}

	/**
	 * @return string
	 *
	 * Get course archive URL
	 */
	public function course_archive_page_url(){
		$course_post_type = lms()->course_post_type;
		$course_page_url = trailingslashit(home_url()).$course_post_type;

		$course_archive_page = $this->get_option('course_archive_page');
		if ($course_archive_page && $course_archive_page !== '-1'){
			$course_page_url = get_permalink($course_archive_page);
		}
		return trailingslashit($course_page_url);
	}

	/**
	 * @param int $student_id
	 *
	 * @return string
	 *
	 * Get student URL
	 */

	public function student_url($student_id = 0){
		$site_url = trailingslashit(home_url()).'student/';
		$user_name = '';

		$student_id = $this->get_user_id($student_id);
		if ($student_id){

			$user = get_userdata($student_id);
			$user_name = $user->user_login;

		}else{
			$user_name = 'user_name';
		}

		return $site_url.$user_name;

	}

	/**
	 * @return bool
	 *
	 * Check if WooCommerce Activated
	 */

	public function has_wc(){
		return class_exists( 'woocommerce' );
	}

	/**
	 * @return mixed
	 *
	 *
	 */
	public function languages(){
		$language_codes = array(
			'en' => 'English' ,
			'aa' => 'Afar' ,
			'ab' => 'Abkhazian' ,
			'af' => 'Afrikaans' ,
			'am' => 'Amharic' ,
			'ar' => 'Arabic' ,
			'as' => 'Assamese' ,
			'ay' => 'Aymara' ,
			'az' => 'Azerbaijani' ,
			'ba' => 'Bashkir' ,
			'be' => 'Byelorussian' ,
			'bg' => 'Bulgarian' ,
			'bh' => 'Bihari' ,
			'bi' => 'Bislama' ,
			'bn' => 'Bengali/Bangla' ,
			'bo' => 'Tibetan' ,
			'br' => 'Breton' ,
			'ca' => 'Catalan' ,
			'co' => 'Corsican' ,
			'cs' => 'Czech' ,
			'cy' => 'Welsh' ,
			'da' => 'Danish' ,
			'de' => 'German' ,
			'dz' => 'Bhutani' ,
			'el' => 'Greek' ,
			'eo' => 'Esperanto' ,
			'es' => 'Spanish' ,
			'et' => 'Estonian' ,
			'eu' => 'Basque' ,
			'fa' => 'Persian' ,
			'fi' => 'Finnish' ,
			'fj' => 'Fiji' ,
			'fo' => 'Faeroese' ,
			'fr' => 'French' ,
			'fy' => 'Frisian' ,
			'ga' => 'Irish' ,
			'gd' => 'Scots/Gaelic' ,
			'gl' => 'Galician' ,
			'gn' => 'Guarani' ,
			'gu' => 'Gujarati' ,
			'ha' => 'Hausa' ,
			'hi' => 'Hindi' ,
			'hr' => 'Croatian' ,
			'hu' => 'Hungarian' ,
			'hy' => 'Armenian' ,
			'ia' => 'Interlingua' ,
			'ie' => 'Interlingue' ,
			'ik' => 'Inupiak' ,
			'in' => 'Indonesian' ,
			'is' => 'Icelandic' ,
			'it' => 'Italian' ,
			'iw' => 'Hebrew' ,
			'ja' => 'Japanese' ,
			'ji' => 'Yiddish' ,
			'jw' => 'Javanese' ,
			'ka' => 'Georgian' ,
			'kk' => 'Kazakh' ,
			'kl' => 'Greenlandic' ,
			'km' => 'Cambodian' ,
			'kn' => 'Kannada' ,
			'ko' => 'Korean' ,
			'ks' => 'Kashmiri' ,
			'ku' => 'Kurdish' ,
			'ky' => 'Kirghiz' ,
			'la' => 'Latin' ,
			'ln' => 'Lingala' ,
			'lo' => 'Laothian' ,
			'lt' => 'Lithuanian' ,
			'lv' => 'Latvian/Lettish' ,
			'mg' => 'Malagasy' ,
			'mi' => 'Maori' ,
			'mk' => 'Macedonian' ,
			'ml' => 'Malayalam' ,
			'mn' => 'Mongolian' ,
			'mo' => 'Moldavian' ,
			'mr' => 'Marathi' ,
			'ms' => 'Malay' ,
			'mt' => 'Maltese' ,
			'my' => 'Burmese' ,
			'na' => 'Nauru' ,
			'ne' => 'Nepali' ,
			'nl' => 'Dutch' ,
			'no' => 'Norwegian' ,
			'oc' => 'Occitan' ,
			'om' => '(Afan)/Oromoor/Oriya' ,
			'pa' => 'Punjabi' ,
			'pl' => 'Polish' ,
			'ps' => 'Pashto/Pushto' ,
			'pt' => 'Portuguese' ,
			'qu' => 'Quechua' ,
			'rm' => 'Rhaeto-Romance' ,
			'rn' => 'Kirundi' ,
			'ro' => 'Romanian' ,
			'ru' => 'Russian' ,
			'rw' => 'Kinyarwanda' ,
			'sa' => 'Sanskrit' ,
			'sd' => 'Sindhi' ,
			'sg' => 'Sangro' ,
			'sh' => 'Serbo-Croatian' ,
			'si' => 'Singhalese' ,
			'sk' => 'Slovak' ,
			'sl' => 'Slovenian' ,
			'sm' => 'Samoan' ,
			'sn' => 'Shona' ,
			'so' => 'Somali' ,
			'sq' => 'Albanian' ,
			'sr' => 'Serbian' ,
			'ss' => 'Siswati' ,
			'st' => 'Sesotho' ,
			'su' => 'Sundanese' ,
			'sv' => 'Swedish' ,
			'sw' => 'Swahili' ,
			'ta' => 'Tamil' ,
			'te' => 'Tegulu' ,
			'tg' => 'Tajik' ,
			'th' => 'Thai' ,
			'ti' => 'Tigrinya' ,
			'tk' => 'Turkmen' ,
			'tl' => 'Tagalog' ,
			'tn' => 'Setswana' ,
			'to' => 'Tonga' ,
			'tr' => 'Turkish' ,
			'ts' => 'Tsonga' ,
			'tt' => 'Tatar' ,
			'tw' => 'Twi' ,
			'uk' => 'Ukrainian' ,
			'ur' => 'Urdu' ,
			'uz' => 'Uzbek' ,
			'vi' => 'Vietnamese' ,
			'vo' => 'Volapuk' ,
			'wo' => 'Wolof' ,
			'xh' => 'Xhosa' ,
			'yo' => 'Yoruba' ,
			'zh' => 'Chinese' ,
			'zu' => 'Zulu' ,
		);

		return apply_filters('lms/utils/languages', $language_codes);
	}

	public function print_view($value = ''){
		echo '<pre>';
		print_r($value);
		echo '</pre>';
	}

	public function get_courses(){
		global $wpdb;

		$course_post_type = lms()->course_post_type;
		$query = $wpdb->get_results("SELECT ID, post_author, post_title, post_name,post_status, menu_order from {$wpdb->posts} WHERE post_status = 'publish' AND post_type = '{$course_post_type}' ");
		return $query;
	}

	public function get_course_count(){
		global $wpdb;

		$course_post_type = lms()->course_post_type;
		$count = $wpdb->get_var("SELECT COUNT(ID) from {$wpdb->posts} WHERE post_status = 'publish' AND post_type = '{$course_post_type}'; ");
		return $count;
	}

	public function get_lesson_count(){
		global $wpdb;

		$lesson_post_type = lms()->lesson_post_type;
		$count = $wpdb->get_var("SELECT COUNT(ID) from {$wpdb->posts} WHERE post_status = 'publish' AND post_type = '{$lesson_post_type}'; ");
		return $count;
	}

	public function get_lesson($course_id = 0){
		$course_id = $this->get_post_id($course_id);

		$lesson_post_type = lms()->lesson_post_type;
		$args = array(
			'post_status'  => 'publish',
			'post_type'  => $lesson_post_type,
			'meta_query' => array(
				array(
					'key'     => '_lms_course_id_for_lesson',
					'value'   => $course_id,
					'compare' => '=',
				),
			),
		);
		$query = new \WP_Query($args);

		return $query;
	}

	public function get_lesson_count_by_course($course_id = 0){
		$course_id = $this->get_post_id($course_id);
		global $wpdb;

		$count_lesson = $wpdb->get_var("select count(meta_id) from {$wpdb->postmeta} where meta_key = '_lms_course_id_for_lesson' AND meta_value = {$course_id} ");

		return (int) $count_lesson;
	}

	public function get_completed_lesson_count_by_course($course_id = 0, $user_id = 0){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);
		global $wpdb;

		$completed_lesson_ids = $wpdb->get_col("select post_id from {$wpdb->postmeta} where meta_key = '_lms_course_id_for_lesson' AND meta_value = {$course_id} ");

		$count = 0;
		if (is_array($completed_lesson_ids) && count($completed_lesson_ids)){
			$completed_lesson_meta_ids = array();
			foreach ($completed_lesson_ids as $lesson_id){
				$completed_lesson_meta_ids[] = '_lms_completed_lesson_id_'.$lesson_id;
			}
			$in_ids = implode("','", $completed_lesson_meta_ids);

			$count = (int) $wpdb->get_var("select count(umeta_id) from {$wpdb->usermeta} WHERE user_id = '{$user_id}' AND meta_key in('{$in_ids}') ");
		}

		return $count;
	}

	public function get_topics($course_id = 0){
		$course_id = $this->get_post_id($course_id);

		$args = array(
			'post_type'  => 'topics',
			'post_parent'  => $course_id,
			'orderby' => 'menu_order',
			'order'   => 'ASC',
		);

		$query = new \WP_Query($args);

		return $query;
	}

	public function get_lessons_by_topic($topics_id = 0){
		$topics_id = $this->get_post_id($topics_id);

		$lesson_post_type = lms()->lesson_post_type;
		$args = array(
			'post_type'  => $lesson_post_type,
			'post_parent'  => $topics_id,
			'orderby' => 'menu_order',
			'order'   => 'ASC',
		);

		$query = new \WP_Query($args);

		return $query;
	}

	public function checking_nonce($request_method = 'post'){
		if ($request_method === 'post'){
			if (!isset($_POST[lms()->nonce]) || !wp_verify_nonce($_POST[lms()->nonce], lms()->nonce_action)) {
				exit();
			}
		}else{
			if (!isset($_GET[lms()->nonce]) || !wp_verify_nonce($_GET[lms()->nonce], lms()->nonce_action)) {
				exit();
			}
		}
	}

	/**
	 * @param int $course_id
	 *
	 * @return bool
	 */
	public function is_course_purchasable($course_id = 0){
		if ( ! $this->has_wc()){
			return false;
		}
		$course_id = $this->get_post_id($course_id);
		$has_product_id = get_post_meta($course_id, '_product_id', true);
		if ($has_product_id){
			return true;
		}

		return false;
	}

	/**
	 * @param int $course_id
	 *
	 * @return array|bool|null|object|void
	 *
	 * Check if current user has been enrolled or not
	 */

	public function is_enrolled($course_id = 0, $user_id = 0){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);

		global $wpdb;

		$getEnrolledInfo = $wpdb->get_row("select ID, post_author, post_date,post_date_gmt,post_title from {$wpdb->posts} WHERE post_type = 'lms_enrolled' AND post_parent = {$course_id} AND post_author = {$user_id} ");

		if ($getEnrolledInfo){
			return $getEnrolledInfo;
		}

		return false;
	}

	/**
	 * @param int $lesson_id
	 * @param int $user_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get the course Enrolled confirmation by lesson ID
	 *
	 * @since v.1.0.0
	 */

	public function is_course_enrolled_by_lesson($lesson_id = 0, $user_id = 0){
		$lesson_id = $this->get_post_id($lesson_id);
		$user_id = $this->get_user_id($user_id);

		return $this->is_enrolled($this->get_course_id_by_lesson($lesson_id));
	}

	/**
	 * @param int $lesson_id
	 *
	 * @return bool|mixed
	 *
	 * Get the course ID by Lesson
	 *
	 * @since v.1.0.0
	 */
	public function get_course_id_by_lesson($lesson_id = 0){
		$lesson_id = $this->get_post_id($lesson_id);
		return get_post_meta($lesson_id, '_lms_course_id_for_lesson', true);
	}

	/**
	 * @param int $course_id
	 *
	 * @return bool|false|string
	 *
	 * Get first lesson of a course
	 *
	 * @since v.1.0.0
	 */
	public function get_course_first_lesson($course_id = 0){
		$course_id = $this->get_post_id($course_id);
		global $wpdb;

		$lessons = $wpdb->get_var(" select main_posts.ID from {$wpdb->posts} main_posts 
					WHERE  post_parent = 
					(SELECT sub_posts.ID FROM {$wpdb->posts} sub_posts 
					WHERE post_type = 'topics' AND 
					sub_posts.post_parent = {$course_id} ORDER BY sub_posts.menu_order ASC LIMIT 1 )  
					ORDER BY main_posts.menu_order ASC LIMIT 1 ;");

		if ($lessons){
			return get_permalink($lessons);
		}

		return false;
	}

	/*
	 *
	 * Get course sub pages in course dashboard
	 *
	 * @since v.1.0.0
	 */
	public function course_sub_pages(){
		$nav_items = array(
			'overview' => __('Overview', 'lms'),
			'content' => __('Content', 'lms'),
			'questions' => __('Questions', 'lms'),
			'announcements' => __('Announcements', 'lms'),
		);

		return apply_filters('lms_course/single/enrolled/nav_items', $nav_items);
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool|array
	 *
	 * @since v.1.0.0
	 */
	public function get_video($post_id = 0){
		$post_id = $this->get_post_id($post_id);
		$attachments = get_post_meta($post_id, '_video', true);
		if ($attachments) {
			$attachments = maybe_unserialize($attachments);
		}
		return $attachments;
	}

	/**
	 * @param int $post_id
	 * @param array $video_data
	 *
	 * @return bool
	 *
	 * Update the video Info
	 */
	public function update_video($post_id = 0, $video_data = array()){
		$post_id = $this->get_post_id($post_id);

		if (is_array($video_data) && count($video_data)){
			update_post_meta($post_id, '_video', $video_data);
		}
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool|mixed
	 *
	 * @since v.1.0.0
	 */
	public function get_attachments($post_id = 0){
		$post_id = $this->get_post_id($post_id);
		$attachments_arr = array();

		$attachments = maybe_unserialize(get_post_meta($post_id, '_lms_attachments', true));
		
		if ( is_array($attachments) && count($attachments)) {
			foreach ( $attachments as $attachment ) {
				$url       = wp_get_attachment_url( $attachment );
				$file_type = wp_check_filetype( $url );
				$ext       = $file_type['ext'];
				$title = get_the_title($attachment);

				$size_bytes = filesize( get_attached_file( $attachment ));
				$size = size_format( $size_bytes, 2 );

				$icon = includes_url("images/media/default.png");
				$type = wp_ext2type($ext);
				if ($type){
					$icon = includes_url("images/media/{$type}.png");
				}

				$data = array(
					'post_id'       => $post_id,
					'id'            => $attachment,
					'url'           => $url,
					'name'          => $title.'.'.$ext,
					'title'         => $title,
					'ext'           => $ext,
					'size'          => $size,
					'size_bytes'    => $size_bytes,
					'icon'          => $icon,
				);

				$attachments_arr[] = (object) apply_filters('lms/posts/attachments', $data);
			}
		}
		
		return $attachments_arr;
	}
	

	/**
	 * @param $seconds
	 *
	 * @return string
	 *
	 * return seconds to formatted playtime
	 *
	 * @since v.1.0.0
	 */
	public function playtime_string($seconds) {
		$sign = (($seconds < 0) ? '-' : '');
		$seconds = round(abs($seconds));
		$H = (int) floor( $seconds                            / 3600);
		$M = (int) floor(($seconds - (3600 * $H)            ) /   60);
		$S = (int) round( $seconds - (3600 * $H) - (60 * $M)        );
		return $sign.($H ? $H.':' : '').($H ? str_pad($M, 2, '0', STR_PAD_LEFT) : intval($M)).':'.str_pad($S, 2, 0, STR_PAD_LEFT);
	}

	/**
	 * @param $seconds
	 *
	 * @return array
	 *
	 * Get the playtime in array
	 */
	public function playtime_array($seconds){
		$run_time_format = array(
			'hours' => '00',
			'minutes' => '00',
			'seconds' => '00',
		);

		if ($seconds <= 0 ){
			return $run_time_format;
		}

		$playTimeString = $this->playtime_string($seconds);
		$timeInArray = explode(':', $playTimeString);

		$run_time_size = count($timeInArray);
		if ($run_time_size === 3){
			$run_time_format['hours'] = $timeInArray[0];
			$run_time_format['minutes'] = $timeInArray[1];
			$run_time_format['seconds'] = $timeInArray[2];
		}elseif($run_time_size === 2){
			$run_time_format['minutes'] = $timeInArray[0];
			$run_time_format['seconds'] = $timeInArray[1];
		}

		return $run_time_format;
	}

	/**
	 * @param int $lesson_id
	 *
	 * @return bool|object
	 *
	 * @since v.1.0.0
	 */

	public function get_video_info($lesson_id = 0){
		$lesson_id = $this->get_post_id($lesson_id);
		$video = $this->get_video($lesson_id);

		if ( ! $video){
			return false;
		}

		$info = array(
			'playtime' => '00:00',
		);

		$types = apply_filters('lms_video_types', array("mp4"=>"video/mp4", "webm"=>"video/webm", "ogg"=>"video/ogg"));

		$videoSource = $this->avalue_dot('source', $video);
		if ($videoSource === 'html5'){
			$sourceVideoID = $this->avalue_dot('source_video_id', $video);
			$video_info = get_post_meta($sourceVideoID, '_wp_attachment_metadata', true);

			if ($video_info){
				$path               = get_attached_file($sourceVideoID);
				$info['playtime']   = $video_info['length_formatted'];
				$info['path']       = $path;
				$info['ext']        = strtolower(pathinfo($path, PATHINFO_EXTENSION));
				$info['type']       = $types[$info['ext']];
			}
		}

		$info = array_merge($info, $video);

		return (object) $info;
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool
	 *
	 * Ensure if attached video is self hosted or not
	 */
	public function is_html5_video($post_id = 0){
		$post_id = $this->get_post_id($post_id);

		$video = $this->get_video($post_id);
		if ( ! $video){
			return false;
		}
		$videoSource = $this->avalue_dot('source', $video);
		return $videoSource === 'html5';
	}

	/**
	 * @param int $lesson_id
	 * @param int $user_id
	 *
	 * @return bool|mixed
	 *
	 * @since v.1.0.0
	 */

	public function is_completed_lesson($lesson_id = 0, $user_id = 0){
		$lesson_id = $this->get_post_id($lesson_id);
		$user_id = $this->get_user_id($user_id);

		$is_completed = get_user_meta($user_id, '_lms_completed_lesson_id_'.$lesson_id, true);

		if ($is_completed){
			return $is_completed;
		}

		return false;
	}


	public function is_completed_course($course_id = 0, $user_id = 0){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);

		$is_completed = get_user_meta($user_id, '_lms_completed_course_id_'.$course_id, true);

		if ($is_completed){
			return $is_completed;
		}

		return false;
	}

	/**
	 * @param array $input
	 *
	 * @return array
	 *
	 * Sanitize input array
	 */
	public function sanitize_array($input = array()){
		$array = array();

		if (is_array($input) && count($input)){
			foreach ($input as $key => $value){
				if (is_array($value)){
					$array[$key] = $this->sanitize_array($value);
				}else{
					$key = sanitize_text_field($key);
					$value = sanitize_text_field($value);
					$array[$key] = $value;
				}
			}
		}

		return $array;
	}
	
	public function has_video_in_single($post_id = 0){
		if (is_single()) {
			$post_id = $this->get_post_id($post_id);

			$video = $this->get_video( $post_id );
			if ( $video ) {
				return $video;
			}
		}
		return false;

	}

	/**
	 * @param int $start
	 * @param int $limit
	 * @param string $search_term
	 * @param int $course_id
	 *
	 * @return array|null|object
	 *
	 *
	 * Get the enrolled students for all courses.
	 *
	 * Pass course id in 4th parameter to get students course wise.
	 *
	 * @since v.1.0.0
	 */
	public function get_students($start = 0, $limit = 10, $search_term = '', $course_id = 0){
		$meta_key = '_is_lms_student';
		if ($course_id){
			$meta_key = '_lms_completed_course_id_'.$meta_key;
		}
		global $wpdb;


		if ($search_term){
			$search_term = " AND ( {$wpdb->users}.display_name LIKE '%{$search_term}%' OR {$wpdb->users}.user_email LIKE '%{$search_term}%' ) ";
		}

		$students = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS {$wpdb->users}.* FROM {$wpdb->users} 
			INNER JOIN {$wpdb->usermeta} 
			ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ) 
			WHERE 1=1 AND ( {$wpdb->usermeta}.meta_key = '{$meta_key}' )  {$search_term}
			ORDER BY {$wpdb->usermeta}.meta_value DESC 
			LIMIT {$start}, {$limit} ");

		return $students;
	}

	/**
	 * @return int
	 *
	 * @since v.1.0.0
	 *
	 * get the total students
	 * pass course id to get course wise total students
	 */
	public function get_total_students($search_term = '', $course_id = 0){
		$meta_key = '_is_lms_student';
		if ($course_id){
			$meta_key = '_lms_completed_course_id_'.$meta_key;
		}

		global $wpdb;

		if ($search_term){
			$search_term = " AND ( {$wpdb->users}.display_name LIKE '%{$search_term}%' OR {$wpdb->users}.user_email LIKE '%{$search_term}%' ) ";
		}

		$count = $wpdb->get_var("SELECT COUNT({$wpdb->users}.ID) FROM {$wpdb->users} INNER JOIN {$wpdb->usermeta} ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ) WHERE 1=1 AND ( {$wpdb->usermeta}.meta_key = '{$meta_key}' ) $search_term ");

		return (int) $count;
	}


	/**
	 * @param int $user_id
	 *
	 * @return bool|\WP_Query
	 *
	 * Return courses by user_id
	 */
	public function get_courses_by_user($user_id = 0){
		$user_id = $this->get_user_id($user_id);
		global $wpdb;

		$meta_key = '_lms_completed_course_id_';

		$course_id_query = $wpdb->get_col("select meta_key from {$wpdb->usermeta} WHERE user_id = {$user_id} AND meta_key LIKE '%{$meta_key}%' ");
		$course_ids = array();

		if (is_array($course_id_query) && count($course_id_query)){
			foreach ($course_id_query as $user_meta_col){
				$course_ids[] = str_replace($meta_key, '', $user_meta_col);
			}
		}else{
			return false;
		}

		if (count($course_ids)){
			$course_post_type = lms()->course_post_type;
			$course_args = array(
				'post_type' => $course_post_type,
				'post_status' => 'publish',
				'post__in'      => $course_ids,
			);

			return new \WP_Query($course_args);
		}

		return false;
	}

	/**
	 * @param int $post_id
	 *
	 * @return string
	 *
	 * Get the video streaming URL by post/lesson/course ID
	 */
	public function get_video_stream_url($post_id = 0){
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$post = get_post($post_id);

		$video_url = trailingslashit(home_url()).'video-url/'.$post->post_name;
		return $video_url;
	}

	/**
	 * @param int $lesson_id
	 * @param int $user_id
	 *
	 * @return array|bool|mixed
	 *
	 * Get student lesson reading current info
	 *
	 * @since v.1.0.0
	 */
	public function get_lesson_reading_info_full($lesson_id = 0, $user_id = 0){
		$lesson_id = $this->get_post_id($lesson_id);
		$user_id = $this->get_user_id($user_id);

		$lesson_info = (array) maybe_unserialize(get_user_meta($user_id, '_lesson_reading_info', true));
		return $this->avalue_dot($lesson_id, $lesson_info);
	}

	public function get_post_id($post_id = 0){
		if ( ! $post_id){
			$post_id = get_the_ID();
			if ( ! $post_id){
				return false;
			}
		}

		return $post_id;
	}

	public function get_user_id($user_id = 0){
		if ( ! $user_id){
			$user_id = get_current_user_id();
			if ( ! $user_id){
				return false;
			}
		}

		return $user_id;
	}

	/**
	 * @param int $lesson_id
	 * @param int $user_id
	 * @param string $key
	 *
	 * @return array|bool|mixed
	 *
	 * Get lesson reading info by key
	 *
	 * @since v.1.0.0
	 */

	public function get_lesson_reading_info($lesson_id = 0, $user_id = 0, $key = ''){
		$lesson_id = $this->get_post_id($lesson_id);
		$user_id = $this->get_user_id($user_id);

		$lesson_info = $this->get_lesson_reading_info_full($lesson_id, $user_id);

		return $this->avalue_dot($key, $lesson_info);
	}

	/**
	 * @param int $lesson_id
	 * @param int $user_id
	 * @param array $data
	 *
	 * @return bool
	 *
	 * Update student lesson reading info
	 *
	 * @since v.1.0.0
	 */
	public function update_lesson_reading_info($lesson_id = 0, $user_id = 0, $key = '', $value = ''){
		$lesson_id = $this->get_post_id($lesson_id);
		$user_id = $this->get_user_id($user_id);

		if ($key && $value){
			$lesson_info = (array) maybe_unserialize(get_user_meta($user_id, '_lesson_reading_info', true));
			$lesson_info[$lesson_id][$key] = $value;
			update_user_meta($user_id, '_lesson_reading_info', $lesson_info);
		}
	}

	/**
	 * @param string $url
	 *
	 * @return bool
	 *
	 * Get the Youtube Video ID from URL
	 *
	 * @since v.1.0.0
	 */
	public function get_youtube_video_id($url = ''){
		if (!$url){
			return false;
		}
		preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);

		if (isset($match[1])) {
			$youtube_id = $match[1];
			return $youtube_id;
		}

		return false;
	}

	/**
	 * @param string $url
	 *
	 * @return bool
	 *
	 * Get the vimeo video id from URL
	 *
	 * @since v.1.0.0
	 */
	public function get_vimeo_video_id($url = ''){
		if (preg_match('%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $url, $match)) {
			if (isset($match[3])){
				return $match[3];
			}
		}
		return false;
	}

	/**
	 * @param int $post_id
	 *
	 * Mark lesson complete
	 */
	public function mark_lesson_complete($post_id = 0, $user_id = 0){
		$post_id = $this->get_post_id($post_id);
		$user_id = $this->get_user_id($user_id);
		update_user_meta($user_id, '_lms_completed_lesson_id_'.$post_id, time());
	}

}