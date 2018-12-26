<?php
namespace TUTOR;

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
		$option = (array) maybe_unserialize(get_option('tutor_option'));

		if (empty($option) || ! is_array($option)){
			return $default;
		}
		if ( ! $key){
			return $option;
		}
		if (array_key_exists($key, $option)){
			return apply_filters($key, $option[$key]);
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
			return apply_filters($key, $new_option);
		}

		return $default;
	}

	/**
	 * @param null $key
	 * @param bool $value
	 *
	 * Update Option
	 */

	public function update_option($key = null, $value = false){
		$option = (array) maybe_unserialize(get_option('tutor_option'));
		$option[$key] = $value;
		update_option('tutor_option', $option);
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
		$course_post_type = tutor()->course_post_type;
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

	public function profile_url($student_id = 0){
		$site_url = trailingslashit(home_url()).'profile/';
		$user_name = '';

		$student_id = $this->get_user_id($student_id);
		if ($student_id){
			global $wpdb;
			$user = $wpdb->get_row("SELECT user_login from {$wpdb->users} WHERE ID = {$student_id} ");
			if ($user){
				$user_name = $user->user_login;
			}
		}else{
			$user_name = 'user_name';
		}

		return $site_url.$user_name;
	}

	/**
	 * @param string $user_login
	 *
	 * @return array|null|object
	 *
	 * Get user by user login
	 */
	public function get_user_by_login($user_login = ''){
		global $wpdb;
		$user_login = sanitize_text_field($user_login);
		$user = $wpdb->get_row("SELECT * from {$wpdb->users} WHERE user_login = '{$user_login}'");
		return $user;
	}

	/**
	 * @return bool
	 *
	 * Check if WooCommerce Activated
	 */

	public function has_wc(){
		$activated_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ));
		$depends = array('woocommerce/woocommerce.php', 'tutor-woocommerce/tutor-woocommerce.php');
		$has = count(array_intersect($depends, $activated_plugins)) == count($depends);

		return $has;
	}

	public function has_edd(){
		$activated_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ));
		$depends = array('easy-digital-downloads/easy-digital-downloads.php', 'tutor-edd/tutor-edd.php');
		$has = count(array_intersect($depends, $activated_plugins)) == count($depends);

		return $has;
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

		return apply_filters('tutor/utils/languages', $language_codes);
	}

	public function print_view($value = ''){
		echo '<pre>';
		print_r($value);
		echo '</pre>';
	}

	public function get_courses(){
		global $wpdb;

		$course_post_type = tutor()->course_post_type;
		$query = $wpdb->get_results("SELECT ID, post_author, post_title, post_name,post_status, menu_order 
				from {$wpdb->posts} WHERE post_status = 'publish'
				AND post_type = '{$course_post_type}' ");
		return $query;
	}

	public function get_courses_for_instructors($instructor_id = 0){
		global $wpdb;

		$instructor_id = $this->get_user_id($instructor_id);

		$course_post_type = tutor()->course_post_type;
		$query = $wpdb->get_results("SELECT ID, post_author, post_title, post_name,post_status, menu_order 
				from {$wpdb->posts} 
				WHERE post_author = {$instructor_id}
				AND post_status IN ('publish', 'pending')
				AND post_type = '{$course_post_type}' ");
		return $query;
	}

	public function get_course_count_by_instructor($instructor_id){
		global $wpdb;

		$course_post_type = tutor()->course_post_type;
		$count = $wpdb->get_var("SELECT COUNT(ID) from {$wpdb->posts} 
			INNER JOIN {$wpdb->usermeta} ON user_id = {$instructor_id} AND meta_key = '_tutor_instructor_course_id' AND meta_value = ID 
			WHERE post_status = 'publish' 
			AND post_type = '{$course_post_type}' ; ");

		return $count;
	}

	public function get_courses_by_instructor($instructor_id){
		global $wpdb;

		$course_post_type = tutor()->course_post_type;

		$querystr = "
	    SELECT $wpdb->posts.* 
	    FROM $wpdb->posts
		INNER JOIN {$wpdb->usermeta} ON $wpdb->usermeta.user_id = {$instructor_id} AND $wpdb->usermeta.meta_key = '_tutor_instructor_course_id' AND $wpdb->usermeta.meta_value = $wpdb->posts.ID 
	
	    
	    WHERE $wpdb->posts.post_status = 'publish' 
	    AND $wpdb->posts.post_type = '{$course_post_type}'
	    AND $wpdb->posts.post_date < NOW()
	    ORDER BY $wpdb->posts.post_date DESC";

		$pageposts = $wpdb->get_results($querystr, OBJECT);
		return $pageposts;
	}

	public function get_archive_page_course_count(){
		global $wp_query;
		return $wp_query->post_count;
	}

	public function get_course_count(){
		global $wpdb;

		$course_post_type = tutor()->course_post_type;
		$count = $wpdb->get_var("SELECT COUNT(ID) from {$wpdb->posts} WHERE post_status = 'publish' AND post_type = '{$course_post_type}'; ");
		return $count;
	}

	public function get_lesson_count(){
		global $wpdb;

		$lesson_post_type = tutor()->lesson_post_type;
		$count = $wpdb->get_var("SELECT COUNT(ID) from {$wpdb->posts} WHERE post_status = 'publish' AND post_type = '{$lesson_post_type}'; ");
		return $count;
	}

	public function get_lesson($course_id = 0, $limit = 10){
		$course_id = $this->get_post_id($course_id);

		$lesson_post_type = tutor()->lesson_post_type;
		$args = array(
			'post_status'  => 'publish',
			'post_type'  => $lesson_post_type,
			'posts_per_page'    => $limit,
			'meta_query' => array(
				array(
					'key'     => '_tutor_course_id_for_lesson',
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

		$count_lesson = $wpdb->get_var("select count(meta_id) from {$wpdb->postmeta} where meta_key = '_tutor_course_id_for_lesson' AND meta_value = {$course_id} ");

		return (int) $count_lesson;
	}

	public function get_completed_lesson_count_by_course($course_id = 0, $user_id = 0){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);
		global $wpdb;

		$completed_lesson_ids = $wpdb->get_col("select post_id from {$wpdb->postmeta} where meta_key = '_tutor_course_id_for_lesson' AND meta_value = {$course_id} ");

		$count = 0;
		if (is_array($completed_lesson_ids) && count($completed_lesson_ids)){
			$completed_lesson_meta_ids = array();
			foreach ($completed_lesson_ids as $lesson_id){
				$completed_lesson_meta_ids[] = '_tutor_completed_lesson_id_'.$lesson_id;
			}
			$in_ids = implode("','", $completed_lesson_meta_ids);

			$count = (int) $wpdb->get_var("select count(umeta_id) from {$wpdb->usermeta} WHERE user_id = '{$user_id}' AND meta_key in('{$in_ids}') ");
		}

		return $count;
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return float|int
	 *
	 */
	public function get_course_completed_percent($course_id = 0, $user_id = 0){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);

		$total_lesson = $this->get_lesson_count_by_course($course_id);
		$completed_lesson = $this->get_completed_lesson_count_by_course($course_id, $user_id);

		if ($total_lesson > 0 && $completed_lesson > 0){
			return number_format(($completed_lesson * 100) / $total_lesson, 1);
		}

		return 0;
	}

	public function get_topics($course_id = 0){
		$course_id = $this->get_post_id($course_id);

		$args = array(
			'post_type'  => 'topics',
			'post_parent'  => $course_id,
			'orderby' => 'menu_order',
			'order'   => 'ASC',
			'posts_per_page'    => -1,
		);

		$query = new \WP_Query($args);
		return $query;
	}

	public function get_next_topic_order_id($course_ID){
		global $wpdb;

		$last_order = (int) $wpdb->get_var("SELECT MAX(menu_order) FROM {$wpdb->posts} WHERE post_parent = {$course_ID} AND post_type = 'topics';");
		return $last_order + 1;
	}

	public function get_lessons_by_topic($topics_id = 0, $limit = 10){
		$topics_id = $this->get_post_id($topics_id);

		$lesson_post_type = tutor()->lesson_post_type;
		$args = array(
			'post_type'  => $lesson_post_type,
			'post_parent'  => $topics_id,
			'posts_per_page'    => $limit,
			'orderby' => 'menu_order',
			'order'   => 'ASC',
		);

		$query = new \WP_Query($args);

		return $query;
	}

	public function checking_nonce($request_method = 'post'){
		if ($request_method === 'post'){
			if (!isset($_POST[tutor()->nonce]) || !wp_verify_nonce($_POST[tutor()->nonce], tutor()->nonce_action)) {
				exit();
			}
		}else{
			if (!isset($_GET[tutor()->nonce]) || !wp_verify_nonce($_GET[tutor()->nonce], tutor()->nonce_action)) {
				exit();
			}
		}
	}

	/**
	 * @param int $course_id
	 *
	 * @return bool
	 *
	 * @since v.1.0.0
	 */
	public function is_course_purchasable($course_id = 0){
		return apply_filters('is_course_purchasable', false, $course_id);
	}

	/**
	 * @param int $course_id
	 *
	 * @return null|string
	 *
	 * get course price in digits format if any
	 *
	 * @since v.1.0.0
	 */

	public function get_course_price($course_id = 0){
		$course_id = $this->get_post_id($course_id);

		$price = null;

		if ($this->is_course_purchasable()) {
			if ($this->has_wc()){
				$product_id = tutor_utils()->get_course_product_id($course_id);
				$product    = wc_get_product( $product_id );

				if ( $product ) {
					$price = $product->get_price();
				}
			}else{
				$price = apply_filters('get_tutor_course_price', null, $course_id);
			}

		}

		return $price;
	}

	/**
	 * @param int $course_id
	 *
	 * @return array|bool|null|object
	 *
	 * Check if current user has been enrolled or not
	 *
	 * @since v.1.0.0
	 */

	public function is_enrolled($course_id = 0, $user_id = 0){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);

		if (is_user_logged_in()) {
			global $wpdb;

			$getEnrolledInfo = $wpdb->get_row( "select ID, post_author, post_date,post_date_gmt,post_title from {$wpdb->posts} WHERE post_type = 'tutor_enrolled' AND post_parent = {$course_id} AND post_author = {$user_id} AND post_status = 'completed'; " );

			if ( $getEnrolledInfo ) {
				return $getEnrolledInfo;
			}
		}
		return false;
	}

	public function has_any_enrolled($course_id = 0, $user_id = 0){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);

		if (is_user_logged_in()) {
			global $wpdb;

			$getEnrolledInfo = $wpdb->get_row( "select ID, post_author, post_date,post_date_gmt,post_title from {$wpdb->posts} WHERE post_type = 'tutor_enrolled' AND post_parent = {$course_id} AND post_author = {$user_id}; " );

			if ( $getEnrolledInfo ) {
				return $getEnrolledInfo;
			}
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
		return get_post_meta($lesson_id, '_tutor_course_id_for_lesson', true);
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
			'overview' => __('Overview', 'tutor'),
		);

		$enable_q_and_a_on_course = tutor_utils()->get_option('enable_q_and_a_on_course');
		if ($enable_q_and_a_on_course){
			$nav_items['questions'] = __('Q&A', 'tutor');
		}
		$nav_items['announcements'] = __('Announcements', 'tutor');

		return apply_filters('tutor_course/single/enrolled/nav_items', $nav_items);
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
		$attachments = maybe_unserialize(get_post_meta($post_id, '_tutor_attachments', true));
		
		
		$font_icons = apply_filters('tutor_file_types_icon', array(
			'archive',
			'audio',
			'code',
			'default',
			'document',
			'interactive',
			'spreadsheet',
			'text',
			'video',
			'image',
		));
		

		if ( is_array($attachments) && count($attachments)) {
			foreach ( $attachments as $attachment ) {
				$url       = wp_get_attachment_url( $attachment );
				$file_type = wp_check_filetype( $url );
				$ext       = $file_type['ext'];
				$title = get_the_title($attachment);

				$size_bytes = filesize( get_attached_file( $attachment ));
				$size = size_format( $size_bytes, 2 );

				$type = wp_ext2type($ext);

				$icon = 'default';
				if ($type && in_array($type, $font_icons)){
					$icon = $type;
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

				$attachments_arr[] = (object) apply_filters('tutor/posts/attachments', $data);
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

	public function seconds_to_time_context($seconds) {
		$sign = (($seconds < 0) ? '-' : '');
		$seconds = round(abs($seconds));
		$H = (int) floor( $seconds                            / 3600);
		$M = (int) floor(($seconds - (3600 * $H)            ) /   60);
		$S = (int) round( $seconds - (3600 * $H) - (60 * $M)        );

		return $sign.($H ? $H.'h ' : '').($H ? str_pad($M, 2, '0', STR_PAD_LEFT) : intval($M)).'m '.str_pad($S, 2, 0, STR_PAD_LEFT).'s';
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

		$types = apply_filters('tutor_video_types', array("mp4"=>"video/mp4", "webm"=>"video/webm", "ogg"=>"video/ogg"));

		$videoSource = $this->avalue_dot('source', $video);
		if ($videoSource === 'html5'){
			$sourceVideoID = $this->avalue_dot('source_video_id', $video);
			$video_info = get_post_meta($sourceVideoID, '_wp_attachment_metadata', true);

			if ($video_info){
				$path               = get_attached_file($sourceVideoID);
				$info['playtime']   = $video_info['length_formatted'];
				$info['path']       = $path;
				$info['url']        = wp_get_attachment_url($sourceVideoID);
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

		$is_completed = get_user_meta($user_id, '_tutor_completed_lesson_id_'.$lesson_id, true);

		if ($is_completed){
			return $is_completed;
		}

		return false;
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return array|bool|null|object|void
	 *
	 * Determine if a course completed
	 */

	public function is_completed_course($course_id = 0, $user_id = 0){
		global $wpdb;
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);

		$is_completed = $wpdb->get_row("SELECT comment_ID, 
		comment_post_ID as course_id, 
		comment_author as completed_user_id, 
		comment_date as completion_date, 
		comment_content as completed_hash 
		from {$wpdb->comments} 
		WHERE comment_agent = 'TutorLMSPlugin' 
		AND comment_type = 'course_completed' 
		AND comment_post_ID = {$course_id} 
		AND user_id = {$user_id} ;");

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
	public function get_students($start = 0, $limit = 10, $search_term = ''){
		$meta_key = '_is_tutor_student';

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
	public function get_total_students($search_term = ''){
		$meta_key = '_is_tutor_student';

		global $wpdb;

		if ($search_term){
			$search_term = " AND ( {$wpdb->users}.display_name LIKE '%{$search_term}%' OR {$wpdb->users}.user_email LIKE '%{$search_term}%' ) ";
		}

		$count = $wpdb->get_var("SELECT COUNT({$wpdb->users}.ID) FROM {$wpdb->users} INNER JOIN {$wpdb->usermeta} ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ) WHERE 1=1 AND ( {$wpdb->usermeta}.meta_key = '{$meta_key}' ) $search_term ");

		return (int) $count;
	}

	public function get_completed_courses_ids_by_user($user_id = 0){
		global $wpdb;

		$user_id = $this->get_user_id($user_id);

		$course_ids = (array) $wpdb->get_col("SELECT comment_post_ID as course_id
		from {$wpdb->comments} 
		WHERE comment_agent = 'TutorLMSPlugin' 
		AND comment_type = 'course_completed' 
		AND user_id = {$user_id} ;");

		return $course_ids;
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
		$course_ids = $this->get_completed_courses_ids_by_user($user_id);

		if (count($course_ids)){
			$course_post_type = tutor()->course_post_type;
			$course_args = array(
				'post_type'     => $course_post_type,
				'post_status'   => 'publish',
				'post__in'      => $course_ids,
			);

			return new \WP_Query($course_args);
		}

		return false;
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool|\WP_Query
	 *
	 * Get the active course by user
	 */

	public function get_active_courses_by_user($user_id = 0){
		$user_id = $this->get_user_id($user_id);

		$course_ids = $this->get_completed_courses_ids_by_user($user_id);
		$enrolled_course_ids = $this->get_enrolled_courses_ids_by_user($user_id);
		$active_courses = array_diff($enrolled_course_ids, $course_ids);

		if (count($active_courses)){
			$course_post_type = tutor()->course_post_type;
			$course_args = array(
				'post_type'     => $course_post_type,
				'post_status'   => 'publish',
				'post__in'      => $active_courses,
			);

			return new \WP_Query($course_args);
		}

		return false;
	}

	public function get_enrolled_courses_ids_by_user($user_id = 0){
		global $wpdb;
		$user_id = $this->get_user_id($user_id);
		$course_ids = $wpdb->get_col("select post_parent from {$wpdb->posts} WHERE post_type = 'tutor_enrolled' AND post_author = {$user_id} AND post_status = 'completed'; ");

		return $course_ids;
	}

	/**
	 * @param int $course_id
	 *
	 * @return int
	 *
	 * Get the total enrolled users at course
	 */
	public function count_enrolled_users_by_course($course_id = 0){
		global $wpdb;
		$course_id = $this->get_post_id($course_id);

		$course_ids = $wpdb->get_var("select COUNT(ID) from {$wpdb->posts} WHERE post_type = 'tutor_enrolled' AND post_parent = {$course_id} AND post_status = 'completed'; ");

		return (int) $course_ids;
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool|\WP_Query
	 *
	 * Get the enrolled courses by user
	 */
	public function get_enrolled_courses_by_user($user_id = 0){
		global $wpdb;

		$user_id = $this->get_user_id($user_id);
		$course_ids = $this->get_enrolled_courses_ids_by_user($user_id);

		if (count($course_ids)){
			$course_post_type = tutor()->course_post_type;
			$course_args = array(
				'post_type'     => $course_post_type,
				'post_status'   => 'publish',
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
		$post_id = $this->get_post_id($post_id);
		$post = get_post($post_id);

		if ($post->post_type === tutor()->lesson_post_type ){
			$video_url = trailingslashit(home_url()).'video-url/'.$post->post_name;
		}else{
			$video_info = tutor_utils()->get_video_info($post_id);
			$video_url =  $video_info->url;
		}

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
		update_user_meta($user_id, '_tutor_completed_lesson_id_'.$post_id, time());
	}

	/**
	 * Saving enroll information to posts table
	 * post_author = enrolled_student_id (wp_users id)
	 * post_parent = enrolled course id
	 *
	 * @type: call when need
	 * @return bool;
	 */
	public function do_enroll($course_id = 0, $order_id = 0){
		if ( ! $course_id){
			return false;
		}

		do_action('tutor_before_enroll', $course_id);
		$user_id = get_current_user_id();
		$title = __('Course Enrolled', 'tutor')." &ndash; ".date_i18n(get_option('date_format')) .' @ '.date_i18n(get_option('time_format') ) ;

		$enrolment_status = 'completed';

		if ($this->is_course_purchasable($course_id)) {
			/**
			 * We need to verify this enrollment, we will change the status later after payment confirmation
			 */
			$enrolment_status = 'pending';
		}

		$enroll_data = apply_filters('tutor_enroll_data',
			array(
				'post_type'     => 'tutor_enrolled',
				'post_title'    => $title,
				'post_status'   => $enrolment_status,
				'post_author'   => $user_id,
				'post_parent'   => $course_id,
			)
		);

		// Insert the post into the database
		$isEnrolled = wp_insert_post( $enroll_data );
		if ($isEnrolled) {
			do_action('tutor_after_enroll', $course_id, $isEnrolled);

			//Mark Current User as Students with user meta data
			update_user_meta( $user_id, '_is_tutor_student', time() );

			if ($order_id) {
				//Mark order for course and user
				$product_id = $this->get_course_product_id($course_id);
				update_post_meta( $isEnrolled, '_tutor_enrolled_by_order_id', $order_id );
				update_post_meta( $isEnrolled, '_tutor_enrolled_by_product_id', $product_id );
				update_post_meta( $order_id, '_is_tutor_order_for_course', time() );
				update_post_meta( $order_id, '_tutor_order_for_course_id_'.$course_id, $isEnrolled );
			}
			return true;
		}

		return false;
	}

	public function complete_course_enroll($order_id){
		if ( ! tutor_utils()->is_tutor_order($order_id)){
			return;
		}

		global $wpdb;

		$enrolled_ids_with_course = $this->get_course_enrolled_ids_by_order_id($order_id);
		if ($enrolled_ids_with_course){
			$enrolled_ids = wp_list_pluck($enrolled_ids_with_course, 'enrolled_id');

			if (is_array($enrolled_ids) && count($enrolled_ids)){
				foreach ($enrolled_ids as $enrolled_id){
					$wpdb->update( $wpdb->posts, array( 'post_status' => 'completed' ), array( 'ID' => $enrolled_id ) );
				}
			}
		}
	}

	/**
	 * @param $order_id
	 *
	 * @return array|bool
	 */
	public function get_course_enrolled_ids_by_order_id($order_id){
		global $wpdb;
		//Getting all of courses ids within this order

		$courses_ids = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id = {$order_id} AND meta_key LIKE '_tutor_order_for_course_id_%' ");

		if (is_array($courses_ids) && count($courses_ids)){
			$course_enrolled_by_order = array();
			foreach ($courses_ids as $courses_id){
				$course_id = str_replace('_tutor_order_for_course_id_', '',$courses_id->meta_key);
				//array(order_id =>  array('course_id' => $course_id, 'enrolled_id' => enrolled_id))
				$course_enrolled_by_order[$courses_id->post_id] = array('course_id' => $course_id, 'enrolled_id' => $courses_id->meta_value);
			}
			return $course_enrolled_by_order;
		}
		return false;
	}

	/**
	 * Get wc product in efficient query
	 *
	 * @since v.1.0.0
	 */

	/**
	 * @return array|null|object
	 *
	 * WooCommerce specific utils
	 */
	public function get_wc_products_db(){
		global $wpdb;
		$query = $wpdb->get_results("SELECT ID, post_title from {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'product' ");

		return $query;
	}

	public function get_course_product_id($course_id = 0){
		$course_id = $this->get_post_id($course_id);
		return (int) get_post_meta($course_id, '_tutor_course_product_id', true);
	}

	public function product_belongs_with_course($product_id = 0){
		global $wpdb;

		$query = $wpdb->get_row("select * from {$wpdb->postmeta} WHERE meta_key='_tutor_course_product_id' AND meta_value = {$product_id} limit 1 ");
		return $query;
	}

	/**
	 * #End WooCommerce specific utils
	 */

	public function get_enrolled_statuses(){
		return array (
			'pending',
			'processing',
			'on-hold',
			'completed',
			'cancelled',
			'refunded',
			'failed',
		);
	}

	public function is_tutor_order($order_id){
		return get_post_meta($order_id, '_is_tutor_order_for_course', true);
	}

	/**
	 * @return mixed
	 *
	 * Tutor Dashboard Pages
	 */

	public function tutor_student_dashboard_pages(){
		$nav_items = array(
			'index' => __('Home', 'tutor'),
			'my-courses' => __('My Courses', 'tutor'),
			'active-courses' => __('Active Courses', 'tutor'),
			'completed-courses' => __('Completed Courses', 'tutor'),
			'wishlist' => __('WishList', 'tutor'),
		);

		return apply_filters('tutor_dashboard/student/pages', $nav_items);
	}


	public function get_tutor_dashboard_page_permalink($page_key = '', $page_id = 0){
		if ($page_key === 'index'){
			$page_key = '';
		}
		$page_id = $this->get_post_id($page_id);
		return trailingslashit(get_permalink($page_id)).$page_key;
	}

	public function input_old($input = ''){
		$value = $this->avalue_dot($input, $_REQUEST);
		if ($value){
			return $value;
		}
		return '';
	}

	/**
	 * @param int $user_id
	 *
	 * @return mixed
	 *
	 * Determine if is instructor or not
	 *
	 * @since v.1.0.0
	 */
	public function is_instructor($user_id = 0){
		$user_id = $this->get_user_id($user_id);
		return get_user_meta($user_id, '_is_tutor_instructor', true);
	}

	public function instructor_status($user_id = 0, $status_name = true){
		$user_id = $this->get_user_id($user_id);

		$instructor_status = apply_filters('tutor_instructor_statuses', array(
			'pending' => __('Pending', 'tutor'),
			'approved' => __('Approved', 'tutor'),
			'blocked' => __('Blocked', 'tutor'),
		));

		$status = get_user_meta($user_id, '_tutor_instructor_status', true);

		if (isset($instructor_status[$status])){
			if ( ! $status_name){
				return $status;
			}
			return $instructor_status[$status];
		}
		return false;
	}


	public function get_total_instructors($search_term = ''){
		$meta_key = '_is_tutor_instructor';

		global $wpdb;

		if ($search_term){
			$search_term = " AND ( {$wpdb->users}.display_name LIKE '%{$search_term}%' OR {$wpdb->users}.user_email LIKE '%{$search_term}%' ) ";
		}

		$count = $wpdb->get_var("SELECT COUNT({$wpdb->users}.ID) FROM {$wpdb->users} INNER JOIN {$wpdb->usermeta} ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ) WHERE 1=1 AND ( {$wpdb->usermeta}.meta_key = '{$meta_key}' ) $search_term ");

		return (int) $count;
	}

	public function get_instructors($start = 0, $limit = 10, $search_term = ''){
		$meta_key = '_is_tutor_instructor';
		global $wpdb;

		if ($search_term){
			$search_term = " AND ( {$wpdb->users}.display_name LIKE '%{$search_term}%' OR {$wpdb->users}.user_email LIKE '%{$search_term}%' ) ";
		}

		$instructors = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS {$wpdb->users}.* FROM {$wpdb->users} 
			INNER JOIN {$wpdb->usermeta} 
			ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ) 
			WHERE 1=1 AND ( {$wpdb->usermeta}.meta_key = '{$meta_key}' )  {$search_term}
			ORDER BY {$wpdb->usermeta}.meta_value DESC 
			LIMIT {$start}, {$limit} ");

		return $instructors;
	}

	public function get_instructors_by_course($course_id = 0){
		global $wpdb;
		$course_id = $this->get_post_id($course_id);

		$instructors = $wpdb->get_results("select ID, display_name, 
			get_course.meta_value as taught_course_id,
			tutor_job_title.meta_value as tutor_profile_job_title, 
			tutor_bio.meta_value as tutor_profile_bio,
			tutor_photo.meta_value as tutor_profile_photo
			from {$wpdb->users}
			INNER JOIN {$wpdb->usermeta} get_course ON ID = get_course.user_id AND get_course.meta_value = {$course_id}
			LEFT JOIN {$wpdb->usermeta} tutor_job_title ON ID = tutor_job_title.user_id AND tutor_job_title.meta_key = '_tutor_profile_job_title'
			LEFT JOIN {$wpdb->usermeta} tutor_bio ON ID = tutor_bio.user_id AND tutor_bio.meta_key = '_tutor_profile_bio'
			LEFT JOIN {$wpdb->usermeta} tutor_photo ON ID = tutor_photo.user_id AND tutor_photo.meta_key = '_tutor_profile_photo'
			");

		if (is_array($instructors) && count($instructors)){
			return $instructors;
		}

		return false;
	}

	/**
	 * @param $instructor_id
	 *
	 * Get total Students by instructor
	 * 1 enrollment = 1 student, so total enrolled for a equivalent total students (Tricks)
	 *
	 * @since v.1.0.0
	 */
	public function get_total_students_by_instructor($instructor_id){
		global $wpdb;

		$course_post_type = tutor()->course_post_type;
		$count = $wpdb->get_var("SELECT COUNT(courses.ID) from {$wpdb->posts} courses

			INNER JOIN {$wpdb->posts} enrolled ON courses.ID = enrolled.post_parent AND enrolled.post_type = 'tutor_enrolled'
			WHERE courses.post_status = 'publish' 
			AND courses.post_type = '{$course_post_type}' 
			AND courses.post_author = {$instructor_id}  ; ");
		return (int) $count;
	}

	/**
	 * @param float $input
	 *
	 * @return float|string
	 *
	 * Get rating format from value
	 */
	public function get_rating_value($input = 0.00){

		if ( $input > 0){
			$input = number_format($input, 2);
			$int_value = (int) $input;
			$fraction = $input - $int_value;

			if ($fraction == 0){
				$fraction = 0.00;
			}elseif($fraction > 0.5){
				$fraction = 1;
			}else{
				$fraction = 0.5;
			}

			return number_format( ($int_value + $fraction), 2);
		}
		return 0.00;
	}

	/**
	 * @param float $current_rating
	 * @param bool $echo
	 *
	 * @return string
	 *
	 * Generate star rating based in given rating value
	 */
	public function star_rating_generator($current_rating = 0.00, $echo = true){
		$output = '';

		for ($i = 1; $i <=5 ; $i++){
			$intRating = (int) $current_rating;

			if ($intRating >= $i){
				$output.= '<i class="tutor-icon-star-full" data-rating-value="'.$i.'"></i>';
			} else{
				if ( ($current_rating - $i) == -0.5){
					$output.= '<i class="tutor-icon-star-half" data-rating-value="'.$i.'"></i>';
				}else{
					$output.= '<i class="tutor-icon-star-line" data-rating-value="'.$i.'"></i>';
				}
			}
		}

		if ($echo){
			echo $output;
		}
		return $output;
	}

	/**
	 * @param null $name
	 *
	 * @return string
	 *
	 * Generate text to avatar
	 */
	public function get_tutor_avatar($user_id = null, $size = 'thumbnail'){
		global $wpdb;

		if ( ! $user_id){
			return '';
		}

		$user = $this->get_tutor_user($user_id);
		if ($user->tutor_profile_photo){
			return '<img src="'.wp_get_attachment_image_url($user->tutor_profile_photo, $size).'" class="tutor-image-avatar" alt="" /> ';
		}

		$name = $user->display_name;
		$arr = explode(' ', trim($name));

		if (count($arr) > 1){
			$first_char = substr($arr[0], 0, 1) ;
			$second_char = substr($arr[1], 0, 1) ;
		}else{
			$first_char = substr($arr[0], 0, 1) ;
			$second_char = substr($arr[0], 1, 1) ;
		}

		$initial_avatar = strtoupper($first_char.$second_char);

		$bg_color = '#'.substr(md5($initial_avatar), 0, 6);
		$initial_avatar = "<span class='tutor-text-avatar' style='background-color: {$bg_color}; color: #fff8e5'>{$initial_avatar}</span>";

		return $initial_avatar;
	}

	public function get_tutor_user($user_id){
		global $wpdb;

		$user = $wpdb->get_row("select ID, display_name, 
			tutor_job_title.meta_value as tutor_profile_job_title, 
			tutor_bio.meta_value as tutor_profile_bio,
			tutor_photo.meta_value as tutor_profile_photo
			
			from {$wpdb->users}
			LEFT JOIN {$wpdb->usermeta} tutor_job_title ON ID = tutor_job_title.user_id AND tutor_job_title.meta_key = '_tutor_profile_job_title'
			LEFT JOIN {$wpdb->usermeta} tutor_bio ON ID = tutor_bio.user_id AND tutor_bio.meta_key = '_tutor_profile_bio'
			LEFT JOIN {$wpdb->usermeta} tutor_photo ON ID = tutor_photo.user_id AND tutor_photo.meta_key = '_tutor_profile_photo'
			
			WHERE ID = {$user_id} ");
		return $user;
	}

	/**
	 * @param int $course_id
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array|null|object
	 *
	 * get course reviews
	 *
	 * @since v.1.0.0
	 */
	public function get_course_reviews($course_id = 0, $offset = 0, $limit = 150){
		$course_id = $this->get_post_id($course_id);
		global $wpdb;

		$reviews = $wpdb->get_results("select {$wpdb->comments}.comment_ID, 
			{$wpdb->comments}.comment_post_ID, 
			{$wpdb->comments}.comment_author, 
			{$wpdb->comments}.comment_author_email, 
			{$wpdb->comments}.comment_date, 
			{$wpdb->comments}.comment_content, 
			{$wpdb->comments}.user_id, 
			{$wpdb->commentmeta}.meta_value as rating,
			{$wpdb->users}.display_name 
			
			from {$wpdb->comments}
			INNER JOIN {$wpdb->commentmeta} 
			ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
			INNER  JOIN {$wpdb->users}
			ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			WHERE {$wpdb->comments}.comment_post_ID = {$course_id} 
			AND meta_key = 'tutor_rating' ORDER BY comment_ID DESC LIMIT {$offset},{$limit} ;"
		);

		return $reviews;
	}

	/**
	 * @param int $course_id
	 *
	 * @return object
	 *
	 * Get course rating
	 */
	public function get_course_rating($course_id = 0){
		$course_id = $this->get_post_id($course_id);

		$ratings = array(
			'rating_count'  => 0,
			'rating_sum'    => 0,
			'rating_avg'    => 0.00,
		);

		global $wpdb;

		$rating = $wpdb->get_row("select COUNT(meta_value) as rating_count, SUM(meta_value) as rating_sum from {$wpdb->comments}
			INNER JOIN {$wpdb->commentmeta} 
			ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
			WHERE {$wpdb->comments}.comment_post_ID = {$course_id} 
			AND meta_key = 'tutor_rating' ;"
		);

		if ($rating->rating_count){
			$avg_rating = number_format(($rating->rating_sum / $rating->rating_count), 2);

			$ratings = array(
				'rating_count'  => $rating->rating_count,
				'rating_sum'    => $rating->rating_sum,
				'rating_avg'    => $avg_rating,
			);
		}

		return (object) $ratings;
	}


	public function get_reviews_by_user($user_id = 0, $offset = 0, $limit = 150){
		$user_id = $this->get_user_id($user_id);
		global $wpdb;

		$reviews = $wpdb->get_results("select {$wpdb->comments}.comment_ID, 
			{$wpdb->comments}.comment_post_ID, 
			{$wpdb->comments}.comment_author, 
			{$wpdb->comments}.comment_author_email, 
			{$wpdb->comments}.comment_date, 
			{$wpdb->comments}.comment_content, 
			{$wpdb->comments}.user_id, 
			{$wpdb->commentmeta}.meta_value as rating,
			{$wpdb->users}.display_name 
			
			from {$wpdb->comments}
			INNER JOIN {$wpdb->commentmeta} 
			ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
			INNER  JOIN {$wpdb->users}
			ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			WHERE {$wpdb->comments}.user_id = {$user_id} 
			AND meta_key = 'tutor_rating' ORDER BY comment_ID DESC LIMIT {$offset},{$limit} ;"
		);

		return $reviews;
	}

	/**
	 * @param $instructor_id
	 *
	 * @return object
	 *
	 * Get instructors rating
	 */
	public function get_instructor_ratings($instructor_id){
		global $wpdb;

		$ratings = array(
			'rating_count'  => 0,
			'rating_sum'    => 0,
			'rating_avg'    => 0.00,
		);

		$rating = $wpdb->get_row("SELECT COUNT(rating.meta_value) as rating_count, SUM(rating.meta_value) as rating_sum  
		FROM {$wpdb->usermeta} courses
		INNER JOIN {$wpdb->comments} reviews ON courses.meta_value = reviews.comment_post_ID AND reviews.comment_type = 'tutor_course_rating'
		INNER JOIN {$wpdb->commentmeta} rating ON reviews.comment_ID = rating.comment_id AND rating.meta_key = 'tutor_rating'
		WHERE courses.user_id = {$instructor_id} AND courses.meta_key = '_tutor_instructor_course_id'");

		if ($rating->rating_count){
			$avg_rating = number_format(($rating->rating_sum / $rating->rating_count), 2);

			$ratings = array(
				'rating_count'  => $rating->rating_count,
				'rating_sum'    => $rating->rating_sum,
				'rating_avg'    => $avg_rating,
			);
		}

		return (object) $ratings;
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return object
	 *
	 * Get course rating by user
	 */
	public function get_course_rating_by_user($course_id = 0, $user_id = 0){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);

		$ratings = array(
			'rating'  => 0,
			'review'    => '',
		);

		global $wpdb;

		$rating = $wpdb->get_row("select meta_value as rating, comment_content as review from {$wpdb->comments}
				INNER JOIN {$wpdb->commentmeta} 
				ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
				WHERE {$wpdb->comments}.comment_post_ID = {$course_id} AND user_id = {$user_id}
				AND meta_key = 'tutor_rating' ;"
		);

		if ($rating){
			$rating_format = number_format($rating->rating, 2);

			$ratings = array(
				'rating'    => $rating_format,
				'review'    => $rating->review,
			);
		}
		return (object) $ratings;
	}

	/**
	 * @param int $user_id
	 *
	 * @return null|string
	 */
	public function count_reviews_wrote_by_user($user_id = 0){
		global $wpdb;
		$user_id = $this->get_user_id($user_id);

		$count_reviews = $wpdb->get_var("SELECT COUNT(comment_ID) from {$wpdb->comments} WHERE user_id = {$user_id} AND comment_type = 'tutor_course_rating' ");
		return $count_reviews;
	}

	/**
	 * @param $size
	 *
	 * @return bool|int|string
	 *
	 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
	 */

	function let_to_num( $size ) {
		$l    = substr( $size, -1 );
		$ret  = substr( $size, 0, -1 );
		$byte = 1024;

		switch ( strtoupper( $l ) ) {
			case 'P':
				$ret *= 1024;
			// No break.
			case 'T':
				$ret *= 1024;
			// No break.
			case 'G':
				$ret *= 1024;
			// No break.
			case 'M':
				$ret *= 1024;
			// No break.
			case 'K':
				$ret *= 1024;
			// No break.
		}
		return $ret;
	}



	function get_db_version() {
		global $wpdb;

		if ( empty( $wpdb->is_mysql ) ) {
			return array(
				'string' => '',
				'number' => '',
			);
		}

		if ( $wpdb->use_mysqli ) {
			$server_info = mysqli_get_server_info( $wpdb->dbh ); // @codingStandardsIgnoreLine.
		} else {
			$server_info = mysql_get_server_info( $wpdb->dbh ); // @codingStandardsIgnoreLine.
		}

		return array(
			'string' => $server_info,
			'number' => preg_replace( '/([^\d.]+).*/', '', $server_info ),
		);
	}

	public function help_tip($tip = ''){
		return '<span class="tutor-help-tip" data-tip="' . $tip . '"></span>';
	}


	public function get_top_question($course_id = 0, $user_id = 0, $offset = 0, $limit = 20){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);

		global $wpdb;

		$questions = $wpdb->get_results("select {$wpdb->comments}.comment_ID, 
			{$wpdb->comments}.comment_post_ID, 
			{$wpdb->comments}.comment_author, 
			{$wpdb->comments}.comment_date, 
			{$wpdb->comments}.comment_content, 
			{$wpdb->comments}.user_id, 
			{$wpdb->commentmeta}.meta_value as question_title,
			{$wpdb->users}.display_name 
			
			from {$wpdb->comments}
			INNER JOIN {$wpdb->commentmeta} 
			ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
			INNER  JOIN {$wpdb->users}
			ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			WHERE {$wpdb->comments}.comment_post_ID = {$course_id} 
			AND {$wpdb->comments}.user_id = {$user_id}
			AND {$wpdb->comments}.comment_type	 = 'tutor_q_and_a'
			AND meta_key = 'tutor_question_title' ORDER BY comment_ID DESC LIMIT {$offset},{$limit} ;"
		);

		return $questions;
	}

	public function get_total_qa_question($search_term = ''){
		global $wpdb;

		if ($search_term){
			$search_term = " AND {$wpdb->commentmeta}.meta_value LIKE '%{$search_term}%' ";
		}

		$count = $wpdb->get_var("SELECT COUNT({$wpdb->comments}.comment_ID) FROM {$wpdb->comments} 
			INNER JOIN {$wpdb->commentmeta} 
			ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
			WHERE comment_type	 = 'tutor_q_and_a' AND comment_parent = 0  {$search_term} ");

		return (int) $count;
	}

	/**
	 * @param int $start
	 * @param int $limit
	 * @param string $search_term
	 *
	 * @return array|null|object
	 *
	 *
	 * Get question and answer query
	 *
	 * @since v.1.0.0
	 */
	public function get_qa_questions($start = 0, $limit = 10, $search_term = '') {
		global $wpdb;

		if ($search_term){
			$search_term = " AND {$wpdb->commentmeta}.meta_value LIKE '%{$search_term}%' ";
		}

		$query = $wpdb->get_results("SELECT 
			{$wpdb->comments}.comment_ID, 
			{$wpdb->comments}.comment_post_ID, 
			{$wpdb->comments}.comment_author, 
			{$wpdb->comments}.comment_date, 
			{$wpdb->comments}.comment_content, 
			{$wpdb->comments}.user_id, 
			{$wpdb->commentmeta}.meta_value as question_title,
			{$wpdb->users}.display_name,
			{$wpdb->posts}.post_title,
			
			(SELECT COUNT(answers_t.comment_ID) FROM {$wpdb->comments} answers_t
		  	WHERE answers_t.comment_parent = {$wpdb->comments}.comment_ID ) as answer_count
		 
		 	FROM {$wpdb->comments} 
		
			INNER JOIN {$wpdb->commentmeta} 
			ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
			
			INNER JOIN {$wpdb->posts} 
			ON {$wpdb->comments}.comment_post_ID = {$wpdb->posts}.ID
			
			INNER  JOIN {$wpdb->users}
			ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
		  
			WHERE {$wpdb->comments}.comment_type = 'tutor_q_and_a' AND {$wpdb->comments}.comment_parent = 0  {$search_term} 
			ORDER BY {$wpdb->comments}.comment_ID DESC 
			LIMIT {$start},{$limit}; ");

		return $query;
	}

	public function get_qa_question($question_id){
		global $wpdb;
		$query = $wpdb->get_row("SELECT 
			{$wpdb->comments}.comment_ID, 
			{$wpdb->comments}.comment_post_ID, 
			{$wpdb->comments}.comment_author, 
			{$wpdb->comments}.comment_date, 
			{$wpdb->comments}.comment_content, 
			{$wpdb->comments}.user_id, 
			{$wpdb->commentmeta}.meta_value as question_title,
			{$wpdb->users}.display_name,
			{$wpdb->posts}.post_title
		 
		 	FROM {$wpdb->comments} 
			INNER JOIN {$wpdb->commentmeta} 
			ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
			
			INNER JOIN {$wpdb->posts} 
			ON {$wpdb->comments}.comment_post_ID = {$wpdb->posts}.ID
			
			INNER  JOIN {$wpdb->users}
			ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			WHERE comment_type	 = 'tutor_q_and_a' AND {$wpdb->comments}.comment_ID = {$question_id}");

		return $query;
	}

	public function get_qa_answer_by_question($question_id){
		global $wpdb;
		$query = $wpdb->get_results("SELECT 
			{$wpdb->comments}.comment_ID, 
			{$wpdb->comments}.comment_post_ID, 
			{$wpdb->comments}.comment_author, 
			{$wpdb->comments}.comment_date, 
			{$wpdb->comments}.comment_content, 
			{$wpdb->comments}.comment_parent,
			{$wpdb->comments}.user_id,
			{$wpdb->users}.display_name
		  		 
		 	FROM {$wpdb->comments} 
			
			INNER  JOIN {$wpdb->users}
			ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			WHERE comment_type = 'tutor_q_and_a' 
			AND {$wpdb->comments}.comment_parent = {$question_id} ORDER BY {$wpdb->comments}.comment_ID ASC ");

		return $query;
	}

	public function unanswered_question_count(){
		global $wpdb;

		$count = $wpdb->get_var("select COUNT({$wpdb->comments}.comment_ID) 
			from {$wpdb->comments} 
			WHERE {$wpdb->comments}.comment_type = 'tutor_q_and_a' 
			AND {$wpdb->comments}.comment_approved = 'waiting_for_answer'
			AND {$wpdb->comments}.comment_parent = 0;");
		return (int) $count;
	}

	/**
	 * @param int $course_id
	 *
	 * @return array|null|object
	 *
	 * Return all of announcements for a course
	 *
	 * @since v.1.0.0
	 */
	public function get_announcements($course_id = 0){
		$course_id = $this->get_post_id($course_id);
		global $wpdb;

		$query = $wpdb->get_results("select {$wpdb->posts}.ID, post_author, post_date, post_content, post_title, display_name
			from {$wpdb->posts}
			INNER JOIN {$wpdb->users} ON post_author = {$wpdb->users}.ID
			WHERE post_type = 'tutor_announcements' 
			AND post_parent = {$course_id} ORDER BY {$wpdb->posts}.ID DESC;");
		return $query;
	}

	public function announcement_content($content = ''){
		$search = array('{user_display_name}');

		$user_display_name = 'User';
		if (is_user_logged_in()){
			$user = wp_get_current_user();
			$user_display_name = $user->display_name;
		}
		$replace = array($user_display_name);

		return str_replace($search, $replace, $content);
	}

	/**
	 * @param int $post_id
	 * @param string $option_key
	 * @param bool $default
	 *
	 * @return array|bool|mixed
	 *
	 * Get the quiz option from meta
	 */
	public function get_quiz_option($post_id = 0, $option_key = '', $default = false){
		$post_id = $this->get_post_id($post_id);
		$get_option_meta = maybe_unserialize(get_post_meta($post_id, 'tutor_quiz_option', true));

		$value = $this->avalue_dot($option_key, $get_option_meta);
		if ($value){
			return $value;
		}
		return $default;
	}


	/**
	 * @param int $quiz_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get the questions by quiz ID
	 */
	public function get_questions_by_quiz($quiz_id = 0){
		$quiz_id = $this->get_post_id($quiz_id);
		global $wpdb;

		$questions = $wpdb->get_results("SELECT ID, post_content, post_title, post_parent from {$wpdb->posts} WHERE post_type = 'tutor_question' AND post_parent = {$quiz_id} ORDER BY menu_order ASC ");

		if (is_array($questions) && count($questions)){
			return $questions;
		}
		return false;
	}

	public function get_question_types($type = null){
		$types = array(
			'true_false'        => __('True/False', 'tutor'),
			'multiple_choice'   => __('Multiple Choice', 'tutor'),
			'single_choice'     => __('Single Choice', 'tutor'),
		);

		if (isset($types[$type])){
			return $types[$type];
		}
		return $types;
	}

	public function get_quiz_answer_options_by_question($question_id){
		global $wpdb;

		$answer_options = $wpdb->get_results("select 
			{$wpdb->comments}.comment_ID, 
			{$wpdb->comments}.comment_post_ID, 
			{$wpdb->comments}.comment_content
						
			FROM {$wpdb->comments}
			WHERE {$wpdb->comments}.comment_post_ID = {$question_id} 
			AND {$wpdb->comments}.comment_type = 'quiz_answer_option'
			ORDER BY {$wpdb->comments}.comment_karma ASC ;");

		if (is_array($answer_options) && count($answer_options)){
			return $answer_options;
		}
		return false;
	}

	/**
	 * @param $quiz_id
	 *
	 * @return int
	 *
	 * Get the next question order ID
	 */

	public function quiz_next_question_order_id($quiz_id){
		global $wpdb;

		$last_order = (int) $wpdb->get_var("SELECT MAX(menu_order) FROM {$wpdb->posts} WHERE post_parent = {$quiz_id} AND post_type = 'tutor_question';");
		return $last_order + 1;
	}

	public function get_quiz_id_by_question($question_id){
		global $wpdb;

		$quiz_id = $wpdb->get_var("SELECT post_parent FROM {$wpdb->posts} WHERE ID = {$question_id} AND post_type = 'tutor_question' ;");
		return $quiz_id;
	}

	public function get_unattached_quiz($config = array()){
		global $wpdb;

		$default_attr = array(
			'search_term' => '',
			'start' => '0',
			'limit' => '10',
			'order' => 'DESC',
			'order_by' => 'ID',
		);
		$attr = array_merge($default_attr, $config);
		extract($attr);

		$search_query = '';
		if (! empty($search_term)){
			$search_query = "AND post_title LIKE '%{$search_term}%'";
		}

		$questions = $wpdb->get_results("SELECT ID, post_content, post_title, post_parent from {$wpdb->posts} WHERE post_type = 'tutor_quiz' AND post_status = 'publish' AND post_parent = 0 {$search_query} ORDER BY {$order_by} {$order}  LIMIT {$start},{$limit} ");

		if (is_array($questions) && count($questions)){
			return $questions;
		}
		return false;
	}

	/**
	 * @param int $post_id
	 *
	 * @return array|bool|null|object
	 */
	public function get_attached_quiz($post_id = 0){
		global $wpdb;

		$post_id = $this->get_post_id($post_id);

		$questions = $wpdb->get_results("SELECT ID, post_content, post_title, post_parent from {$wpdb->posts} WHERE post_type = 'tutor_quiz' AND post_status = 'publish' AND post_parent = {$post_id}");

		if (is_array($questions) && count($questions)){
			return $questions;
		}
		return false;
	}


	public function get_course_by_quiz($quiz_id){
		global $wpdb;

		$quiz_id = $this->get_post_id($quiz_id);
		$post = get_post($quiz_id);

		if ($post) {
			$course_post_type = tutor()->course_post_type;
			$course = $wpdb->get_row( "select ID, post_name, post_type, post_parent from {$wpdb->posts} where ID = {$post->post_parent} " );

			if ($course) {
				//Checking if this topic
				if ( $course->post_type !== $course_post_type ) {
					$course = $wpdb->get_row( "select ID, post_name, post_type, post_parent from {$wpdb->posts} where ID = {$course->post_parent} " );
				}
				//Checking if this lesson
				if ( $course->post_type !== $course_post_type ) {
					$course = $wpdb->get_row( "select ID, post_name, post_type, post_parent from {$wpdb->posts} where ID = {$course->post_parent} " );
				}

				return $course;
			}
		}

		return false;
	}

	/**
	 * @param $quiz_id
	 *
	 * @return int
	 */
	public function total_questions_for_student_by_quiz($quiz_id){
		$quiz_id = $this->get_post_id($quiz_id);
		global $wpdb;

		$total_question = (int) $wpdb->get_var("select count(ID) from {$wpdb->posts} where post_parent = {$quiz_id} AND post_type = 'tutor_question' ");

		return $total_question;
	}

	public function is_started_quiz($quiz_id = 0){
		global $wpdb;

		$quiz_id = $this->get_post_id($quiz_id);
		$user_id = get_current_user_id();

		$is_started = $wpdb->get_row("SELECT 
 			comment_ID,
 			comment_post_ID,
 			comment_author,
 			comment_date as quiz_started_at,
 			comment_date_gmt,
 			comment_approved as quiz_attempt_status,
 			comment_parent,
 			user_id
 			
 			FROM {$wpdb->comments} 
			WHERE user_id = {$user_id} 
		  	AND comment_type = 'tutor_quiz_attempt' 
		  	AND comment_approved = 'quiz_started' 
		  	AND comment_post_ID = {$quiz_id} ; ");

		return $is_started;
	}

	/**
	 * @param $quiz_id
	 *
	 * Method for get the total amount of question for a quiz
	 * Student will answer this amount of question, one quiz have many question
	 * but student will answer a specific amount of questions
	 *
	 * @return int
	 */

	public function max_questions_for_take_quiz($quiz_id){
		$quiz_id = $this->get_post_id($quiz_id);
		global $wpdb;

		$max_questions = (int) $wpdb->get_var("select count(ID) from {$wpdb->posts} where post_parent = {$quiz_id} AND post_type = 'tutor_question' ");
		$max_mentioned = (int) $this->get_quiz_option($quiz_id, 'max_questions_for_answer', 10);

		if ($max_mentioned < $max_questions ){
			return $max_mentioned;
		}

		return $max_questions;
	}

	public function get_attempt($attempt_id = 0){
		global $wpdb;
		
		$attempt = $wpdb->get_row("SELECT 
 			comment_ID,
 			comment_post_ID,
 			comment_author,
 			comment_date as quiz_started_at,
 			comment_date_gmt,
 			comment_approved as quiz_attempt_status,
 			comment_parent,
 			user_id
 			
 			FROM {$wpdb->comments} 
		  	WHERE comment_type = 'tutor_quiz_attempt' 
		  	AND comment_ID = {$attempt_id} ;");

		return $attempt;
	}

	public function quiz_attempt_info($quiz_attempt_id){
		$attempt_info = get_comment_meta($quiz_attempt_id, 'quiz_attempt_info', true);
		return $attempt_info;
	}

	public function quiz_update_attempt_info($quiz_attempt_id, $attempt_info = array()){
		$answers = tutor_utils()->avalue_dot('answers', $attempt_info);
		$total_marks = array_sum(wp_list_pluck($answers, 'question_mark'));
		$earned_marks = tutor_utils()->avalue_dot('marks_earned', $attempt_info);
		$earned_mark_percent = $earned_marks > 0 ? ( number_format(($earned_marks * 100) / $total_marks)) : 0;
		update_comment_meta($quiz_attempt_id, 'earned_mark_percent', $earned_mark_percent);

		return update_comment_meta($quiz_attempt_id,'quiz_attempt_info', $attempt_info);
	}

	public function get_rand_single_question_by_quiz_for_student($quiz_id = 0){
		global $wpdb;

		$quiz_id = $this->get_post_id($quiz_id);

		$is_attempt = $this->is_started_quiz($quiz_id);
		$attempted_question_ids = array();
		if ($is_attempt){
			$attempt_info = $this->quiz_attempt_info($is_attempt->comment_ID);
			$attempted_question_ids = wp_list_pluck($this->avalue_dot('answers', $attempt_info),'questionID');
		}
		$attempted_question_ids_string = implode(",", $attempted_question_ids);

		$not_in_sql = "";
		if (is_array($attempted_question_ids) && count($attempted_question_ids)){
			$not_in_sql = " AND ID NOT IN({$attempted_question_ids_string}) ";
		}

		$question = $wpdb->get_row("SELECT ID, post_content, post_title, post_parent 
			from {$wpdb->posts} WHERE post_type = 'tutor_question' AND post_parent = {$quiz_id} {$not_in_sql} ORDER BY RAND() ;");

		return $question;
	}

	/**
	 * @param int $quiz_id
	 * @param int $user_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get all of the attempts by an user of a quiz
	 */

	public function quiz_attempts($quiz_id = 0, $user_id = 0){
		global $wpdb;

		$quiz_id = $this->get_post_id($quiz_id);
		$user_id = $this->get_user_id($user_id);

		$attempts = $wpdb->get_results("SELECT 
 			{$wpdb->comments}.comment_ID,
 			comment_post_ID,
 			comment_author,
 			comment_date as quiz_started_at,
 			comment_date_gmt,
 			comment_approved as quiz_attempt_status,
 			comment_parent,
 			user_id,
 			
 			attempt_info.meta_value as quiz_attempt_info,
 			pass_mark.meta_value as pass_mark_percent
 			
 			FROM {$wpdb->comments} 
 			
 			LEFT JOIN {$wpdb->commentmeta} attempt_info ON {$wpdb->comments}.comment_ID = attempt_info.comment_id AND attempt_info.meta_key = 'quiz_attempt_info'
 			LEFT JOIN {$wpdb->commentmeta} pass_mark ON {$wpdb->comments}.comment_ID = pass_mark.comment_id AND pass_mark.meta_key = 'pass_mark_percent'
 			
			WHERE user_id = {$user_id} 
		  	AND comment_type = 'tutor_quiz_attempt' 
		  	AND comment_approved != 'quiz_started' 
		  	AND comment_post_ID = {$quiz_id} ; ");

		if (is_array($attempts) && count($attempts)){
			return $attempts;
		}

		return false;
	}

	public function get_total_quiz_attempts($search_term = ''){
		global $wpdb;

		if ($search_term){
			$search_term = " AND ( user_email like '%{$search_term}%' OR display_name like '%{$search_term}%' OR post_title like '%{$search_term}%' ) ";
		}

		$count = $wpdb->get_var("SELECT COUNT({$wpdb->comments}.comment_ID) FROM {$wpdb->comments} 
		INNER JOIN {$wpdb->posts} 
		ON {$wpdb->comments}.comment_post_ID = {$wpdb->posts}.ID
			
		INNER  JOIN {$wpdb->users}
		ON {$wpdb->comments}.user_id = {$wpdb->users}.ID

		WHERE comment_type = 'tutor_quiz_attempt' {$search_term} ");

		return (int) $count;
	}

	public function get_quiz_attempts($start = 0, $limit = 10, $search_term = '') {
		global $wpdb;

		if ($search_term){
			$search_term = " AND ( user_email like '%{$search_term}%' OR display_name like '%{$search_term}%' OR post_title like '%{$search_term}%' ) ";
		}

		$query = $wpdb->get_results("SELECT 
			{$wpdb->comments}.comment_ID, 
			{$wpdb->comments}.comment_post_ID, 
			{$wpdb->comments}.comment_author, 
			{$wpdb->comments}.comment_date, 
			{$wpdb->comments}.comment_content, 
			{$wpdb->comments}.comment_approved as attempt_status, 
			{$wpdb->comments}.user_id, 
			{$wpdb->users}.display_name,
			{$wpdb->users}.user_email,
			{$wpdb->posts}.post_title,
			
			attempt_info.meta_value as quiz_attempt_info,
 			pass_mark.meta_value as pass_mark_percent,
	
			(SELECT COUNT(answers_t.comment_ID) FROM {$wpdb->comments} answers_t
		  	WHERE answers_t.comment_parent = {$wpdb->comments}.comment_ID ) as answer_count
		 
		 	FROM {$wpdb->comments} 
		
			INNER JOIN {$wpdb->posts} 
			ON {$wpdb->comments}.comment_post_ID = {$wpdb->posts}.ID
			
			INNER  JOIN {$wpdb->users}
			ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			
 			LEFT JOIN {$wpdb->commentmeta} attempt_info ON {$wpdb->comments}.comment_ID = attempt_info.comment_id AND attempt_info.meta_key = 'quiz_attempt_info'
 			LEFT JOIN {$wpdb->commentmeta} pass_mark ON {$wpdb->comments}.comment_ID = pass_mark.comment_id AND pass_mark.meta_key = 'pass_mark_percent'
 			
			WHERE {$wpdb->comments}.comment_type = 'tutor_quiz_attempt' {$search_term} 
			ORDER BY {$wpdb->comments}.comment_ID DESC 
			LIMIT {$start},{$limit}; ");

		return $query;
	}

	public function get_quiz_answers_by_ids($ids){
		$ids = (array) $ids;

		if (!count($ids)){
			return false;
		}

		$in_ids = implode(",", $ids);

		global $wpdb;
		$query = $wpdb->get_results("SELECT 
			comment_ID, 
			comment_content
		 	FROM {$wpdb->comments} 
			WHERE comment_type = 'quiz_answer_option' AND comment_ID IN({$in_ids}) ");

		if (is_array($query) && count($query)){
			return $query;
		}

		return false;
	}

	/**
	 * @param null $level
	 *
	 * @return mixed
	 *
	 * Get the users / students / course levels
	 */

	public function course_levels($level = null){
		$levels = apply_filters('tutor_course_level', array(
			'all_levels'    => __('All Levels', 'tutor'),
			'beginner'      => __('Beginner', 'tutor'),
			'intermediate'  => __('Intermediate', 'tutor'),
			'expert'        => __('Expert', 'tutor'),
		));

		if ($level){
			if (isset($levels[$level])){
				return $levels[$level];
			}else{
				return '';
			}
		}

		return $levels;
	}

	public function user_profile_permalinks(){
		$permalinks = array(
			'enrolled_course'   => __('Enrolled Course', 'tutor'),
			'courses_taken'     => __('Courses Taken', 'tutor'),
			'reviews_wrote'     => __('Reviews Written', 'tutor'),
		);
		
		return apply_filters('tutor_public_profile/permalinks', $permalinks);
	}

	public function student_register_url(){
		$student_register_page = (int) $this->get_option('student_register_page');

		if ($student_register_page){
			return get_the_permalink($student_register_page);
		}
		return false;
	}


	public function tutor_dashboard_url(){
		$page_id = (int) tutor_utils()->get_option('student_dashboard');
		$page_id = apply_filters('tutor_dashboard_url', $page_id);
		return get_the_permalink($page_id);
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return bool
	 *
	 * is_wishlisted();
	 */
	public function is_wishlisted($course_id = 0, $user_id = 0){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);
		if ( ! $user_id){
			return false;
		}

		global $wpdb;
		$if_added_to_list = (bool) $wpdb->get_row("select * from {$wpdb->usermeta} WHERE user_id = {$user_id} AND meta_key = '_tutor_course_wishlist' AND meta_value = {$course_id} ;");

		return $if_added_to_list;
	}

	/**
	 * @param int $user_id
	 *
	 * @return array|null|object
	 *
	 * Get the wish lists by an user
	 */
	public function get_wishlist($user_id = 0){
		$user_id = $this->get_user_id($user_id);
		global $wpdb;

		$query = "SELECT $wpdb->posts.*
	    FROM $wpdb->posts
	    LEFT JOIN $wpdb->usermeta ON ($wpdb->posts.ID = $wpdb->usermeta.meta_value)
	    WHERE $wpdb->usermeta.meta_key = '_tutor_course_wishlist'
	    AND $wpdb->usermeta.user_id = {$user_id}
	    ORDER BY $wpdb->usermeta.umeta_id DESC ";
		$pageposts = $wpdb->get_results($query, OBJECT);
		return $pageposts;
	}

	/**
	 * @param int $limit
	 *
	 * @return array|null|object
	 *
	 * Getting popular courses
	 */
	public function most_popular_courses($limit = 10){
		global $wpdb;

		$courses = $wpdb->get_results("
              SELECT COUNT(enrolled.ID) as total_enrolled,
              enrolled.post_parent as course_id,
              course.*
              from {$wpdb->posts} enrolled
              INNER JOIN {$wpdb->posts} course ON enrolled.post_parent = course.ID
              WHERE enrolled.post_type = 'tutor_enrolled' AND enrolled.post_status = 'completed'
              GROUP BY course_id
              ORDER BY total_enrolled DESC LIMIT 0,{$limit} ;");

		return $courses;
	}

}



