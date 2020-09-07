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
	 *
	 * @since v.1.0.0
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
	 * @update v.1.4.1 (Added default parameter)
	 */

	public function avalue_dot($key = null, $array = array(), $default = false){
		$array = (array) $array;
		if ( ! $key || ! count($array) ){
			return $default;
		}
		$option_key_array = explode('.', $key);

		$value = $array;

		foreach ($option_key_array as $dotKey){
			if (isset($value[$dotKey])){
				$value = $value[$dotKey];
			}else{
				return $default;
			}
		}
		return $value;
	}

	/**
	 * @param null $key
	 * @param array $array
	 *
	 * @return array|bool|mixed
	 *
	 * alias of avalue_dot method of utils
	 *
	 * Get array value by key and recursive array value by dot notation key
	 *
	 * ex: tutor_utils()->array_get('key.child_key', $array);
	 *
	 * @since v.1.3.3
	 */
	public function array_get($key = null, $array = array(), $default = false){
		return $this->avalue_dot($key, $array, $default);
	}

	/**
	 * @return array
	 *
	 * Get all pages
	 *
	 * @since v.1.0.0
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
	 *
	 * @since v.1.0.0
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
	 *
	 * @since v.1.0.0
	 */

	public function profile_url($student_id = 0){
		$site_url = trailingslashit(home_url()).'profile/';
		$user_name = '';

		$student_id = $this->get_user_id($student_id);
		if ($student_id){
			global $wpdb;
			$user = $wpdb->get_row("SELECT user_nicename from {$wpdb->users} WHERE ID = {$student_id} ");
			if ($user){
				$user_name = $user->user_nicename;
			}
		}else{
			$user_name = 'user_name';
		}

		return $site_url.$user_name;
	}

	/**
	 * @param string $user_nicename
	 *
	 * @return array|null|object
	 *
	 * Get user by user login
	 *
	 * @since v.1.0.0
	 */
	public function get_user_by_login($user_nicename = ''){
		global $wpdb;
		$user_nicename = sanitize_text_field($user_nicename);
		$user = $wpdb->get_row("SELECT * from {$wpdb->users} WHERE user_nicename = '{$user_nicename}'");
		return $user;
	}

	/**
	 * @return bool
	 *
	 * Check if WooCommerce Activated
	 *
	 * @since v.1.0.0
     * @updated @1.5.9
	 */

	public function has_wc(){
		return class_exists('WooCommerce');
	}

	/**
	 * @return bool
	 *
	 * determine if EDD plugin activated
	 *
	 * @since v.1.0.0
	 */
	public function has_edd(){
		$activated_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ));
		//$depends = array('easy-digital-downloads/easy-digital-downloads.php', 'tutor-edd/tutor-edd.php');
		$depends = array('easy-digital-downloads/easy-digital-downloads.php');
		$has = count(array_intersect($depends, $activated_plugins)) == count($depends);

		return $has;
	}

	/**
	 * @return bool
	 *
	 * Determine if PMPro is activated
	 *
	 * @since v.1.3.6
	 */
	public function has_pmpro(){
		$activated_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ));
		$depends = array('paid-memberships-pro/paid-memberships-pro.php');
		return count(array_intersect($depends, $activated_plugins)) == count($depends);
	}

	public function has_wcs(){
		$activated_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ));
		$depends = array('woocommerce-subscriptions/woocommerce-subscriptions.php');
		return count(array_intersect($depends, $activated_plugins)) == count($depends);
	}


	/**
	 * @return bool
	 *
	 * checking if BuddyPress exists and activated;
	 *
	 * @since v.1.4.8
	 */
	public function has_bp(){
		$activated_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ));
		$depends = array('buddypress/bp-loader.php');
		return count(array_intersect($depends, $activated_plugins)) == count($depends);
	}

	/**
	 * @return mixed
	 *
	 * @since v.1.0.0
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


	/**
	 * @param string $value
	 *
	 * Check raw data
	 *
	 * @since v.1.0.0
	 */
	public function print_view($value = ''){
		echo '<pre>';
		print_r($value);
		echo '</pre>';
	}

	/**
	 * @param array $excludes
	 *
	 * @return array|null|object
	 *
	 * Get courses
	 *
	 * @since v.1.0.0
	 */

	public function get_courses($excludes = array()){
		global $wpdb;


		$excludes = (array) $excludes;
		$exclude_query = '';
		if (count($excludes)){
			$exclude_query = implode("','", $excludes);
		}

		$course_post_type = tutor()->course_post_type;
		$query = $wpdb->get_results("SELECT ID, post_author, post_title, post_name,post_status, menu_order 
				from {$wpdb->posts} WHERE post_status = 'publish'
				AND ID NOT IN('$exclude_query')
				AND post_type = '{$course_post_type}' ");
		return $query;
	}

	/**
	 * @param int $instructor_id
	 *
	 * @return array|null|object
	 *
	 * Get courses for instructors
	 *
	 * @since v.1.0.0
	 */
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

	/**
	 * @param $instructor_id
	 *
	 * @return null|string
	 *
	 * Get course count by instructor
	 *
	 * @since v.1.0.0
	 */

	public function get_course_count_by_instructor($instructor_id){
		global $wpdb;

		$course_post_type = tutor()->course_post_type;
		$count = $wpdb->get_var("SELECT COUNT(ID) from {$wpdb->posts} 
			INNER JOIN {$wpdb->usermeta} ON user_id = {$instructor_id} AND meta_key = '_tutor_instructor_course_id' AND meta_value = ID 
			WHERE post_status = 'publish' 
			AND post_type = '{$course_post_type}' ; ");

		return $count;
	}

	/**
	 * @param $instructor_id
	 *
	 * @return array|null|object
	 *
	 * Get courses by a instructor
	 *
	 * @since v.1.0.0
	 */
	public function get_courses_by_instructor($instructor_id = 0, $post_status = array('publish')){
		global $wpdb;

		$instructor_id = $this->get_user_id($instructor_id);
		$course_post_type = tutor()->course_post_type;

		if ($post_status === 'any'){
			$where_post_status = "";
		}else{
			$post_status = (array) $post_status;
			$statuses = "'".implode("','", $post_status)."'";
			$where_post_status = "AND $wpdb->posts.post_status IN({$statuses}) ";
		}

		//AND $wpdb->posts.post_date < NOW()
		$querystr = "
	    SELECT $wpdb->posts.* 
	    FROM $wpdb->posts
		INNER JOIN {$wpdb->usermeta} ON $wpdb->usermeta.user_id = {$instructor_id} AND $wpdb->usermeta.meta_key = '_tutor_instructor_course_id' AND $wpdb->usermeta.meta_value = $wpdb->posts.ID 
	
	    WHERE 1 = 1 {$where_post_status}
	    AND $wpdb->posts.post_type = '{$course_post_type}'
	    
	    ORDER BY $wpdb->posts.post_date DESC";

		$pageposts = $wpdb->get_results($querystr, OBJECT);
		return $pageposts;
	}

	/**
	 * @return mixed
	 *
	 * Get archive page course count
	 *
	 * @since v.1.0.0
	 */
	public function get_archive_page_course_count(){
		global $wp_query;
		return $wp_query->post_count;
	}

	/**
	 * @return null|string
	 *
	 * Get course count
	 *
	 * @since v.1.0.0
	 */
	public function get_course_count(){
		global $wpdb;

		$course_post_type = tutor()->course_post_type;
		$count = $wpdb->get_var("SELECT COUNT(ID) from {$wpdb->posts} WHERE post_status = 'publish' AND post_type = '{$course_post_type}'; ");
		return $count;
	}

	/**
	 * @return null|string
	 *
	 * Get lesson count
	 *
	 * @since v.1.0.0
	 */
	public function get_lesson_count(){
		global $wpdb;

		$lesson_post_type = tutor()->lesson_post_type;
		$count = $wpdb->get_var("SELECT COUNT(ID) from {$wpdb->posts} WHERE post_status = 'publish' AND post_type = '{$lesson_post_type}'; ");
		return $count;
	}

	/**
	 * @param int $course_id
	 * @param int $limit
	 *
	 * @return \WP_Query
	 *
	 * Get lesson
	 *
	 * @since v.1.0.0
	 */
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

	/**
	 * @param int $course_id
	 *
	 * @return int
	 *
	 * Get total lesson count by a course
	 *
	 * @since v.1.0.0
	 */
	public function get_lesson_count_by_course($course_id = 0){
		$course_id = $this->get_post_id($course_id);
		global $wpdb;

		$lesson_post_type = tutor()->lesson_post_type;

		$course_id = $this->get_post_id($course_id);
		$topicIDS = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'topics' AND post_parent = {$course_id} ");

		$lesson_count = 0;
		if ($this->count($topicIDS)){
			$inIDS = implode(",", $topicIDS);
			$lesson_count = $wpdb->get_var("SELECT COUNT(ID)  FROM {$wpdb->posts} WHERE post_parent IN({$inIDS}) AND post_type = '{$lesson_post_type}' ");
		}

		return (int) $lesson_count;
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return int
	 *
	 * Get completed lesson total number by a course
	 *
	 * @since v.1.0.0
	 */
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
	 * @since v.1.0.0
     * @updated v.1.6.1
	 */
	public function get_course_completed_percent($course_id = 0, $user_id = 0){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);

		$completed_lesson = $this->get_completed_lesson_count_by_course($course_id, $user_id);
        $course_contents = tutils()->get_course_contents_by_id($course_id);

        $totalContents = $this->count($course_contents);
        $totalContents = $totalContents ? $totalContents : 0;

        $completedCount = $completed_lesson;

        if (tutils()->count($course_contents)){
            foreach ($course_contents as $content){
                if ($content->post_type === 'tutor_quiz'){
                    $attempt = $this->get_quiz_attempt($content->ID);
                    if ($attempt){
                        $completedCount++;
                    }
                }elseif ($content->post_type === 'tutor_assignments'){
                    $isSubmitted = $this->is_assignment_submitted($content->ID);
                    if ($isSubmitted){
                        $completedCount++;
                    }
                }
            }
        }

		if ($totalContents > 0 && $completedCount > 0){
			return number_format(($completedCount * 100) / $totalContents);
		}

		return 0;
	}

	/**
	 * @param int $course_id
	 *
	 * @return \WP_Query
	 *
	 * Get all topics by given course ID
	 *
	 * @since v.1.0.0
	 */
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

	/**
	 * @param $course_ID
	 *
	 * @return int
	 *
	 * Get next topic order id
	 *
	 * @since v.1.0.0
	 */
	public function get_next_topic_order_id($course_ID){
		global $wpdb;

		$last_order = (int) $wpdb->get_var("SELECT MAX(menu_order) FROM {$wpdb->posts} WHERE post_parent = {$course_ID} AND post_type = 'topics';");
		return $last_order + 1;
	}

	/**
	 * @param $topic_ID
	 *
	 * @return int
	 *
	 * Get next course content order id
	 *
	 * @since v.1.0.0
	 */
	public function get_next_course_content_order_id($topic_ID){
		global $wpdb;

		$last_order = (int) $wpdb->get_var("SELECT MAX(menu_order) FROM {$wpdb->posts} WHERE post_parent = {$topic_ID};");
		return $last_order + 1;
	}

	/**
	 * @param int $topics_id
	 * @param int $limit
	 *
	 * @return \WP_Query
	 *
	 * Get lesson by topic
	 *
	 * @since v.1.0.0
	 */
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

	/**
	 * @param int $topics_id
	 * @param int $limit
	 *
	 * @return \WP_Query
	 *
	 * Get course content by topic
	 *
	 * @since v.1.0.0
	 */
	public function get_course_contents_by_topic($topics_id = 0, $limit = 10){
		$topics_id = $this->get_post_id($topics_id);

		$lesson_post_type = tutor()->lesson_post_type;
		$args = array(
			'post_type'  => apply_filters('tutor_course_contents_post_types', array($lesson_post_type, 'tutor_quiz')),
			'post_parent'  => $topics_id,
			'posts_per_page'    => $limit,
			'orderby' => 'menu_order',
			'order'   => 'ASC',
		);

		$query = new \WP_Query($args);

		return $query;
	}

	/**
	 * @param string $request_method
	 *
	 * Check actions nonce
	 *
	 * @since v.1.0.0
	 */
	public function checking_nonce($request_method = 'post'){
		if ($request_method === 'post'){
			if (!isset($_POST[tutor()->nonce]) || !wp_verify_nonce($_POST[tutor()->nonce], tutor()->nonce_action)) {
				exit('Nonce does not matched');
			}
		}else{
			if (!isset($_GET[tutor()->nonce]) || !wp_verify_nonce($_GET[tutor()->nonce], tutor()->nonce_action)) {
				exit('Nonce does not matched');
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
		$course_id = $this->get_post_id($course_id);

		$price_type = $this->price_type($course_id);
		if ($price_type === 'free'){
			$is_paid = apply_filters('is_course_paid', false, $course_id);
			if ( ! $is_paid){
				return false;
			}
		}
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
			$monetize_by = $this->get_option('monetize_by');

			if ($this->has_wc() && $monetize_by === 'wc'){
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
	 * @return object
	 *
	 * Get raw course price and sale price of a course
	 * It could help you to calculate something
	 * Such as Calculate discount by regular price and sale price
	 *
	 * @since v.1.3.1
	 */
	public function get_raw_course_price($course_id = 0){
		$course_id = $this->get_post_id($course_id);

		$prices = array(
			'regular_price' => 0,
			'sale_price'    => 0,
		);

		$monetize_by = $this->get_option('monetize_by');

		//if ($this->is_course_purchasable($course_id)){
		$product_id = $this->get_course_product_id($course_id);
		if ($product_id) {
			if ( $monetize_by === 'wc' && $this->has_wc() ) {
				$prices['regular_price'] = get_post_meta( $product_id, '_regular_price', true );
				$prices['sale_price']    = get_post_meta( $product_id, '_sale_price', true );
			} elseif ( $monetize_by === 'edd' && $this->has_edd() ) {
				$prices['regular_price'] = get_post_meta( $product_id, 'edd_price', true );
				$prices['sale_price']    = get_post_meta( $product_id, 'edd_price', true );
			}
		}
		//}

		return (object) $prices;
	}

	/**
	 * @param int $course_id
	 *
	 * @return mixed
	 *
	 * Get the course price type
	 *
	 * @since  v.1.3.5
	 */

	public function price_type($course_id = 0){
		$course_id = $this->get_post_id($course_id);

		$price_type = get_post_meta($course_id, '_tutor_course_price_type', true);
		return $price_type;
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

			do_action('tutor_is_enrolled_before', $course_id, $user_id);

			$getEnrolledInfo = $wpdb->get_row( "select ID, post_author, post_date,post_date_gmt,post_title from {$wpdb->posts} WHERE post_type = 'tutor_enrolled' AND post_parent = {$course_id} AND post_author = {$user_id} AND post_status = 'completed'; " );

			if ( $getEnrolledInfo ) {
				return apply_filters('tutor_is_enrolled', $getEnrolledInfo, $course_id, $user_id);
			}
		}
		return false;
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return array|bool|null|object|void
	 *
	 * Has any enrolled for a user in a course
	 *
	 * @since v.1.0.0
	 */
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
     * @param int $enrol_id
     * @return array|bool|\WP_Post|null
     *
     * Get course by enrol id
     *
     * @since v.1.6.1
     */

	public function get_course_by_enrol_id($enrol_id = 0){
	    if ( ! $enrol_id){
	        return false;
        }

        global $wpdb;

        $course_id = (int) $wpdb->get_var( "select post_parent from {$wpdb->posts} WHERE post_type = 'tutor_enrolled' AND ID = {$enrol_id}" );

        if ( $course_id ) {
            return get_post($course_id);
        }

        return null;
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
		$course_id = $this->get_course_id_by_lesson($lesson_id);

		return $this->is_enrolled($course_id);
	}

	/**
	 * @param int $lesson_id
	 *
	 * @return bool|mixed
	 *
	 * Get the course ID by Lesson
	 *
	 * @since v.1.0.0
	 *
	 * @updated v.1.4.8
	 * Added Legacy Supports
	 */

	public function get_course_id_by_lesson($lesson_id = 0){
		$lesson_id = $this->get_post_id($lesson_id);
		$course_id = get_post_meta($lesson_id, '_tutor_course_id_for_lesson', true);

		if ( ! $course_id) {
			$course_id = $this->get_course_id_by_content($lesson_id);
		}
		if ( ! $course_id){
			$course_id = 0;
		}

		return $course_id;
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

		$user_id = get_current_user_id();

		$lessons = $wpdb->get_results("SELECT items.ID FROM {$wpdb->posts} topic
				INNER JOIN {$wpdb->posts} items ON topic.ID = items.post_parent 
				WHERE topic.post_parent = {$course_id} AND items.post_status = 'publish' order by topic.menu_order ASC, items.menu_order ASC;");

		$first_lesson = false;

		if (tutils()->count($lessons)){
		    if (! empty($lessons[0])){
                $first_lesson = $lessons[0];
            }

			foreach ($lessons as $lesson){
				$is_complete = get_user_meta($user_id, "_tutor_completed_lesson_id_{$lesson->ID}", true);
				if ( ! $is_complete){
					$first_lesson = $lesson;
					break;
				}
			}

			if (! empty($first_lesson->ID)){
				return get_permalink($first_lesson->ID);
			}
		}

		return false;
	}

	/**
	 *
	 * Get course sub pages in course dashboard
	 *
	 * @since v.1.0.0
	 */
	public function course_sub_pages(){
		$nav_items = array(
			'questions' => __('Q&A', 'tutor'),
			'announcements' => __('Announcements', 'tutor'),
		);
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

				$file_path = get_attached_file( $attachment );
				$size_bytes = file_exists($file_path) ? filesize( $file_path ) : 0;
				$size       = size_format( $size_bytes, 2 );
				$type = wp_ext2type( $ext );

				$icon = 'default';
				if ( $type && in_array( $type, $font_icons ) ) {
					$icon = $type;
				}

				$data = array(
					'post_id'    => $post_id,
					'id'         => $attachment,
					'url'        => $url,
					'name'       => $title . '.' . $ext,
					'title'      => $title,
					'ext'        => $ext,
					'size'       => $size,
					'size_bytes' => $size_bytes,
					'icon'       => $icon,
				);

				$attachments_arr[] = (object) apply_filters( 'tutor/posts/attachments', $data );
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
		$H = (int) floor( $seconds / 3600);
		$M = (int) floor(($seconds - (3600 * $H) ) / 60);
		$S = (int) round( $seconds - (3600 * $H) - (60 * $M)        );
		return $sign.($H ? $H.':' : '').($H ? str_pad($M, 2, '0', STR_PAD_LEFT) : intval($M)).':'.str_pad($S, 2, 0, STR_PAD_LEFT);
	}

	/**
	 * @param $seconds
	 *
	 * @return array
	 *
	 * Get the playtime in array
	 *
	 * @since v.1.0.0
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
	 * @param $seconds
	 *
	 * @return string
	 *
	 * Convert seconds to human readable time
	 *
	 * @since v.1.0.0
	 */
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

			if ( $video_info && in_array($this->array_get('mime_type', $video_info), $types) ) {
				$path             = get_attached_file( $sourceVideoID );
				$info['playtime'] = $video_info['length_formatted'];
				$info['path']     = $path;
				$info['url']      = wp_get_attachment_url( $sourceVideoID );
				$info['ext']      = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
				$info['type']     = $types[ $info['ext'] ];
			}
		}

		if ($videoSource !== 'html5'){
			$video = maybe_unserialize(get_post_meta($lesson_id, '_video', true));

			$runtimeHours = tutor_utils()->avalue_dot('runtime.hours', $video);
			$runtimeMinutes = tutor_utils()->avalue_dot('runtime.minutes', $video);
			$runtimeSeconds = tutor_utils()->avalue_dot('runtime.seconds', $video);

			$runtimeHours = $runtimeHours ? $runtimeHours : '00';
			$runtimeMinutes = $runtimeMinutes ? $runtimeMinutes : '00';
			$runtimeSeconds = $runtimeSeconds ? $runtimeSeconds : '00';

			$info['playtime'] = "$runtimeHours:$runtimeMinutes:$runtimeSeconds";
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
	 *
	 * @since v.1.0.0
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
	 *
	 * return lesson type icon
	 *
	 * @param int $lesson_id
	 * @param bool $html
	 * @param bool $echo
	 *
	 * @return string
	 *
	 * @since v.1.0.0
	 */

	public function get_lesson_type_icon($lesson_id = 0, $html = false, $echo = false){
		$post_id = $this->get_post_id($lesson_id);
		$video = tutor_utils()->get_video_info($post_id);

		$play_time = false;
		if ($video){
			$play_time = $video->playtime;
		}

		$tutor_lesson_type_icon = $play_time ? 'youtube' : 'document';

		if ($html){
			$tutor_lesson_type_icon = "<i class='tutor-icon-$tutor_lesson_type_icon'></i> ";
		}

		if ($tutor_lesson_type_icon){
			echo $tutor_lesson_type_icon;
		}

		return $tutor_lesson_type_icon;
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
	 * @return array|bool|null|object
	 *
	 * Determine if a course completed
	 *
	 * @since v.1.0.0
	 *
	 * @updated v.1.4.9
	 */

	public function is_completed_course($course_id = 0, $user_id = 0){
		if ( ! is_user_logged_in()){
			return apply_filters('is_completed_course', false, $course_id, $user_id);
		}

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
			return apply_filters('is_completed_course', $is_completed, $course_id, $user_id);
		}

		return apply_filters('is_completed_course', false, $course_id, $user_id);
	}

	/**
	 * @param array $input
	 *
	 * @return array
	 *
	 * Sanitize input array
	 *
	 * @since v.1.0.0
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

	/**
	 * @param int $post_id
	 *
	 * @return array|bool
	 *
	 * Determine if has any video in single
	 *
	 * @since v.1.0.0
	 */

	public function has_video_in_single($post_id = 0){
		if (is_single()) {
			$post_id = $this->get_post_id($post_id);

			$video = $this->get_video( $post_id );
			if ( $video && $this->array_get('source', $video) !== '-1' ) {
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
	 *
	 * @since v.1.0.0
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

	/**
	 * @param int $user_id
	 *
	 * @return array
	 *
	 * Get complete courses ids by user
	 *
	 * @since v.1.0.0
	 */
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
	 *
	 * @since v.1.0.0
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
                'posts_per_page' => -1
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
	 *
	 * @since v.1.0.0
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
                'posts_per_page' => -1,
			);

			return new \WP_Query($course_args);
		}

		return false;
	}

	/**
	 * @param int $user_id
	 *
	 * @return array
	 *
	 * Get enrolled course ids by a user
	 *
	 * @since v.1.0.0
	 */

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
                'posts_per_page' => -1
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

	/**
	 * @param int $post_id
	 *
	 * @return bool|false|int
	 *
	 * Get current post id or given post id
	 *
	 * @since v.1.0.0
	 */
	public function get_post_id($post_id = 0){
		if ( ! $post_id){
			$post_id = get_the_ID();
			if ( ! $post_id){
				return false;
			}
		}

		return $post_id;
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool|int
	 *
	 * Get current user or given user ID
	 *
	 * @since v.1.0.0
	 */
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
	 *
	 * @since v.1.0.0
	 */
	public function mark_lesson_complete($post_id = 0, $user_id = 0){
		$post_id = $this->get_post_id($post_id);
		$user_id = $this->get_user_id($user_id);

		do_action('tutor_mark_lesson_complete_before', $post_id, $user_id);
		update_user_meta($user_id, '_tutor_completed_lesson_id_'.$post_id, tutor_time());
		do_action('tutor_mark_lesson_complete_after', $post_id, $user_id);
	}

	/**
	 *
	 * @param int $course_id
	 * @param int $order_id
	 * @param int $user_id
	 *
	 * Saving enroll information to posts table
	 * post_author = enrolled_student_id (wp_users id)
	 * post_parent = enrolled course id
	 *
	 * @type: call when need
	 * @return bool;
	 *
	 * @since v.1.0.0
	 * @updated v.1.4.3
	 *
	 * @return bool
	 */

	public function do_enroll($course_id = 0, $order_id = 0, $user_id = 0){
		if ( ! $course_id){
			return false;
		}

		do_action('tutor_before_enroll', $course_id);
		$user_id = $this->get_user_id($user_id);
		$title = __('Course Enrolled', 'tutor')." &ndash; ".date(get_option('date_format')) .' @ '.date(get_option('time_format') ) ;

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
			update_user_meta( $user_id, '_is_tutor_student', tutor_time() );

			if ($order_id) {
				//Mark order for course and user
				$product_id = $this->get_course_product_id($course_id);
				update_post_meta( $isEnrolled, '_tutor_enrolled_by_order_id', $order_id );
				update_post_meta( $isEnrolled, '_tutor_enrolled_by_product_id', $product_id );
				update_post_meta( $order_id, '_is_tutor_order_for_course', tutor_time() );
				update_post_meta( $order_id, '_tutor_order_for_course_id_'.$course_id, $isEnrolled );
			}
			return true;
		}

		return false;
	}

    /**
     * @param bool $enrol_id
     * @param string $new_status
     *
     * Enrol Status change
     *
     * @since v.1.6.1
     */

	public function course_enrol_status_change($enrol_id = false, $new_status = ''){
	    if ( ! $enrol_id){
	        return;
        }

	    global $wpdb;

	    do_action('tutor/course/enrol_status_change/before',$enrol_id, $new_status );
        $wpdb->update( $wpdb->posts, array( 'post_status' => $new_status ), array( 'ID' => $enrol_id ) );
        do_action('tutor/course/enrol_status_change/after',$enrol_id, $new_status );
    }


    /**
     * @param int $course_id
     * @param int $user_id
     * @param string $cancel_status
     */
	public function cancel_course_enrol($course_id = 0, $user_id = 0, $cancel_status = 'canceled'){
	    $course_id = $this->get_post_id($course_id);
	    $user_id = $this->get_user_id($user_id);

	    $enrolled = $this->is_enrolled($course_id, $user_id);

	    if ($enrolled){
	        global $wpdb;

	        if ($cancel_status === 'delete'){
	            $wpdb->delete($wpdb->posts, array('post_type' => 'tutor_enrolled', 'post_author' => $user_id, 'post_parent' => $course_id));

	            //Delete Related Meta Data
	            delete_post_meta($enrolled->ID, '_tutor_enrolled_by_product_id');
	            $order_id = get_post_meta($enrolled->ID, '_tutor_enrolled_by_order_id', true);
	            if ($order_id){
	                delete_post_meta($enrolled->ID, '_tutor_enrolled_by_order_id');
	                delete_post_meta($order_id, '_is_tutor_order_for_course');
					delete_post_meta($order_id, '_tutor_order_for_course_id_'.$course_id);
					
					do_action('tutor_enrollment/after/delete', $enrolled->ID);
                }
            }else{
	            $wpdb->update($wpdb->posts, array('post_status' => $cancel_status), array('post_type' => 'tutor_enrolled', 'post_author' => $user_id, 'post_parent' => $course_id) );
			
				if ($cancel_status === 'cancel'){
					do_action('tutor_enrollment/after/cancel', $enrolled->ID);
				}
			}
        }
    }

	/**
	 * @param $order_id
	 *
	 * Complete course enrollment and do some task
	 *
	 * @since v.1.0.0
	 */
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
	 *
	 * @since v.1.0.0
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
				$course_enrolled_by_order[] = array('course_id' => $course_id, 'enrolled_id' => $courses_id->meta_value, 'order_id' => $courses_id->post_id );
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

	/**
	 * @return array|null|object
	 *
	 * Get EDD Products
	 */
	public function get_edd_products(){
		global $wpdb;
		$query = $wpdb->get_results("SELECT ID, post_title from {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'download' ");

		return $query;
	}

	/**
	 * @param int $course_id
	 *
	 * @return int
	 *
	 * Get course productID
	 *
	 * @since v.1.0.0
	 */
	public function get_course_product_id($course_id = 0){
		$course_id = $this->get_post_id($course_id);
		$product_id =  (int) get_post_meta($course_id, '_tutor_course_product_id', true);

		return $product_id;
	}

	/**
	 * @param int $product_id
	 *
	 * @return array|null|object|void
	 *
	 * Get Product belongs with course
	 *
	 * @since v.1.0.0
	 */

	public function product_belongs_with_course($product_id = 0){
		global $wpdb;

		$query = $wpdb->get_row("select * from {$wpdb->postmeta} WHERE meta_key='_tutor_course_product_id' AND meta_value = {$product_id} limit 1 ");
		return $query;
	}

	/**
	 * #End WooCommerce specific utils
	 *
	 * @since v.1.0.0
	 */

	public function get_enrolled_statuses(){
		return apply_filters(
			'tutor_get_enrolled_statuses',
			array (
				'pending',
				'processing',
				'on-hold',
				'completed',
				'cancelled',
				'refunded',
				'failed',
			)
		);
	}

	/**
	 * @param $order_id
	 *
	 * @return mixed
	 *
	 * determine is this a tutor order
	 *
	 * @since v.1.0.0
	 */
	public function is_tutor_order($order_id){
		return get_post_meta($order_id, '_is_tutor_order_for_course', true);
	}

	/**
	 * @return mixed
	 *
	 * @deprecated
	 */
	public function tutor_student_dashboard_pages(){
		_deprecated_function(__METHOD__, '1.1.2', 'tutor_dashboard_pages');
		return $this->tutor_dashboard_pages();
	}

	/**
	 * @return mixed
	 *
	 * Tutor Dashboard Pages, supporting for the URL rewriting
	 *
	 * @since v.1.0.0
	 */

	public function tutor_dashboard_pages(){
		$nav_items = apply_filters('tutor_dashboard/nav_items', array(
			'index'             => __('Dashboard', 'tutor'),
			'my-profile'        => __('My Profile', 'tutor'),
			'enrolled-courses'  => __('Enrolled Courses', 'tutor'),
			'wishlist'          => __('Wishlist', 'tutor'),
			'reviews'           => __('Reviews', 'tutor'),
			'my-quiz-attempts'  => __('My Quiz Attempts', 'tutor'),
			'purchase_history'  => __('Purchase History', 'tutor'),
		));

		$instructor_nav_items = apply_filters('tutor_dashboard/instructor_nav_items', array(
			'separator-1'     	=> array('title' => __('Instructor', 'tutor'), 'auth_cap' => tutor()->instructor_role, 'type' => 'separator'),
			'create-course'     => array('title' => __('Create Course', 'tutor'), 'show_ui' => false, 'auth_cap' => tutor()->instructor_role),
			'my-courses'        => array('title' => __('My Courses', 'tutor'), 'auth_cap' => tutor()->instructor_role),
			'earning'           => array('title' => __('Earnings', 'tutor'), 'auth_cap' => tutor()->instructor_role),
			'withdraw'          => array('title' => __('Withdrawals', 'tutor'), 'auth_cap' => tutor()->instructor_role),
			'quiz-attempts'     => array('title' => __('Quiz Attempts', 'tutor'), 'auth_cap' => tutor()->instructor_role),
			'question-answer'   => array('title' => __('Question & Answer', 'tutor'), 'auth_cap' => tutor()->instructor_role),
		));

		$disable = get_tutor_option('disable_course_review');
		if ($disable && isset($nav_items['reviews'])){
			unset($nav_items['reviews']);
		}

		$nav_items = array_merge($nav_items, $instructor_nav_items);

		$new_navs = apply_filters('tutor_dashboard/bottom_nav_items', array(
			'separator-2'     	=> array('title' => '', 'type' => 'separator'),
			'settings'          => __('Settings', 'tutor'),
			'logout'            => __('Logout', 'tutor'),
		));
		$all_nav_items = array_merge($nav_items, $new_navs);

		return apply_filters('tutor_dashboard/nav_items_all', $all_nav_items);
	}

	public function tutor_dashboard_permalinks(){
		$dashboard_pages = $this->tutor_dashboard_pages();

		$dashboard_permalinks = apply_filters('tutor_dashboard/permalinks', array(
			'retrieve-password' => array('title' => __('Retrieve Password', 'tutor'), 'login_require' => false),
		));

		$dashboard_pages = array_merge($dashboard_pages, $dashboard_permalinks);

		return $dashboard_pages;
	}

	/**
	 * @return mixed
	 *
	 * Tutor Dashboard UI nav, only for using in the nav, it's handling user permission based
	 *  Dashboard nav items
	 *
	 * @since v.1.3.4
	 */
	public function tutor_dashboard_nav_ui_items(){
		$nav_items = $this->tutor_dashboard_pages();

		foreach ($nav_items as $key => $nav_item){
			if (is_array($nav_item)){

				if ( isset($nav_item['show_ui']) && ! tutor_utils()->array_get('show_ui', $nav_item)){
					unset($nav_items[$key]);
				}
				if (  isset($nav_item['auth_cap'] ) && ! current_user_can($nav_item['auth_cap']) ){
					unset($nav_items[$key]);
				}
			}
		}

		return apply_filters('tutor_dashboard/nav_ui_items', $nav_items);
	}

	/**
	 * @param string $page_key
	 * @param int $page_id
	 *
	 * @return string
	 *
	 * Get tutor dashboard page single URL
	 *
	 * @since v.1.0.0
	 */
	public function get_tutor_dashboard_page_permalink($page_key = '', $page_id = 0){
		if ($page_key === 'index'){
			$page_key = '';
		}
		if ( ! $page_id){
            $page_id = (int) tutils()->get_option('tutor_dashboard_page_id');
        }
		return trailingslashit(get_permalink($page_id)).$page_key;
	}

	/**
	 * @param string $input
	 *
	 * @return array|bool|mixed|string
	 *
	 * Get old input
	 *
	 * @since v.1.0.0
	 * @updated v.1.4.2
	 */
	public function input_old($input = '', $old_data = null){
		if ( ! $old_data){
			$old_data = $_REQUEST;
		}
		$value = $this->avalue_dot($input, $old_data);
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

	/**
	 * @param int $user_id
	 * @param bool $status_name
	 *
	 * @return bool|mixed
	 *
	 * Instructor status
	 *
	 * @since v.1.0.0
	 */
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

	/**
	 * @param string $search_term
	 *
	 * @return int
	 *
	 * Get total number of instructors
	 *
	 * @since v.1.0.0
	 */

	public function get_total_instructors($search_term = ''){
		$meta_key = '_is_tutor_instructor';

		global $wpdb;

		if ($search_term){
			$search_term = " AND ( {$wpdb->users}.display_name LIKE '%{$search_term}%' OR {$wpdb->users}.user_email LIKE '%{$search_term}%' ) ";
		}

		$count = $wpdb->get_var("SELECT COUNT({$wpdb->users}.ID) FROM {$wpdb->users} INNER JOIN {$wpdb->usermeta} ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ) WHERE 1=1 AND ( {$wpdb->usermeta}.meta_key = '{$meta_key}' ) $search_term ");

		return (int) $count;
	}

	/**
	 * @param int $start
	 * @param int $limit
	 * @param string $search_term
	 *
	 * @return array|null|object
	 *
	 * Get all instructors
	 *
	 * @since v.1.0.0
	 */
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

	/**
	 * @param int $course_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get all instructors by course
	 *
	 * @since v.1.0.0
	 */
	public function get_instructors_by_course($course_id = 0){
		global $wpdb;
		$course_id = $this->get_post_id($course_id);

		$instructors = $wpdb->get_results("select ID, display_name, 
			get_course.meta_value as taught_course_id,
			tutor_job_title.meta_value as tutor_profile_job_title,
			tutor_bio.meta_value as tutor_profile_bio,
			tutor_photo.meta_value as tutor_profile_photo
			from {$wpdb->users}
			INNER JOIN {$wpdb->usermeta} get_course ON ID = get_course.user_id AND get_course.meta_key = '_tutor_instructor_course_id' AND get_course.meta_value = {$course_id}
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
	 * @return int
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
	 *
	 * @since v.1.0.0
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
	 *
	 * @since v.1.0.0
	 */
	public function star_rating_generator($current_rating = 0.00, $echo = true){
		$output = '<div class="tutor-star-rating-group">';

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

		$output .= "<div class='tutor-rating-gen-input'><input type='hidden' name='tutor_rating_gen_input' value='{$current_rating}' /> </div>";

		$output .= "</div>";

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
	 *
	 * @since v.1.0.0
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

	/**
	 * @param $user_id
	 *
	 * @return array|null|object|void
	 *
	 * Get tutor user
	 *
	 * @since v.1.0.0
	 */

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
			LEFT JOIN {$wpdb->users}
			ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			WHERE {$wpdb->comments}.comment_post_ID = {$course_id} 
			AND comment_type = 'tutor_course_rating' AND meta_key = 'tutor_rating' ORDER BY comment_ID DESC LIMIT {$offset},{$limit} ;"
		);

		return $reviews;
	}

	/**
	 * @param int $course_id
	 *
	 * @return object
	 *
	 * Get course rating
	 *
	 * @since v.1.0.0
	 */
	public function get_course_rating($course_id = 0){
		$course_id = $this->get_post_id($course_id);

		$ratings = array(
			'rating_count'  => 0,
			'rating_sum'    => 0,
			'rating_avg'    => 0.00,
			'count_by_value'    => array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0)
		);

		global $wpdb;

		$rating = $wpdb->get_row("select COUNT(meta_value) as rating_count, SUM(meta_value) as rating_sum 
			from {$wpdb->comments}
			INNER JOIN {$wpdb->commentmeta} 
			ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
			WHERE {$wpdb->comments}.comment_post_ID = {$course_id} 
			AND {$wpdb->comments}.comment_type = 'tutor_course_rating'
			AND meta_key = 'tutor_rating' ;"
		);

		if ($rating->rating_count){
			$avg_rating = number_format(($rating->rating_sum / $rating->rating_count), 2);

			/**
			 * Get individual Rating by integer
			 */
			$five_stars_count = $wpdb->get_var("select COUNT(meta_value) as rating_count
				from {$wpdb->comments}
				INNER JOIN {$wpdb->commentmeta} ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
				WHERE {$wpdb->comments}.comment_post_ID = {$course_id} 
				AND {$wpdb->comments}.comment_type = 'tutor_course_rating'
				AND meta_key = 'tutor_rating' AND meta_value = 5 ;"
			);
			$four_stars_count = $wpdb->get_var("select COUNT(meta_value) as rating_count
				from {$wpdb->comments}
				INNER JOIN {$wpdb->commentmeta} ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
				WHERE {$wpdb->comments}.comment_post_ID = {$course_id} 
				AND {$wpdb->comments}.comment_type = 'tutor_course_rating'
				AND meta_key = 'tutor_rating' AND meta_value = 4 ;"
			);
			$three_stars_count = $wpdb->get_var("select COUNT(meta_value) as rating_count
				from {$wpdb->comments}
				INNER JOIN {$wpdb->commentmeta} ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
				WHERE {$wpdb->comments}.comment_post_ID = {$course_id} 
				AND {$wpdb->comments}.comment_type = 'tutor_course_rating'
				AND meta_key = 'tutor_rating' AND meta_value = 3 ;"
			);
			$two_stars_count = $wpdb->get_var("select COUNT(meta_value) as rating_count
				from {$wpdb->comments}
				INNER JOIN {$wpdb->commentmeta} ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
				WHERE {$wpdb->comments}.comment_post_ID = {$course_id} 
				AND {$wpdb->comments}.comment_type = 'tutor_course_rating'
				AND meta_key = 'tutor_rating' AND meta_value = 2 ;"
			);
			$one_stars_count = $wpdb->get_var("select COUNT(meta_value) as rating_count
				from {$wpdb->comments}
				INNER JOIN {$wpdb->commentmeta} ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
				WHERE {$wpdb->comments}.comment_post_ID = {$course_id} 
				AND {$wpdb->comments}.comment_type = 'tutor_course_rating'
				AND meta_key = 'tutor_rating' AND meta_value = 1 ;"
			);

			$ratings = array(
				'rating_count'  => $rating->rating_count,
				'rating_sum'    => $rating->rating_sum,
				'rating_avg'    => $avg_rating,
				'count_by_value'    => array(5 => $five_stars_count, 4 => $four_stars_count, 3 => $three_stars_count, 2 => $two_stars_count, 1 => $one_stars_count)
			);

		}

		return (object) $ratings;
	}

	/**
	 * @param int $user_id
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array|null|object
	 *
	 * Get reviews by a user
	 *
	 * @since v.1.0.0
	 */
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
			AND comment_type = 'tutor_course_rating' AND meta_key = 'tutor_rating' ORDER BY comment_ID DESC LIMIT {$offset},{$limit} ;"
		);

		return $reviews;
	}

	/**
	 * @param int $user_id
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array|null|object
	 *
	 * Get reviews by instructor
	 *
	 * @since v.1.4.0
	 */

	public function get_reviews_by_instructor($instructor_id = 0, $offset = 0, $limit = 150){
		$instructor_id = $this->get_user_id($instructor_id);
		global $wpdb;

		$results = array(
			'count'     => 0,
			'results'   => false,
		);

		$cours_ids = (array) $this->get_assigned_courses_ids_by_instructors($instructor_id);

		if ($this->count($cours_ids)){
			$implode_ids = implode( ',', $cours_ids );

			//Count
			$results['count'] = $wpdb->get_var("select COUNT({$wpdb->comments}.comment_ID)
			from {$wpdb->comments}
			INNER JOIN {$wpdb->commentmeta} 
			ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
			INNER  JOIN {$wpdb->users}
			ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			WHERE {$wpdb->comments}.comment_post_ID IN({$implode_ids}) 
			AND comment_type = 'tutor_course_rating' AND meta_key = 'tutor_rating';" );

			//Results
			$results['results'] = $wpdb->get_results("select {$wpdb->comments}.comment_ID, 
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
			WHERE {$wpdb->comments}.comment_post_ID IN({$implode_ids}) 
			AND comment_type = 'tutor_course_rating' AND meta_key = 'tutor_rating' ORDER BY comment_ID DESC LIMIT {$offset},{$limit} ;" );
		}

		return (object) $results;
	}

	/**
	 * @param $instructor_id
	 *
	 * @return object
	 *
	 * Get instructors rating
	 *
	 * @since v.1.0.0
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
	 *
	 * @since v.1.0.0
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
	 *
	 * @since v.1.0.0
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
	 *
	 * @since v.1.0.0
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

	/**
	 * @return array
	 *
	 * Get Database version
	 *
	 * @since v.1.0.0
	 */
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

	/**
	 * @param int $course_id
	 * @param int $user_id
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array|null|object
	 *
	 * Get top question
	 *
	 * @since v.1.0.0
	 */
	public function get_top_question($course_id = 0, $user_id = 0, $offset = 0, $limit = 20, $is_author = false ){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);

		global $wpdb;
		$author_sql = $is_author ? "" : "AND {$wpdb->comments}.user_id = {$user_id}";
		
		$questions = $wpdb->get_results(
			"SELECT 
				{$wpdb -> comments}.comment_ID, 
				{$wpdb -> comments}.comment_post_ID, 
				{$wpdb -> comments}.comment_author, 
				{$wpdb -> comments}.comment_date, 
				{$wpdb -> comments}.comment_content, 
				{$wpdb -> comments}.user_id, 
				{$wpdb -> commentmeta}.meta_value as question_title, 
				{$wpdb -> users}.display_name 
			FROM 
				{$wpdb -> comments} 
				INNER JOIN {$wpdb -> commentmeta} ON {$wpdb -> comments}.comment_ID = {$wpdb -> commentmeta}.comment_id 
				INNER JOIN {$wpdb -> users} ON {$wpdb -> comments}.user_id = {$wpdb -> users}.ID 
			WHERE 
				{$wpdb -> comments}.comment_post_ID = {$course_id} {$author_sql} 
				AND {$wpdb -> comments}.comment_type = 'tutor_q_and_a' 
				AND meta_key = 'tutor_question_title' 
			ORDER BY 
				comment_ID DESC 
			LIMIT 
				{$offset}, 
				{$limit};
			"
		);

		return $questions;
	}

	/**
	 * @param string $search_term
	 *
	 * @return int
	 *
	 * Get total number of Q&A questions
	 *
	 * @since v.1.0.0
	 */
	public function get_total_qa_question($search_term = ''){
		global $wpdb;

		if ($search_term){
			$search_term = " AND {$wpdb->commentmeta}.meta_value LIKE '%{$search_term}%' ";
		}

		$user_id = get_current_user_id();
		$course_type = tutor()->course_post_type;

		$in_question_id_query = '';
		/**
		 * Get only assinged  courses questions if current user is a
		 */
		if ( ! current_user_can('administrator') && current_user_can(tutor()->instructor_role)) {
			$get_course_ids           = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_author = {$user_id} AND post_type = '{$course_type}' AND post_status = 'publish' " );
			$get_assigned_courses_ids = $wpdb->get_col( "SELECT meta_value from {$wpdb->usermeta} WHERE meta_key = '_tutor_instructor_course_id' AND user_id = {$user_id}  " );
			$my_course_ids            = array_unique( array_merge( $get_course_ids, $get_assigned_courses_ids ) );

			if ( $this->count( $my_course_ids ) ) {
				$implode_ids = implode( ',', $my_course_ids );
				$in_question_id_query = " AND {$wpdb->comments}.comment_post_ID IN($implode_ids) ";
			}
		}

		$count = $wpdb->get_var(
			"SELECT 
				COUNT({$wpdb -> comments}.comment_ID) 
			FROM 
				{$wpdb -> comments} 
				INNER JOIN {$wpdb -> commentmeta} ON {$wpdb -> comments}.comment_ID = {$wpdb -> commentmeta}.comment_id 
			WHERE 
				comment_type = 'tutor_q_and_a' 
				AND comment_parent = 0 {$in_question_id_query} {$search_term};
			"
		);

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

		$user_id = get_current_user_id();
		$course_type = tutor()->course_post_type;

		$in_question_id_query = '';
		/**
		 * Get only assinged  courses questions if current user is a
		 */
		if ( ! current_user_can('administrator') && current_user_can(tutor()->instructor_role)) {
			$get_course_ids           = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_author = {$user_id} AND post_type = '{$course_type}' AND post_status = 'publish' " );
			$get_assigned_courses_ids = $wpdb->get_col( "SELECT meta_value from {$wpdb->usermeta} WHERE meta_key = '_tutor_instructor_course_id' AND user_id = {$user_id}  " );
			$my_course_ids            = array_unique( array_merge( $get_course_ids, $get_assigned_courses_ids ) );

			if ( $this->count( $my_course_ids ) ) {
				$implode_ids = implode( ',', $my_course_ids );
				$in_question_id_query = " AND {$wpdb->comments}.comment_post_ID IN($implode_ids) ";
			}
		}

		$query = $wpdb->get_results(
			"SELECT 
				{$wpdb -> comments}.comment_ID, 
				{$wpdb -> comments}.comment_post_ID, 
				{$wpdb -> comments}.comment_author, 
				{$wpdb -> comments}.comment_date, 
				{$wpdb -> comments}.comment_content, 
				{$wpdb -> comments}.user_id, 
				{$wpdb -> commentmeta}.meta_value as question_title, 
				{$wpdb -> users}.display_name, 
				{$wpdb -> posts}.post_title, 
				(
				SELECT 
					COUNT(answers_t.comment_ID) 
				FROM 
					{$wpdb -> comments} answers_t 
				WHERE 
					answers_t.comment_parent = {$wpdb -> comments}.comment_ID
				) as answer_count 
			FROM 
				{$wpdb -> comments} 
				INNER JOIN {$wpdb -> commentmeta} ON {$wpdb -> comments}.comment_ID = {$wpdb -> commentmeta}.comment_id 
				INNER JOIN {$wpdb -> posts} ON {$wpdb -> comments}.comment_post_ID = {$wpdb -> posts}.ID 
				INNER JOIN {$wpdb -> users} ON {$wpdb -> comments}.user_id = {$wpdb -> users}.ID 
			WHERE 
				{$wpdb -> comments}.comment_type = 'tutor_q_and_a' 
				AND {$wpdb -> comments}.comment_parent = 0 {$search_term} {$in_question_id_query} 
			ORDER BY 
				{$wpdb -> comments}.comment_ID DESC 
			LIMIT 
				{$start}, 
				{$limit};
			"
		);

		return $query;
	}

	/**
	 * @param $question_id
	 *
	 * @return array|null|object|void
	 *
	 * Get question for Q&A
	 *
	 * @since v.1.0.0
	 */
	public function get_qa_question($question_id){
		global $wpdb;
		$query = $wpdb->get_row(
			"SELECT 
				{$wpdb -> comments}.comment_ID, 
				{$wpdb -> comments}.comment_post_ID, 
				{$wpdb -> comments}.comment_author, 
				{$wpdb -> comments}.comment_date, 
				{$wpdb -> comments}.comment_content, 
				{$wpdb -> comments}.user_id, 
				{$wpdb -> commentmeta}.meta_value as question_title, 
				{$wpdb -> users}.display_name, 
				{$wpdb -> posts}.post_title 
			FROM 
				{$wpdb -> comments} 
				INNER JOIN {$wpdb -> commentmeta} ON {$wpdb -> comments}.comment_ID = {$wpdb -> commentmeta}.comment_id 
				INNER JOIN {$wpdb -> posts} ON {$wpdb -> comments}.comment_post_ID = {$wpdb -> posts}.ID 
				INNER JOIN {$wpdb -> users} ON {$wpdb -> comments}.user_id = {$wpdb -> users}.ID 
			WHERE 
				comment_type = 'tutor_q_and_a' 
				AND {$wpdb -> comments}.comment_ID = {$question_id};
			"
		);

		return $query;
	}

	/**
	 * @param $question_id
	 *
	 * @return array|null|object
	 *
	 * Get question and asnwer by question
	 */
	public function get_qa_answer_by_question($question_id){
		global $wpdb;
		$query = $wpdb->get_results(
			"SELECT 
				{$wpdb -> comments}.comment_ID, 
				{$wpdb -> comments}.comment_post_ID, 
				{$wpdb -> comments}.comment_author, 
				{$wpdb -> comments}.comment_date, 
				{$wpdb -> comments}.comment_content, 
				{$wpdb -> comments}.comment_parent, 
				{$wpdb -> comments}.user_id, 
				{$wpdb -> users}.display_name 
			FROM 
				{$wpdb -> comments} 
				INNER JOIN {$wpdb -> users} ON {$wpdb -> comments}.user_id = {$wpdb -> users}.ID 
			WHERE 
				comment_type = 'tutor_q_and_a' 
				AND {$wpdb -> comments}.comment_parent = {$question_id} 
			ORDER BY 
				{$wpdb -> comments}.comment_ID ASC;
			"
		);

		return $query;
	}

	/**
	 * @param $answer_id
	 *
	 * @return array|null|object
	 * 
	 * @since v1.6.9
	 *
	 * Get question and asnwer by answer_id
	 */
	public function get_qa_answer_by_answer_id($answer_id) {
		global $wpdb;
		$answer = $wpdb->get_row("
			SELECT 	answer.comment_post_ID, 
					answer.comment_content, 
					users.display_name,
					question.user_id AS question_by,
					question.comment_content AS question,
					question_meta.meta_value AS question_title
			FROM   {$wpdb -> comments} answer
					INNER JOIN {$wpdb -> users} users
							ON answer.user_id = users.id 
					INNER JOIN {$wpdb -> comments} question 
							ON answer.comment_parent = question.comment_ID 
					INNER JOIN {$wpdb -> commentmeta} question_meta 
							ON answer.comment_parent = question_meta.comment_id
							AND question_meta.meta_key = 'tutor_question_title'
			WHERE  	answer.comment_ID = {$answer_id} 
					AND answer.comment_type = 'tutor_q_and_a';
			"
		);

		if ( $answer ) {
			return $answer;
		}

		return false;
	}

	public function unanswered_question_count(){
		global $wpdb;
		$count = $wpdb->get_var(
			"SELECT 
				COUNT({$wpdb -> comments}.comment_ID) 
			FROM 
				{$wpdb -> comments} 
				INNER JOIN {$wpdb -> posts} ON {$wpdb -> comments}.comment_post_ID = {$wpdb -> posts}.ID 
				INNER JOIN {$wpdb -> users} ON {$wpdb -> comments}.user_id = {$wpdb -> users}.ID 
			WHERE 
				{$wpdb -> comments}.comment_type = 'tutor_q_and_a' 
				AND {$wpdb -> comments}.comment_approved = 'waiting_for_answer' 
				AND {$wpdb -> comments}.comment_parent = 0;
			"
		);
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
		$query = $wpdb->get_results(
			"SELECT 
				{$wpdb -> posts}.ID, 
				post_author, 
				post_date, 
				post_content, 
				post_title, 
				display_name 
			FROM 
				{$wpdb -> posts} 
				INNER JOIN {$wpdb -> users} ON post_author = {$wpdb -> users}.ID 
			WHERE 
				post_type = 'tutor_announcements' 
				AND post_parent = {$course_id} 
			ORDER BY 
				{$wpdb -> posts}.ID DESC;
			"
		);
		return $query;
	}

	/**
	 * @param string $content
	 *
	 * @return mixed
	 *
	 * Announcement content
	 *
	 * @since v.1.0.0
	 */

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

		if ( ! $option_key && ! empty($get_option_meta)) {
			return $get_option_meta;
		}

		$value = $this->avalue_dot( $option_key, $get_option_meta );
		if ( $value > 0 || $value !== false ) {
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

		$questions = $wpdb->get_results("SELECT * from {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id = {$quiz_id} ORDER BY question_order ASC ");

		if (is_array($questions) && count($questions)){
			return $questions;
		}
		return false;
	}

	/**
	 * @param int $question_id
	 *
	 * @return array|bool|object|void|null
	 *
	 * Get Quiz question by question id
	 */
	public function get_quiz_question_by_id($question_id = 0){
		global $wpdb;

		if ($question_id){
			$question = $wpdb->get_row("SELECT * from {$wpdb->prefix}tutor_quiz_questions WHERE question_id = {$question_id} LIMIT 0,1 ;");
			return $question;
		}

		return false;
	}

	/**
	 * @param null $type
	 *
	 * @return array|mixed
	 *
	 * Get all question types
	 *
	 * @since v.1.0.0
	 */

	public function get_question_types($type = null){
		$types = array(
			'true_false'        	=> array('name' => __('True/False', 'tutor'), 'icon' => '<span class="tooltip-btn" data-tooltip="True/False"><i class="tutor-icon-block tutor-icon-yes-no"></i></span>', 'is_pro' => false),
			'single_choice'     	=> array('name' => __('Single Choice', 'tutor'), 'icon' => '<span class="tooltip-btn" data-tooltip="Single Choice"><i class="tutor-icon-block tutor-icon-mark"></i></span>', 'is_pro' => false),
			'multiple_choice'   	=> array('name' => __('Multiple Choice', 'tutor'), 'icon' => '<span class="tooltip-btn" data-tooltip="Multiple Choicee"><i class="tutor-icon-block tutor-icon-multiple-choice"></i></span>', 'is_pro' => false),
			'open_ended'        	=> array('name' => __('Open Ended/Essay', 'tutor'), 'icon' => '<span class="tooltip-btn" data-tooltip="Open/Essay"><i class="tutor-icon-block tutor-icon-open-ended"></i></span>', 'is_pro' => false),
			'fill_in_the_blank'  	=> array('name' => __('Fill In The Blanks', 'tutor'), 'icon' => '<span class="tooltip-btn" data-tooltip="Fill In The Blanks"><i class="tutor-icon-block tutor-icon-fill-gaps"></i></span>', 'is_pro' => false),
			'short_answer'          => array('name' => __('Short Answer', 'tutor'), 'icon' => '<span class="tooltip-btn" data-tooltip="Short Answer"><i class="tutor-icon-block tutor-icon-short-ans"></i></span>', 'is_pro' => true),
			'matching'              => array('name' => __('Matching', 'tutor'), 'icon' => '<span class="tooltip-btn" data-tooltip="Matching"><i class="tutor-icon-block tutor-icon-matching"></i></span>', 'is_pro' => true),
			'image_matching'        => array('name' => __('Image Matching', 'tutor'), 'icon' => '<span class="tooltip-btn" data-tooltip="Image Matching"><i class="tutor-icon-block tutor-icon-image-matching"></i></span>', 'is_pro' => true),
			'image_answering'       => array('name' => __('Image Answering', 'tutor'), 'icon' => '<span class="tooltip-btn" data-tooltip="Image Answering"><i class="tutor-icon-block tutor-icon-image-ans"></i></span>', 'is_pro' => true),
			'ordering'          	=> array('name' => __('Ordering', 'tutor'), 'icon' => '<span class="tooltip-btn" data-tooltip="Ordering"><i class="tutor-icon-block tutor-icon-ordering"></i></span>', 'is_pro' => true),
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
	 *
	 * @since v.1.0.0
	 */

	public function quiz_next_question_order_id($quiz_id){
		global $wpdb;

		$last_order = (int) $wpdb->get_var("SELECT MAX(question_order) FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id = {$quiz_id} ;");
		return $last_order + 1;
	}

	/**
	 * @param $quiz_id
	 *
	 * @return int
	 *
	 * new design quiz question
	 * @since v.1.0.0
	 */
	public function quiz_next_question_id(){
		global $wpdb;

		$last_order = (int) $wpdb->get_var("SELECT MAX(question_id) FROM {$wpdb->prefix}tutor_quiz_questions;");
		return $last_order + 1;
	}

	public function get_quiz_id_by_question($question_id){
		global $wpdb;

		$quiz_id = $wpdb->get_var("SELECT quiz_id FROM {$wpdb->tutor_quiz_questions} WHERE question_id = {$question_id} ;");
		return $quiz_id;
	}

	/**
	 * @param int $post_id
	 *
	 * @return array|bool|null|object
	 *
	 * @since v.1.0.0
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

	/**
	 * @param $quiz_id
	 *
	 * @return array|bool|null|object|void
	 *
	 * Get course by quiz
	 *
	 * @since v.1.0.0
	 */

	public function get_course_by_quiz($quiz_id){
		global $wpdb;

		$quiz_id = $this->get_post_id($quiz_id);
		$post = get_post($quiz_id);

		if ($post) {
			$course_post_type = tutor()->course_post_type;
			$course = $wpdb->get_row( "select ID, post_author, post_name, post_type, post_parent from {$wpdb->posts} where ID = {$post->post_parent} " );
			if ($course) {
				if ( $course->post_type !== $course_post_type ) {
					$course = $wpdb->get_row( "select ID, post_author, post_name, post_type, post_parent from {$wpdb->posts} where ID = {$course->post_parent} " );
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
	 *
	 * @since v.1.0.0
	 */
	public function total_questions_for_student_by_quiz($quiz_id){
		$quiz_id = $this->get_post_id($quiz_id);
		global $wpdb;

		$max_questions_count = (int) tutor_utils()->get_quiz_option(get_the_ID(), 'max_questions_for_answer');
		$total_question = (int) $wpdb->get_var("select count(question_id) from {$wpdb->tutor_quiz_questions} where quiz_id = {$quiz_id}");

		return min($max_questions_count, $total_question);
	}

	/**
	 * @param int $quiz_id
	 *
	 * @return array|null|object|void
	 *
	 * Determine if there is any started quiz exists
	 *
	 * @since v.1.0.0
	 */

	public function is_started_quiz($quiz_id = 0){
		global $wpdb;

		$quiz_id = $this->get_post_id($quiz_id);
		$user_id = get_current_user_id();

		$is_started = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts WHERE user_id =  {$user_id} AND quiz_id = {$quiz_id} AND attempt_status = 'attempt_started' ");

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
	 *
	 * @since v.1.0.0
	 */

	public function max_questions_for_take_quiz($quiz_id){
		$quiz_id = $this->get_post_id($quiz_id);
		global $wpdb;

		$max_questions = (int) $wpdb->get_var("select count(question_id) from {$wpdb->prefix}tutor_quiz_questions where quiz_id = {$quiz_id}  ");
		$max_mentioned = (int) $this->get_quiz_option($quiz_id, 'max_questions_for_answer', 10);

		if ($max_mentioned < $max_questions ){
			return $max_mentioned;
		}

		return $max_questions;
	}

	/**
	 * @param int $attempt_id
	 *
	 * @return array|bool|null|object|void
	 *
	 * Get single quiz attempt
	 *
	 * @since v.1.0.0
	 */
	public function get_attempt($attempt_id = 0){
		global $wpdb;
		if ( ! $attempt_id){
			return false;
		}
		$attempt = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts WHERE attempt_id = {$attempt_id} ");
		return $attempt;
	}

	/**
	 * @param $attempt_info
	 *
	 * @return mixed
	 *
	 * Get unserialize attempt info
	 *
	 * @since v.1.0.0
	 */

	public function quiz_attempt_info($attempt_info){
		return maybe_unserialize($attempt_info);
	}

	/**
	 * @param $quiz_attempt_id
	 * @param array $attempt_info
	 *
	 * @return bool|int
	 *
	 * Update attempt for various action
	 *
	 * @since v.1.0.0
	 */
	public function quiz_update_attempt_info($quiz_attempt_id, $attempt_info = array()){
		$answers = tutor_utils()->avalue_dot('answers', $attempt_info);
		$total_marks = array_sum(wp_list_pluck($answers, 'question_mark'));
		$earned_marks = tutor_utils()->avalue_dot('marks_earned', $attempt_info);
		$earned_mark_percent = $earned_marks > 0 ? ( number_format(($earned_marks * 100) / $total_marks)) : 0;
		update_comment_meta($quiz_attempt_id, 'earned_mark_percent', $earned_mark_percent);

		return update_comment_meta($quiz_attempt_id,'quiz_attempt_info', $attempt_info);
	}

	/**
	 * @param int $quiz_id
	 *
	 * @return array|null|object
	 *
	 * Get random question by quiz id
	 *
	 * @since v.1.0.0
	 */

	public function get_random_question_by_quiz($quiz_id = 0){
		global $wpdb;

		$quiz_id = $this->get_post_id($quiz_id);
		$is_attempt = $this->is_started_quiz($quiz_id);

		$tempSql = " AND question_type = 'matching' ";
		$questions = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id = {$quiz_id} {$tempSql}  ORDER BY RAND() LIMIT 0,1 ");

		return $questions;
	}

	/**
	 * @param int $quiz_id
	 *
	 * @return array|null|object
	 *
	 * Get random questions by quiz
	 */
	public function get_random_questions_by_quiz($quiz_id = 0){
		global $wpdb;

		$quiz_id = $this->get_post_id($quiz_id);
		$attempt = $this->is_started_quiz($quiz_id);
		$total_questions = (int) $attempt->total_questions;
		if ( ! $attempt){
			return false;
		}

		$questions_order = tutor_utils()->get_quiz_option(get_the_ID(), 'questions_order', 'rand');

		$order_by = "";
		if ($questions_order === 'rand'){
			$order_by = "ORDER BY RAND()";
		}elseif ($questions_order === 'asc'){
			$order_by = "ORDER BY question_id ASC";
		}elseif ($questions_order === 'desc'){
			$order_by = "ORDER BY question_id DESC";
		}elseif ($questions_order === 'sorting'){
			$order_by = "ORDER BY question_order ASC";
		}

		$limit = '';
		if ($total_questions){
			$limit = "LIMIT {$total_questions} ";
		}

		$questions = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id = {$quiz_id}  {$order_by} {$limit} ");

		return $questions;
	}

	/**
	 * @param $question_id
	 * @param bool $rand
	 *
	 * @return array|bool|null|object
	 *
	 * Get answers list by quiz question
	 *
	 * @since v.1.0.0
	 */
	public function get_answers_by_quiz_question($question_id, $rand = false){
		global $wpdb;

		$question = $wpdb->get_row("SELECT * from {$wpdb->prefix}tutor_quiz_questions WHERE question_id = {$question_id} ;");
		if ( ! $question){
			return false;
		}

		$order = " answer_order ASC ";
		if ($question->question_type === 'ordering'){
			$order = " RAND() ";
		}

		if ($rand){
			$order = " RAND() ";
		}

		$answers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id = {$question_id} AND belongs_question_type = '{$question->question_type}' order by {$order} ");
		return $answers;
	}

	/**
	 * @param int $quiz_id
	 * @param int $user_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get all of the attempts by an user of a quiz
	 *
	 * @since v.1.0.0
	 */

	public function quiz_attempts($quiz_id = 0, $user_id = 0){
		global $wpdb;

		$quiz_id = $this->get_post_id($quiz_id);
		$user_id = $this->get_user_id($user_id);

		$attempts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts WHERE quiz_id = {$quiz_id} AND user_id = {$user_id} ");

		if (is_array($attempts) && count($attempts)){
			return $attempts;
		}

		return false;
	}

	/**
	 * @param int $quiz_id
	 * @param int $user_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get all ended attempts by an user of a quiz
	 *
	 * @since v.1.4.1
	 */
	public function quiz_ended_attempts($quiz_id = 0, $user_id = 0){
		global $wpdb;

		$quiz_id = $this->get_post_id($quiz_id);
		$user_id = $this->get_user_id($user_id);

		$attempts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts WHERE quiz_id = {$quiz_id} AND user_id = {$user_id} AND attempt_status != 'attempt_started' ");

		if (is_array($attempts) && count($attempts)){
			return $attempts;
		}

		return false;
	}


	/**
	 * @param int $user_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get attempts by an user
	 *
	 * @since v.1.0.0
	 */
	public function get_all_quiz_attempts_by_user($user_id = 0){
		global $wpdb;

		$user_id = $this->get_user_id($user_id);
		$attempts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts WHERE user_id = {$user_id} ORDER BY attempt_id DESC ");

		if (is_array($attempts) && count($attempts)){
			return $attempts;
		}

		return false;
	}

	/**
	 * @param string $search_term
	 *
	 * @return int
	 *
	 * Total number of quiz attempts
	 *
	 * @since v.1.0.0
	 */

	public function get_total_quiz_attempts($search_term = ''){
		global $wpdb;

		if ($search_term){
			$search_term = " AND ( user_email like '%{$search_term}%' OR display_name like '%{$search_term}%' OR post_title like '%{$search_term}%' ) ";
		}

		$count = $wpdb->get_var("SELECT COUNT(attempt_id)
		 	FROM {$wpdb->prefix}tutor_quiz_attempts quiz_attempts
			INNER JOIN {$wpdb->posts} quiz
			ON quiz_attempts.quiz_id = quiz.ID
			INNER  JOIN {$wpdb->users}
			ON quiz_attempts.user_id = {$wpdb->users}.ID
			WHERE 1=1 AND attempt_status != 'attempt_started'  {$search_term} ");
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
	 * Get the all quiz attempts
	 *
	 * @since v.1.0.0
	 */
	public function get_quiz_attempts($start = 0, $limit = 10, $search_term = '') {
		global $wpdb;

		if ($search_term){
			$search_term = " AND ( user_email like '%{$search_term}%' OR display_name like '%{$search_term}%' OR post_title like '%{$search_term}%' ) ";
		}

		$query = $wpdb->get_results("SELECT *
		 	FROM {$wpdb->prefix}tutor_quiz_attempts quiz_attempts
			INNER JOIN {$wpdb->posts} quiz
			ON quiz_attempts.quiz_id = quiz.ID
			INNER  JOIN {$wpdb->users}
			ON quiz_attempts.user_id = {$wpdb->users}.ID
			WHERE 1=1  AND attempt_status != 'attempt_started'  {$search_term} 
			ORDER BY quiz_attempts.attempt_id DESC 
			LIMIT {$start},{$limit}; ");
		return $query;
	}

	public function get_quiz_attempts_by_course_ids($start = 0, $limit = 10, $course_ids = array(), $search_term = '') {
		global $wpdb;

		if ($search_term){
			$search_term = " AND ( user_email like '%{$search_term}%' OR display_name like '%{$search_term}%' OR post_title like '%{$search_term}%' ) ";
		}

		$course_ids_in = implode(',', $course_ids);
		$sql = " AND quiz_attempts.course_id IN({$course_ids_in}) ";
		$search_term = $sql.$search_term;

		$query = $wpdb->get_results("SELECT *
		 	FROM {$wpdb->prefix}tutor_quiz_attempts quiz_attempts
			INNER JOIN {$wpdb->posts} quiz
			ON quiz_attempts.quiz_id = quiz.ID
			INNER  JOIN {$wpdb->users}
			ON quiz_attempts.user_id = {$wpdb->users}.ID
			WHERE 1=1  AND attempt_status != 'attempt_started' {$search_term} 
			ORDER BY quiz_attempts.attempt_id DESC 
			LIMIT {$start},{$limit}; ");
		return $query;
	}

	public function get_total_quiz_attempts_by_course_ids($course_ids = array(), $search_term = ''){
		global $wpdb;

		if ($search_term){
			$search_term = " AND ( user_email like '%{$search_term}%' OR display_name like '%{$search_term}%' OR post_title like '%{$search_term}%' ) ";
		}

		$course_ids_in = implode(',', $course_ids);
		$sql = " AND quiz_attempts.course_id IN({$course_ids_in}) ";
		$search_term = $sql.$search_term;

		$count = $wpdb->get_var("SELECT COUNT(attempt_id)
		 	FROM {$wpdb->prefix}tutor_quiz_attempts quiz_attempts
			INNER JOIN {$wpdb->posts} quiz
			ON quiz_attempts.quiz_id = quiz.ID
			INNER  JOIN {$wpdb->users}
			ON quiz_attempts.user_id = {$wpdb->users}.ID
			WHERE 1=1  AND attempt_status != 'attempt_started' {$search_term} ");
		return (int) $count;
	}

	/**
	 * @param $attempt_id
	 *
	 * @return array|null|object
	 *
	 * Get quiz answers by attempt id
	 *
	 * @since v.1.0.0
	 */
	public function get_quiz_answers_by_attempt_id($attempt_id){
		global $wpdb;

		$results = $wpdb->get_results("SELECT answers.*, question.question_title, question.question_type
		FROM {$wpdb->prefix}tutor_quiz_attempt_answers answers
 		LEFT JOIN {$wpdb->prefix}tutor_quiz_questions question ON answers.question_id = question.question_id
 		WHERE answers.quiz_attempt_id = {$attempt_id} ORDER BY attempt_answer_id ASC ;");

		return $results;
	}

	/**
	 * @param $answer_id
	 *
	 * @return array|null|object
	 *
	 * Get single answer by answer_id
	 *
	 * @since v.1.0.0
	 */
	public function get_answer_by_id($answer_id){
		global $wpdb;

		if (is_array($answer_id)){
			$in_ids = implode(",", $answer_id);
			$sql = "answer.answer_id IN({$in_ids})";
		}else{
			$sql = "answer.answer_id = {$answer_id}";
		}

		$answer = $wpdb->get_results("SELECT answer.*, question.question_title, question.question_type
		FROM {$wpdb->prefix}tutor_quiz_question_answers answer
 		LEFT JOIN {$wpdb->prefix}tutor_quiz_questions question ON answer.belongs_question_id = question.question_id
 		WHERE 1=1 AND {$sql} ");

		return $answer;
	}

	/**
	 * @param $ids
	 *
	 * @return array|bool|null|object
	 *
	 * Get quiz answers by ids
	 *
	 * @since v.1.0.0
	 */

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
	 *
	 * @since v.1.0.0
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

	/**
	 * @return mixed|void
	 *
	 * Get user permalink for dashboard
	 *
	 * @since v.1.0.0
	 */
	public function user_profile_permalinks(){
		$permalinks = array(
			'courses_taken'     => __('Courses Taken', 'tutor'),
		);

		$show_enrolled_course = tutor_utils()->get_option('show_courses_completed_by_student');
		$enable_show_reviews_wrote = tutor_utils()->get_option('students_own_review_show_at_profile');

		if ($show_enrolled_course){
			$permalinks['enrolled_course'] = __('Enrolled Course', 'tutor');
		}
		if ($enable_show_reviews_wrote){
			$permalinks['reviews_wrote'] = __('Reviews Written', 'tutor');
		}


		return apply_filters('tutor_public_profile/permalinks', $permalinks);
	}

	/**
	 * @return bool|false|string
	 *
	 * Student registration form
	 *
	 * @since v.1.0.0
	 */
	public function student_register_url(){
		$student_register_page = (int) $this->get_option('student_register_page');

		if ($student_register_page){
			return get_the_permalink($student_register_page);
		}
		return false;
	}
	/**
	 * @return bool|false|string
	 *
	 * Instructor registration form
	 *
	 * @since v.1.2.13
	 */
	public function instructor_register_url(){
		$instructor_register_page = (int) $this->get_option('instructor_register_page');

		if ($instructor_register_page){
			return get_the_permalink($instructor_register_page);
		}
		return false;
	}

	/**
	 * @return false|string
	 *
	 * Get frontend dashboard URL
	 */
	public function tutor_dashboard_url($sub_url = ''){
		$page_id = (int) tutor_utils()->get_option('tutor_dashboard_page_id');
		$page_id = apply_filters('tutor_dashboard_page_id', $page_id);
		return trailingslashit(get_the_permalink($page_id)).$sub_url;
	}

	/**
	 * Get the tutor dashboard page ID
	 *
	 * @return int
	 *
	 */
	public function dashboard_page_id(){
		$page_id = (int) tutor_utils()->get_option('tutor_dashboard_page_id');
		$page_id = apply_filters('tutor_dashboard_page_id', $page_id);
		return $page_id;
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return bool
	 *
	 * is_wishlisted();
	 *
	 * @since v.1.0.0
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
	 *
	 * @since v.1.0.0
	 */
	public function get_wishlist($user_id = 0){
		$user_id = $this->get_user_id($user_id);
		$course_post_type = tutor()->course_post_type;

		global $wpdb;

		$query = "SELECT $wpdb->posts.*
	    FROM $wpdb->posts
	    LEFT JOIN $wpdb->usermeta ON ($wpdb->posts.ID = $wpdb->usermeta.meta_value)
	    WHERE post_type = '{$course_post_type}' AND post_status = 'publish' AND $wpdb->usermeta.meta_key = '_tutor_course_wishlist'
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
	 *
	 * @since v.1.0.0
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

	/**
	 * @param int $limit
	 *
	 * @return array|bool|null|object
	 *
	 * Get most rated courses lists
	 *
	 * @since v.1.0.0
	 */
	public function most_rated_courses($limit = 10){
		global $wpdb;

		$result = $wpdb->get_results("
		SELECT COUNT(comment_ID) AS total_rating, 
		comment_ID, 
		comment_post_ID,
		course.*
		FROM {$wpdb->comments}
		INNER JOIN {$wpdb->posts} course ON comment_post_ID = course.ID 
		WHERE {$wpdb->comments}.comment_type = 'tutor_course_rating' AND {$wpdb->comments}.comment_approved = 'approved'
		GROUP BY comment_post_ID ORDER BY total_rating DESC  LIMIT 0,{$limit}
		;");

		if (is_array($result) && count($result)){
			return $result;
		}
		return false;
	}

	/**
	 * @param null $addon_field
	 *
	 * @return bool
	 *
	 * Get Addon config
	 *
	 * @since v.1.0.0
	 */
	public function get_addon_config($addon_field = null){
		if ( ! $addon_field){
			return false;
		}

		$addonsConfig = maybe_unserialize(get_option('tutor_addons_config'));

		if (isset($addonsConfig[$addon_field])){
			return $addonsConfig[$addon_field];
		}

		return false;
	}

	/**
	 * @return array|false|string
	 *
	 * Get the IP from visitor
	 *
	 * @since v.1.0.0
	 */
	function get_ip() {
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
			$ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}

	/**
	 * @return array $array
	 *
	 * Get the social icons
	 *
	 * @since v.1.0.4
	 */

	public function tutor_social_share_icons(){
		$icons = array(
			'facebook' => array('share_class' => 's_facebook', 'icon_html' => '<i class="tutor-icon-facebook"></i>' ),
			'twitter' => array('share_class' => 's_twitter', 'icon_html' => '<i class="tutor-icon-twitter"></i>' ),
			'linkedin' => array('share_class' => 's_linkedin', 'icon_html' => '<i class="tutor-icon-linkedin"></i>' ),
			'tumblr' => array('share_class' => 's_tumblr', 'icon_html' => '<i class="tutor-icon-tumblr"></i>' ),
		);

		return apply_filters('tutor_social_share_icons', $icons);
	}

	/**
	 * @return array $array
	 *
	 * Get the user social icons
	 *
	 * @since v.1.3.7
	 */

	public function tutor_user_social_icons(){
		$icons = array(
			'_tutor_profile_website'  => array(
				'label'         => __('Website URL', 'tutor'),
				'placeholder'   => 'https://example.com/',
				'icon_classes'   => 'tutor-icon-earth'
			),
			'_tutor_profile_github'  => array(
				'label'         => __('Github URL', 'tutor'),
				'placeholder'   => 'https://github.com/username',
				'icon_classes'   => 'tutor-icon-github-logo'
			),
			'_tutor_profile_facebook'  => array(
				'label'         => __('Facebook URL', 'tutor'),
				'placeholder'   => 'https://facebook.com/username',
				'icon_classes'   => 'tutor-icon-facebook'
			),
			'_tutor_profile_twitter'  => array(
				'label'         => __('Twitter URL', 'tutor'),
				'placeholder'   => 'https://twitter.com/username',
				'icon_classes'   => 'tutor-icon-twitter'
			),
			'_tutor_profile_linkedin'  => array(
				'label'         => __('Linkedin URL', 'tutor'),
				'placeholder'   => 'https://linkedin.com/username',
				'icon_classes'   => 'tutor-icon-linkedin'
			),
		);

		return apply_filters('tutor_user_social_icons', $icons);
	}

	/**
	 * @param array $array
	 *
	 * @return bool
	 *
	 * count method with check is_array
	 *
	 * @since v.1.0.4
	 */
	public function count($array = array()){
		if (is_array($array) && count($array)){
			return count($array);
		}
		return false;
	}

	/**
	 * @return array
	 *
	 * get all screen ids
	 *
	 * @since v.1.1.2
	 */
	public function tutor_get_screen_ids(){
		$screen_ids = array(
			"edit-course",
			"course",
			"edit-course-category",
			"edit-course-tag",
			"tutor-lms_page_tutor-students",
			"tutor-lms_page_tutor-instructors",
			"tutor-lms_page_question_answer",
			"tutor-lms_page_tutor_quiz_attempts",
			"tutor-lms_page_tutor-addons",
			"tutor-lms_page_tutor-status",
			"tutor-lms_page_tutor_report",
			"tutor-lms_page_tutor_settings",
			"tutor-lms_page_tutor_emails",
		);

		return apply_filters('tutor_get_screen_ids', $screen_ids);
	}


	/**
	 * @return mixed
	 *
	 * get earning transaction completed status
	 *
	 * @since v.1.1.2
	 */
	public function get_earnings_completed_statuses(){
		return apply_filters(
			'tutor_get_earnings_completed_statuses',
			array (
				'wc-completed',
				'completed',
				'complete',
			)
		);
	}

	/**
	 * @param int $user_id
	 * @param array $date_filter
	 *
	 * @return array|null|object
	 *
	 * Get all time earning sum for an instructor with all commission
	 *
	 * @since v.1.1.2
	 */

	public function get_earning_sum($user_id = 0, $date_filter = array()){
		global $wpdb;

		$user_id = $this->get_user_id($user_id);
		$date_query = '';
		if ($this->count($date_filter)){
			extract($date_filter);

			if ( ! empty($dataFor)){
				if ($dataFor === 'yearly'){
					if (empty($year)){
						$year = date('Y');
					}
					$date_query = "AND YEAR(created_at) = {$year} ";
				}
			}else{
				$date_query = " AND (created_at BETWEEN '{$start_date}' AND '{$end_date}') ";
			}
		}

		$complete_status = tutor_utils()->get_earnings_completed_statuses();
		$complete_status = "'".implode("','", $complete_status)."'";

		$earning_sum = $wpdb->get_row("SELECT SUM(course_price_total) as course_price_total, 
                    SUM(course_price_grand_total) as course_price_grand_total, 
                    SUM(instructor_amount) as instructor_amount, 
                    (SELECT SUM(amount) FROM {$wpdb->prefix}tutor_withdraws WHERE user_id = {$user_id} AND status != 'rejected' ) as 
                    withdraws_amount,
                    SUM(admin_amount) as admin_amount, 
                    SUM(deduct_fees_amount)  as deduct_fees_amount
                    FROM {$wpdb->prefix}tutor_earnings 
                    WHERE user_id = {$user_id} AND order_status IN({$complete_status}) {$date_query} ");

		//TODO: need to check
		// (SUM(instructor_amount) - (SELECT withdraws_amount) ) as balance,


		if ( $earning_sum->course_price_total){
			$earning_sum->balance = $earning_sum->instructor_amount - $earning_sum->withdraws_amount;
		}else{

			$earning_sum = (object) array(
				'course_price_total'        => 0,
				'course_price_grand_total'  => 0,
				'instructor_amount'         => 0,
				'withdraws_amount'          => 0,
				'balance'                   => 0,
				'admin_amount'              => 0,
				'deduct_fees_amount'        => 0,
			);
		}

		return $earning_sum;
	}

	/**
	 * @param int $user_id
	 * @param array $date_filter
	 *
	 * @return array|null|object
	 *
	 * Get earning statements
	 *
	 * @since v.1.1.2
	 */
	public function get_earning_statements($user_id = 0, $filter_data = array()){
		global $wpdb;

		$user_sql = "";
		if ($user_id){
			$user_sql = " AND user_id='{$user_id}' ";
		}

		$date_query = '';
		$query_by_status = '';
		$pagination_query = '';

		/**
		 * Query by Date Filter
		 */
		if ($this->count($filter_data)){
			extract($filter_data);

			if ( ! empty($dataFor)){
				if ($dataFor === 'yearly'){
					if (empty($year)){
						$year = date('Y');
					}
					$date_query = "AND YEAR(created_at) = {$year} ";
				}
			}else{
				$date_query = " AND (created_at BETWEEN '{$start_date}' AND '{$end_date}') ";
			}

			/**
			 * Query by order status related to this earning transaction
			 */
			if ( ! empty($statuses)) {
				if ( $this->count( $statuses ) ) {
					$status          = "'" . implode( "','", $statuses ) . "'";
					$query_by_status = "AND order_status IN({$status})";
				} elseif ( $statuses === 'completed' ) {

					$get_earnings_completed_statuses = $this->get_earnings_completed_statuses();
					if ( $this->count( $get_earnings_completed_statuses ) ) {
						$status          = "'" . implode( "','", $get_earnings_completed_statuses ) . "'";
						$query_by_status = "AND order_status IN({$status})";
					}
				}
			}

			if ( ! empty($per_page)){
				$offset = (int) ! empty($offset) ? $offset : 0;

				$pagination_query = " LIMIT {$offset}, {$per_page}  ";

			}


		}

		$query = $wpdb->get_results("SELECT earning_tbl.*, course.post_title as course_title
					FROM {$wpdb->prefix}tutor_earnings earning_tbl
					LEFT JOIN {$wpdb->posts} course ON earning_tbl.course_id = course.ID
                    WHERE 1=1 {$user_sql} {$date_query} {$query_by_status} ORDER BY created_at DESC {$pagination_query} ");


		$query_count = (int) $wpdb->get_var("SELECT COUNT(earning_tbl.earning_id)
					FROM {$wpdb->prefix}tutor_earnings earning_tbl
                    WHERE 1=1 {$user_sql} {$date_query} {$query_by_status} ORDER BY created_at DESC ");

		return (object) array(
			'count' => $query_count,
			'results' => $query,
		);
	}

	/**
	 * @param int $price
	 *
	 * @return int|string
	 *
	 * Get the price format
	 *
	 * @since v.1.1.2
	 */

	public function tutor_price($price = 0){
		if (function_exists('wc_price')){
			return wc_price($price);
		}elseif (function_exists('edd_currency_filter')){
			return edd_currency_filter(edd_format_amount($price));
		}else{
			return number_format_i18n($price);
		}
	}

	/**
	 * @return mixed
	 *
	 * Get currency symbol from activated plugin, WC,EDD
	 *
	 * @since  v.1.3.4
	 */

	public function currency_symbol(){
		$enable_tutor_edd = tutor_utils()->get_option('enable_tutor_edd');
		$monetize_by = $this->get_option('monetize_by');

		$symbol = '&#36;';
		if ($enable_tutor_edd && function_exists('edd_currency_symbol')){
			$symbol = edd_currency_symbol();
		}

		if ($monetize_by === 'wc' && function_exists('get_woocommerce_currency_symbol') ){
			$symbol = get_woocommerce_currency_symbol();
		}

		return apply_filters('get_tutor_currency_symbol', $symbol);
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool|mixed
	 *
	 * Get withdraw method for a specific
	 */
	public function get_user_withdraw_method($user_id = 0){
		$user_id = $this->get_user_id($user_id);

		$account = get_user_meta($user_id, '_tutor_withdraw_method_data', true);
		if ($account){
			return maybe_unserialize($account);
		}

		return false;
	}

	/**
	 * @param int $user_id
	 * @param array $filter
	 *
	 * get withdrawal history
	 *
	 * @return object
	 */
	public function get_withdrawals_history($user_id = 0, $filter = array()){
		global $wpdb;

		$filter = (array) $filter;
		extract($filter);

		$query_by_status_sql = "";
		$query_by_user_sql = "";
		$query_by_pagination = "";

		if ( ! empty($status)){
			$status = (array) $status;
			$status = "'".implode("','", $status)."'";

			$query_by_status_sql = " AND status IN({$status}) ";
		}

		if ( ! empty($per_page)){
			if ( empty($start))
				$start = 0;

			$query_by_pagination = " LIMIT {$start}, {$per_page} ";
		}

		if ($user_id){
			$query_by_user_sql = " AND user_id = {$user_id} ";
		}


		$count = (int) $wpdb->get_var("SELECT COUNT(withdraw_id) FROM {$wpdb->prefix}tutor_withdraws WHERE 1=1 {$query_by_user_sql} {$query_by_status_sql} ");

		$results = $wpdb->get_results("SELECT withdraw_tbl.*, 
		user_tbl.display_name as user_name, 
		user_tbl.user_email 
		FROM {$wpdb->prefix}tutor_withdraws withdraw_tbl 
		INNER JOIN {$wpdb->users} user_tbl ON withdraw_tbl.user_id = user_tbl.ID
		WHERE 1=1 
		{$query_by_user_sql} 
		{$query_by_status_sql} ORDER BY 
		created_at DESC  {$query_by_pagination} ");

		$withdraw_history = array(
			'count' => 0,
			'results' => null,
		);

		if ($count){
			$withdraw_history['count'] = $count;
		}

		if (tutor_utils()->count($results)){
			$withdraw_history['results'] = $results;
		}
		return (object) $withdraw_history;

	}

	/**
	 * @param int $instructor_id
	 *
	 * Add Instructor role to any user by user iD
	 */
	public function add_instructor_role($instructor_id = 0){
		if ( ! $instructor_id){
			return;
		}
		do_action('tutor_before_approved_instructor', $instructor_id);

		update_user_meta($instructor_id, '_is_tutor_instructor', tutor_time());
		update_user_meta($instructor_id, '_tutor_instructor_status', 'approved');
		update_user_meta($instructor_id, '_tutor_instructor_approved', tutor_time());

		$instructor = new \WP_User($instructor_id);
		$instructor->add_role(tutor()->instructor_role);

		do_action('tutor_after_approved_instructor', $instructor_id);
	}

	/**
	 * @param int $instructor_id
	 *
	 * Remove instructor role by instructor id
	 */
	public function remove_instructor_role($instructor_id = 0){
		if ( ! $instructor_id){
			return;
		}

		do_action('tutor_before_blocked_instructor', $instructor_id);
		delete_user_meta($instructor_id, '_is_tutor_instructor');
		update_user_meta($instructor_id, '_tutor_instructor_status', 'blocked');

		$instructor = new \WP_User($instructor_id);
		$instructor->remove_role(tutor()->instructor_role);
		do_action('tutor_after_blocked_instructor', $instructor_id);
	}

	/**
	 * @param string $msg
	 * @param string $name
	 *
	 * Set Flash Message to view in next action / route
	 */
	public function set_flash_msg($msg = '', $name = 'success'){
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$filename = "tutor_flash_msg_{$name}.txt";
		$upload_dir = wp_upload_dir();
		$dir = trailingslashit($upload_dir['basedir']) . 'tutor/';

		WP_Filesystem( false, $upload_dir['basedir'], true );

		if( ! $wp_filesystem->is_dir( $dir ) ) {
			$wp_filesystem->mkdir( $dir );
		}
		$wp_filesystem->put_contents( $dir . $filename, $msg );
	}

	/**
	 * @param null $name
	 *
	 * @return mixed|string|void
	 *
	 * Get Flash Message
	 */
	public function get_flash_msg($name = null){
		if ( ! $name){
			return '';
		}

		$upload_dir = wp_get_upload_dir();
		$upload_dir = trailingslashit($upload_dir['basedir']);
		$msg_name = 'tutor_flash_msg_'.$name;

		$msg = '';
		$flash_msg_file_name = $upload_dir."tutor/{$msg_name}.txt";
		if (file_exists($flash_msg_file_name)){
			$msg = file_get_contents($flash_msg_file_name);
			unlink($flash_msg_file_name);
		}

		return apply_filters('tutor_get_flash_msg', $msg, $name);
	}

	/**
	 * @param int $user_id
	 *
	 * @return array|null|object
	 *
	 * Get purchase history by customer id
	 */

	public function get_orders_by_user_id($user_id = 0){
		global $wpdb;

		$user_id = $this->get_user_id();
		$monetize_by = tutils()->get_option('monetize_by');

		$post_type = "";
		$user_meta = "";
		if ($monetize_by === 'wc') {
			$post_type = "shop_order";
			$user_meta = "_customer_user";
		} else if ($monetize_by === 'edd') {
			$post_type = "edd_payment";
			$user_meta = "_edd_payment_user_id";
		}

		$orders = $wpdb->get_results(
			"	SELECT 
					{$wpdb -> posts}.* 
				FROM 
					{$wpdb -> posts} 
					INNER JOIN {$wpdb -> postmeta} customer ON id = customer.post_id 
					AND customer.meta_key = '{$user_meta}' 
					INNER JOIN {$wpdb -> postmeta} tutor_order ON id = tutor_order.post_id 
					AND tutor_order.meta_key = '_is_tutor_order_for_course' 
				WHERE 
					post_type = '{$post_type}'
					AND customer.meta_value = {$user_id} 
				ORDER BY
					{$wpdb -> posts}.id DESC
			");

		return $orders;
	}

	/**
	 * @param null $status
	 *
	 * @return string
	 *
	 * Get status contact formatted for order
	 *
	 * @since v.1.3.1
	 */
	public function order_status_context($status = null){
		$status = str_replace('wc-', '', $status);
		$status_name = ucwords(str_replace('-', ' ', $status));

		return "<span class='label-order-status label-status-{$status}'>$status_name</span>";
	}

	public function get_course_id_by_assignment($assignment_id = 0){
		$assignment_id = $this->get_post_id($assignment_id);
		return get_post_meta($assignment_id, '_tutor_course_id_for_assignments', true);
	}

	/**
	 * @param int $assignment_id
	 * @param string $option_key
	 * @param bool $default
	 *
	 * @return array|bool|mixed
	 *
	 * Get assignment options
	 *
	 * @since v.1.3.3
	 */
	public function get_assignment_option($assignment_id = 0, $option_key = '', $default = false){
		$assignment_id = $this->get_post_id($assignment_id);
		$get_option_meta = maybe_unserialize(get_post_meta($assignment_id, 'assignment_option', true));

		if ( ! $option_key && ! empty($get_option_meta)) {
			return $get_option_meta;
		}

		$value = $this->avalue_dot( $option_key, $get_option_meta );
		if ( $value ) {
			return $value;
		}
		return $default;
	}

	/**
	 * @param int $assignment_id
	 * @param int $user_id
	 *
	 * @return int
	 *
	 * Is running any assignment submitting
	 *
	 * @since v.1.3.3
	 */
	public function is_assignment_submitting($assignment_id = 0, $user_id = 0){
		global $wpdb;

		$assignment_id = $this->get_post_id($assignment_id);
		$user_id = $this->get_user_id($user_id);

		$is_running_submit = (int) $wpdb->get_var("SELECT comment_ID FROM {$wpdb->comments} 
		WHERE comment_type = 'tutor_assignment' 
		AND comment_approved = 'submitting' 
		AND user_id = {$user_id} 
		AND comment_post_ID = {$assignment_id} ");

		return $is_running_submit;
	}

	/**
	 * @param int $assignment_id
	 * @param int $user_id
	 *
	 * @return array|null|object
	 *
	 * Determine if any assignment submitted by user to a assignment
	 *
	 * @since v.1.3.3
	 */

	public function is_assignment_submitted($assignment_id = 0, $user_id = 0){
		global $wpdb;

		$assignment_id = $this->get_post_id($assignment_id);
		$user_id = $this->get_user_id($user_id);

		$has_submitted = $wpdb->get_row("SELECT * FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_approved = 'submitted' AND user_id = {$user_id} AND comment_post_ID = {$assignment_id} ");

		return $has_submitted;
	}

	public function get_assignment_submit_info($assignment_submitted_id = 0){
		global $wpdb;

		$assignment_submitted_id = $this->get_post_id($assignment_submitted_id);
		$submitted_info = $wpdb->get_row("SELECT * FROM {$wpdb->comments} WHERE comment_ID = {$assignment_submitted_id} AND comment_type = 'tutor_assignment' AND comment_approved = 'submitted' ");

		return $submitted_info;
	}

	public function get_total_assignments(){
		global $wpdb;

		$count = $wpdb->get_var("SELECT COUNT(comment_ID) FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_approved = 'submitted'    ");

		return (int) $count;
	}

	public function get_assignments(){
		global $wpdb;

		$results = $wpdb->get_results("SELECT * FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_approved = 'submitted'    ");

		return $results;
	}

	/**
	 * @param int $user_id
	 *
	 * @return array
	 *
	 * Get all courses id assigned or owned by an instructors
	 *
	 * @since v.1.3.3
	 */
	public function get_assigned_courses_ids_by_instructors($user_id = 0){
		global $wpdb;
		$user_id = $this->get_user_id($user_id);

		$course_post_type = tutor()->course_post_type;
		$get_assigned_courses_ids = $wpdb->get_col("SELECT meta_value from {$wpdb->usermeta} WHERE meta_key = '_tutor_instructor_course_id' AND user_id = {$user_id} GROUP BY meta_value ; ");

		/*
		$author_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} where post_type = '{$course_post_type}' AND post_author = {$user_id}");
		$final_course_ids = array_merge($get_assigned_courses_ids, $author_ids);
        $final_course_ids = array_unique($final_course_ids);
		*/

		return $get_assigned_courses_ids;
	}

	/**
	 * @param int $parent
	 *
	 * @return array
	 *
	 * Get course categories in array with child
	 *
	 * @since v.1.3.4
	 */

	public function get_course_categories($parent = 0 ){
		$args = apply_filters('tutor_get_course_categories_args', array(
			'taxonomy' => 'course-category',
			'hide_empty'    => false,
			'parent' => $parent,
		));

		$terms = get_terms($args);

		$children = array();
		foreach ( $terms as $term ){
			$term->children = $this->get_course_categories( $term->term_id );
			$children[ $term->term_id ] = $term;
		}

		return $children;
	}

	/**
	 * @param int $parent_id
	 *
	 * @return array|int|\WP_Error
	 *
	 * Get course categories terms in raw array
	 *
	 * @since v.1.3.5
	 */
	public function get_course_categories_term($parent_id = 0){
		$args = apply_filters('tutor_get_course_categories_terms_args', array(
			'taxonomy' => 'course-category',
			'parent' => $parent_id,
			'hide_empty' => false,
		));

		$terms = get_terms($args);

		return $terms;
	}

	/**
	 * @return mixed
	 *
	 * Get back url from the request
	 * @since v.1.3.4
	 */
	public function referer(){
		$url = $this->array_get('_wp_http_referer', $_REQUEST);
		return apply_filters('tutor_referer_url', $url);
	}

	/**
	 * @param int $course_id
	 *
	 * @return false|string
	 *
	 * Get the frontend dashboard course edit page
	 *
	 * @since v.1.3.4
	 */
	public function course_edit_link($course_id = 0){
		$course_id = $this->get_post_id($course_id);

		$url = admin_url("post.php?post={$course_id}&action=edit");
		if (tutor()->has_pro){
			$url = $this->tutor_dashboard_url("create-course/?course_ID=".$course_id);
		}

		return $url;
	}

	public function get_assignments_by_instructor($instructor_id = 0, $filter_data = array()){
		global $wpdb;

		$instructor_id = $this->get_user_id($instructor_id);
		$course_ids = tutor_utils()->get_assigned_courses_ids_by_instructors($instructor_id);

		//$new_course_ids = tutils()->get_courses_by_instructor();

		//die($this->print_view($course_ids));

		$in_course_ids = implode("','", $course_ids);

		$count = (int) $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->postmeta} post_meta
 			INNER JOIN {$wpdb->posts} assignment ON post_meta.post_id = assignment.ID AND post_meta.meta_key = '_tutor_course_id_for_assignments'
 			where post_type = 'tutor_assignments' AND post_meta.meta_value IN('$in_course_ids') ORDER BY ID DESC ");

		$pagination_query = '';
		if ($this->count($filter_data)) {
			extract( $filter_data );

			if ( ! empty( $per_page ) ) {
				$offset           = (int) ! empty( $offset ) ? $offset : 0;
				$pagination_query = " LIMIT {$offset}, {$per_page}  ";
			}
		}

		$query = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} post_meta
 			INNER JOIN {$wpdb->posts} assignment ON post_meta.post_id = assignment.ID AND post_meta.meta_key = '_tutor_course_id_for_assignments'
 			where post_type = 'tutor_assignments' AND post_meta.meta_value IN('$in_course_ids')  ORDER BY ID DESC {$pagination_query} ");

		return (object) array('count' => $count, 'results' => $query);
	}

	/**
	 * @param int $course_id
	 *
	 * @return bool|object
	 *
	 * Get assignments by course id
	 */
	public function get_assignments_by_course($course_id = 0){
		if ( ! $course_id){
			return false;
		}
		global $wpdb;

		$count = (int) $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->postmeta} post_meta
 			INNER JOIN {$wpdb->posts} assignment ON post_meta.post_id = assignment.ID AND post_meta.meta_key = '_tutor_course_id_for_assignments'
 			where post_type = 'tutor_assignments' AND post_meta.meta_value = {$course_id} ORDER BY ID DESC ");

		$query = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} post_meta
 			INNER JOIN {$wpdb->posts} assignment ON post_meta.post_id = assignment.ID AND post_meta.meta_key = '_tutor_course_id_for_assignments'
 			where post_type = 'tutor_assignments' AND post_meta.meta_value = {$course_id} ORDER BY ID DESC");

		return (object) array('count' => $count, 'results' => $query);
	}

	/**
	 * @return bool
	 *
	 * Determine if script debug
	 *
	 * @since v.1.3.4
	 */
	public function is_script_debug(){
		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	}

	/**
	 * Check lesson edit access by instructor
	 *
	 * @since  v.1.4.0
	 */

	public function has_lesson_edit_access($lesson_id = 0, $instructor_id = 0){
		$lesson_id = $this->get_post_id($lesson_id);
		$instructor_id = $this->get_user_id($instructor_id);

		if (user_can($instructor_id, tutor()->instructor_role)){
			$permitted_course_ids = tutils()->get_assigned_courses_ids_by_instructors();
			$course_id = tutils()->get_course_id_by_lesson($lesson_id);

			if (in_array($course_id, $permitted_course_ids)){
				return true;
			}
		}

		return false;
	}


	/**
	 * Get total Enrolments
	 * @since v.1.4.0
	 */

	public function get_total_enrolments($search_term = ''){
		global $wpdb;

		$search_sql = '';
		if ($search_term){
			$search_sql = " AND ( enrol.ID = '{$search_term}' OR student.display_name LIKE '%{$search_term}%' OR student.user_email LIKE '%{$search_term}%' OR course.post_title LIKE '%{$search_term}%' ) ";
		}

		$count = $wpdb->get_var("SELECT COUNT(enrol.ID)
			FROM {$wpdb->posts} enrol
			INNER JOIN {$wpdb->posts} course ON enrol.post_parent = course.ID
			INNER JOIN {$wpdb->users} student ON enrol.post_author = student.ID
			WHERE enrol.post_type = 'tutor_enrolled' {$search_sql}  ");
		return (int) $count;
	}

	public function get_enrolments($start = 0, $limit = 10, $search_term = ''){
		global $wpdb;

		$search_sql = '';
		if ($search_term){
			$search_sql = " AND ( enrol.ID = '{$search_term}' OR student.display_name LIKE '%{$search_term}%' OR student.user_email LIKE '%{$search_term}%' OR course.post_title LIKE '%{$search_term}%' ) ";
		}

		$enrolments = $wpdb->get_results("SELECT 
			enrol.ID as enrol_id,
			enrol.post_author as student_id,
			enrol.post_date as enrol_date,
			enrol.post_title as enrol_title,
			enrol.post_status as status,
			enrol.post_parent as course_id,
			
			course.post_title as course_title,
			
			student.user_nicename,
			student.user_email,
			student.display_name
			
			FROM {$wpdb->posts} enrol
			
			INNER JOIN {$wpdb->posts} course ON enrol.post_parent = course.ID
			INNER JOIN {$wpdb->users} student ON enrol.post_author = student.ID
			
			WHERE enrol.post_type = 'tutor_enrolled' {$search_sql}
			ORDER BY enrol_id DESC 
			LIMIT {$start}, {$limit} ");

		return $enrolments;
	}

	/**
	 * @param int $post_id
	 *
	 * @return false|string
	 *
	 * @since v.1.4.0
	 */

	public function get_current_url($post_id = 0){
		$page_id = $this->get_post_id($post_id);

		if ($page_id){
			return get_the_permalink($page_id);
		}else{
			global $wp;
			$current_url = home_url( $wp->request );

			return $current_url;
		}
	}


	/**
	 * @param int $rating_id
	 *
	 * @return object
	 *
	 * Get rating by rating id|comment_ID
	 *
	 * @since v.1.4.0
	 */

	public function get_rating_by_id($rating_id = 0){
		$ratings = array(
			'rating'  => 0,
			'review'    => '',
		);

		global $wpdb;

		$rating = $wpdb->get_row("select meta_value as rating, comment_content as review from {$wpdb->comments}
				INNER JOIN {$wpdb->commentmeta} 
				ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
				WHERE {$wpdb->comments}.comment_ID = {$rating_id} ;"
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
	 * @param int $course_id
	 * @param null $key
	 * @param bool $default
	 *
	 * @return array|bool|mixed
	 *
	 * Get course settings by course ID
	 */
	public function get_course_settings($course_id = 0, $key = null, $default = false){
		$course_id = $this->get_post_id($course_id);
		$settings_meta = get_post_meta($course_id, '_tutor_course_settings', true);
		$settings = (array) maybe_unserialize($settings_meta);

		return $this->array_get($key, $settings, $default);
	}

	/**
	 * @param int $lesson_id
	 * @param null $key
	 * @param bool $default
	 *
	 * @return array|bool|mixed
	 *
	 * Get Lesson content drip settings
	 *
	 * @since v.1.4.0
	 */
	public function get_item_content_drip_settings($lesson_id = 0, $key = null, $default = false){
		$lesson_id = $this->get_post_id($lesson_id);
		$settings_meta = get_post_meta($lesson_id, '_content_drip_settings', true);
		$settings = (array) maybe_unserialize($settings_meta);

		return $this->array_get($key, $settings, $default);
	}


	/**
	 * @param null $post
	 *
	 * @return bool
	 *
	 * Get previous ID
	 */
	public function get_course_previous_content_id($post = null){
		$current_item = get_post($post);
		$course_id = $this->get_course_id_by_content($current_item);

		$topics = tutor_utils()->get_topics($course_id);

		$contents = array();
		if ($topics->have_posts()) {
			while ( $topics->have_posts() ) {
				$topics->the_post();
				$topic_id = get_the_ID();
				$lessons = tutor_utils()->get_course_contents_by_topic($topic_id, -1);
				if ($lessons->have_posts()) {
					while ( $lessons->have_posts() ) {
						$lessons->the_post();
						global $post;
						$contents[] = $post;
					}
				}

			}
		}

		if (tutils()->count($contents)){
			foreach ($contents as $key => $content){
				if ($current_item->ID == $content->ID){
					if ( ! empty($contents[$key-1]->ID)){
						return $contents[$key-1]->ID;
					}
				}
			}
		}

/*

		if ($post->menu_order > 0){

			$contents = $wpdb->get_results("SELECT items.* FROM {$wpdb->posts} topic
				INNER JOIN {$wpdb->posts} items ON topic.ID = items.post_parent 
				WHERE topic.post_parent = {$course_id} AND items.post_status = 'publish' order by topic.menu_order ASC, items.menu_order ASC;");



			if (tutils()->count($contents)){
				foreach ($contents as $key => $content){
					if ($post->ID == $content->ID){
						if ( ! empty($contents[$key-1]->ID)){
							//return $contents[$key-1]->ID;
						}
					}
				}
			}

			die(print_r($contents));

		}else{
			$previous = $wpdb->get_row("SELECT items.* FROM {$wpdb->posts} topic 
              		INNER JOIN {$wpdb->posts} items ON topic.ID = items.post_parent 
					WHERE topic.post_parent = {$course_id} 
					AND items.post_status = 'publish' 
					AND items.ID < {$post->ID} ORDER BY ID DESC  LIMIT 1; ");

			if ( ! empty($previous->ID)){
				return $previous->ID;
			}
		}*/

		return false;
	}

	/**
	 * @param null $post
	 *
	 * @return int
	 *
	 * Get Course iD by any course content
	 */
	public function get_course_id_by_content($post = null){
		global $wpdb;
		$post = get_post($post);
		$course_id = $wpdb->get_var("SELECT post_parent FROM {$wpdb->posts} WHERE ID = {$post->post_parent} AND post_type = 'topics'");

		return (int) $course_id;
	}

	/**
	 * @param int $course_id
	 *
	 * @return array|null|object
	 *
	 * Get Course contents by Course ID
	 *
	 * @since v.1.4.1
	 */
	public function get_course_contents_by_id($course_id = 0){
		global $wpdb;

		$course_id = $this->get_post_id($course_id);

		$contents = $wpdb->get_results("SELECT items.* FROM {$wpdb->posts} topic
				INNER JOIN {$wpdb->posts} items ON topic.ID = items.post_parent 
				WHERE topic.post_parent = {$course_id} AND items.post_status = 'publish' order by topic.menu_order ASC, items.menu_order ASC;");

		return $contents;
	}

	/**
	 * @param string $grade_for
	 *
	 * @return array|null|object
	 *
	 * Get Gradebooks lists by type
	 *
	 * @since v.1.4.2
	 */
	public function get_gradebooks(){
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM {$wpdb->tutor_gradebooks} ORDER BY grade_point DESC ");
		return $results;
	}


	/**
	 * @param int $quiz_id
	 * @param int $user_id
	 *
	 * @return array|bool|null|object
	 *
	 * Get Attempt row by grade method settings
	 *
	 * @since v.1.4.2
	 */
	public function get_quiz_attempt($quiz_id = 0, $user_id = 0){
		global $wpdb;

		$quiz_id = $this->get_post_id($quiz_id);
		$user_id = $this->get_user_id($user_id);

		$attempt = false;

		$quiz_grade_method = get_tutor_option('quiz_grade_method', 'highest_grade');

		if ($quiz_grade_method === 'highest_grade'){

			$attempt = $wpdb->get_row("SELECT *
			FROM {$wpdb->tutor_quiz_attempts} WHERE quiz_id = {$quiz_id} AND user_id = {$user_id} AND attempt_status != 'attempt_started' 
			ORDER BY earned_marks DESC LIMIT 1; ");

		}elseif ($quiz_grade_method === 'average_grade'){

			$attempt = $wpdb->get_row("SELECT {$wpdb->tutor_quiz_attempts}.*,
			COUNT(attempt_id) as attempt_count,
			AVG(total_marks) as total_marks,
			AVG(earned_marks) as earned_marks
			FROM {$wpdb->tutor_quiz_attempts} WHERE  quiz_id = {$quiz_id} AND user_id = {$user_id} AND attempt_status != 'attempt_started' ");

		}elseif ($quiz_grade_method === 'first_attempt'){

			$attempt = $wpdb->get_row("SELECT *
			FROM {$wpdb->tutor_quiz_attempts} WHERE quiz_id = {$quiz_id} AND user_id = {$user_id} AND attempt_status != 'attempt_started' 
			ORDER BY attempt_id ASC LIMIT 1; ");

		}elseif ($quiz_grade_method === 'last_attempt'){

			$attempt = $wpdb->get_row("SELECT *
			FROM {$wpdb->tutor_quiz_attempts} WHERE quiz_id = {$quiz_id} AND user_id = {$user_id} AND attempt_status != 'attempt_started' 
			ORDER BY attempt_id DESC LIMIT 1; ");

		}

		return $attempt;
	}

	/**
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return string
	 *
	 * Print Course Status Context
	 *
	 * @since v.1.4.2
	 */
	public function course_progress_status_context($course_id = 0, $user_id = 0){
		$course_id = $this->get_post_id($course_id);
		$user_id = $this->get_user_id($user_id);

		$is_completed = tutils()->is_completed_course($course_id, $user_id);
		$html = '';
		if ($is_completed){
			$html = '<span class="course-completion-status course-completed"><i class="tutor-icon-mark"></i> '.__('Completed', 'tutor').' </span>';
		}else{
			$is_in_progress = tutor_utils()->get_completed_lesson_count_by_course($course_id, $user_id);
			if($is_in_progress){
				$html = '<span class="course-completion-status course-inprogress"><i class="tutor-icon-refresh-button-1"></i> '.__('In Progress', 'tutor').' </span>';
			}else{
				$html = '<span class="course-completion-status course-not-taken"><i class="tutor-icon-spinner"></i> '.__('Not Taken', 'tutor').' </span>';
			}
		}
		return $html;
	}

	/**
	 * @param $user
	 * @param $new_pass
	 *
	 * Reset Password
	 *
	 * @since v.1.4.3
	 */
	public function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );

		$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
		$rp_path   = isset( $_SERVER['REQUEST_URI'] ) ? current( explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : ''; // WPCS: input var ok, sanitization ok.

		setcookie( $rp_cookie, ' ', tutor_time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		wp_password_change_notification( $user );
	}

	/**
	 * @return array
	 *
	 * Get tutor pages, required to show dashboard, and others forms
	 *
	 * @since v.1.4.3
	 */
	public function tutor_pages(){
		$pages = apply_filters('tutor_pages', array(
			'tutor_dashboard_page_id'   => __('Dashboard Page', 'tutor'),
			'instructor_register_page'  => __('Instructor Registration Page', 'tutor'),
			'student_register_page'     => __('Student Registration Page', 'tutor'),
		));

		$new_pages = array();
		foreach ($pages as $key => $page){
			$page_id = (int) get_tutor_option($key);

			$wp_page_name = '';
			$wp_page = get_post($page_id);
			$page_exists = (bool) $wp_page;
			$page_visible = false;

			if ($wp_page){
				$wp_page_name = $wp_page->post_title;
				$page_visible = $wp_page->post_status === 'publish';
			}

			$new_pages[] = array(
				'option_key'    => $key,
				'page_name'     => $page,
				'wp_page_name'  => $wp_page_name,
				'page_id'       => $page_id,
				'page_exists'   => $page_exists,
				'page_visible'  => $page_visible,
			);

		}

		return $new_pages;
	}

	/**
	 * @param int $course_id
	 *
	 * @return array|null|object
	 *
	 * Get Course prev next lession contents by content ID
	 *
	 * @since v.1.4.9
	 */
	public function get_course_prev_next_contents_by_id($content_id = 0){

		$course_id = $this->get_course_id_by_content($content_id);
		$course_contents = $this->get_course_contents_by_id($course_id);
		$previous_id = 0;
		$next_id = 0;
		if ($this->count($course_contents)){
			$ids = wp_list_pluck($course_contents, 'ID');

			$i=0;
			foreach ($ids as $key => $id){
				$previous_i = $key - 1;
				$next_i = $key + 1;

				if ($id == $content_id){
					if (isset($ids[$previous_i])){
						$previous_id = $ids[$previous_i];
					}
					if (isset($ids[$next_i])){
						$next_id = $ids[$next_i];
					}
				}
				$i++;
			}
		}

		return (object) ['previous_id'=>$previous_id, 'next_id'=>$next_id];
	}


	/**
	 * Get a subset of the items from the given array.
	 *
	 * @param array $array
	 * @param array|string  $keys
	 *
	 * @return array|bool
	 *
	 * @since v.1.5.2
	 */

	public function array_only($array = array(), $keys = null){
		if ( ! $this->count($array) || ! $keys){
			return false;
		}

		return array_intersect_key($array, array_flip((array) $keys));
	}


	/**
	 * @param int $instructor_id
	 * @param int $course_id
	 *
	 * @return bool|int
	 *
	 * Is instructor of this course
	 *
	 * @since v.1.6.4
	 */
	public function is_instructor_of_this_course($instructor_id=0, $course_id=0) {
		global $wpdb;

		$instructor_id 	= $this->get_user_id($instructor_id);
		$course_id 		= $this->get_post_id($course_id);

		if (!$instructor_id || !$course_id) {
			return false;
		}

		$instructor = $wpdb->get_col("
			SELECT umeta_id
			FROM   {$wpdb->usermeta}
			WHERE  user_id = {$instructor_id}
				AND meta_key = '_tutor_instructor_course_id'
				AND meta_value = {$course_id}
		");
		
		if (is_array($instructor) && count($instructor)) {
			return $instructor;
		}

		return false;
	}

	/**
	 * @param int $user_id
	 *
	 * @return array|object
	 *
	 * User profile completion
	 *
	 * @since v.1.6.6
	 */
	public function user_profile_completion($user_id=0) {
		$user_id = $this->get_user_id($user_id);
		$instructor = $this->is_instructor($user_id);

		$required_fields = apply_filters('tutor_profile_required_fields', array(
			'first_name' 					=> __('First Name', 'tutor'),
			'last_name' 					=> __('Last Name', 'tutor'),
			'_tutor_profile_photo' 			=> __('Profile Photo', 'tutor'),
			'_tutor_withdraw_method_data' 	=> __('Withdraw Method', 'tutor'),
		));

		if (!$instructor && array_key_exists("_tutor_withdraw_method_data", $required_fields)) {
			unset($required_fields['_tutor_withdraw_method_data']);
		}

		$empty_fields = array();
		foreach ($required_fields as $key => $field) {
			$value = get_user_meta($user_id, $key, true);
			if (!$value) {
				array_push($empty_fields, $field);
			}
		}
		
		$total_empty_fields = count($empty_fields);
		$total_required_fields = count($required_fields);
		$signup_point = apply_filters('tutor_profile_completion_signup_point', 50);

		if ($total_empty_fields == 0) {
			$progress = 100;
		} else {
			$completed_field = $total_required_fields-$total_empty_fields;
			$per_field_point = $signup_point / $total_required_fields;
			$progress = $signup_point + ceil($per_field_point * $completed_field);
		}

		$return = array(
			'empty_fields' => $empty_fields,
			'progress' => $progress,
		);

		return (object) $return;
	}

	/**
	 * @param int $enrol_id
	 *
	 * @return array|object
	 *
	 * Get enrolment by enrol_id
	 *
	 * @since v1.6.9
	 */
	public function get_enrolment_by_enrol_id($enrol_id = 0){
		global $wpdb;

		$enrolment = $wpdb->get_row("
			SELECT 	enrol.id          AS enrol_id,
					enrol.post_author AS student_id,
					enrol.post_date   AS enrol_date,
					enrol.post_title  AS enrol_title,
					enrol.post_status AS status,
					enrol.post_parent AS course_id,
					course.post_title AS course_title,
					student.user_nicename,
					student.user_email,
					student.display_name
			FROM   {$wpdb->posts} enrol
					INNER JOIN {$wpdb->posts} course
							ON enrol.post_parent = course.id
					INNER JOIN {$wpdb->users} student
							ON enrol.post_author = student.id
			WHERE  enrol.id = {$enrol_id};
		");

		if ( $enrolment ) {
			return $enrolment;
		}

		return false;
	}

	/**
	 * @param int $course_id
	 *
	 * @return int
	 * 
	 * @since v1.6.9
	 *
	 * Get students email by course id
	 */
	public function get_student_emails_by_course_id($course_id = 0){
		global $wpdb;
		$course_id = $this->get_post_id($course_id);

		$student_emails = $wpdb->get_results("
			SELECT 	student.user_email
			FROM   	{$wpdb->posts} enrol
					INNER JOIN {$wpdb->users} student
						ON enrol.post_author = student.id
			WHERE  	enrol.post_type = 'tutor_enrolled'
					AND enrol.post_parent = {$course_id}
					AND enrol.post_status = 'completed'; 
		");

		$email_array = array_column($student_emails,'user_email');

		return $email_array;
	}
}