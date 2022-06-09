<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {
	public function __construct() {

		add_action( 'wp_ajax_sync_video_playback', array( $this, 'sync_video_playback' ) );
		add_action( 'wp_ajax_nopriv_sync_video_playback', array( $this, 'sync_video_playback_noprev' ) );
		add_action( 'wp_ajax_tutor_place_rating', array( $this, 'tutor_place_rating' ) );
		add_action( 'wp_ajax_delete_tutor_review', array( $this, 'delete_tutor_review' ) );

		add_action( 'wp_ajax_tutor_course_add_to_wishlist', array( $this, 'tutor_course_add_to_wishlist' ) );
		add_action( 'wp_ajax_nopriv_tutor_course_add_to_wishlist', array( $this, 'tutor_course_add_to_wishlist' ) );

		/**
		 * Get all addons
		 */
		add_action( 'wp_ajax_tutor_get_all_addons', array( $this, 'tutor_get_all_addons' ) );

		/**
		 * Addon Enable Disable Control
		 */
		add_action( 'wp_ajax_addon_enable_disable', array( $this, 'addon_enable_disable' ) );

		/**
		 * Ajax login
		 *
		 * @since  v.1.6.3
		 */
		add_action( 'wp_ajax_nopriv_tutor_user_login', array( $this, 'process_ajax_login' ) );

		/**
		 * Announcement
		 *
		 * @since  v.1.7.9
		 */
		add_action( 'wp_ajax_tutor_announcement_create', array( $this, 'create_or_update_annoucement' ) );
		add_action( 'wp_ajax_tutor_announcement_delete', array( $this, 'delete_annoucement' ) );
	}



	/**
	 * Update video information and data when necessary
	 *
	 * @since v.1.0.0
	 */
	public function sync_video_playback() {
		tutor_utils()->checking_nonce();

		$user_id     = get_current_user_id();
		$post_id     = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : 0;
		$duration    = sanitize_text_field( $_POST['duration'] );
		$currentTime = sanitize_text_field( $_POST['currentTime'] );

		if ( ! tutor_utils()->has_enrolled_content_access( 'lesson', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
			exit;
		}

		/**
		 * Update posts attached video
		 */
		$video = tutor_utils()->get_video( $post_id );

		if ( $duration ) {
			$video['duration_sec'] = $duration; // secs
			$video['playtime']     = tutor_utils()->playtime_string( $duration );
			$video['runtime']      = tutor_utils()->playtime_array( $duration );
		}
		tutor_utils()->update_video( $post_id, $video );

		/**
		 * Sync Lesson Reading Info by Users
		 */

		$best_watch_time = tutor_utils()->get_lesson_reading_info( $post_id, $user_id, 'video_best_watched_time' );
		if ( $best_watch_time < $currentTime ) {
			tutor_utils()->update_lesson_reading_info( $post_id, $user_id, 'video_best_watched_time', $currentTime );
		}

		if ( tutor_utils()->avalue_dot( 'is_ended', $_POST ) ) {
			tutor_utils()->mark_lesson_complete( $post_id );
		}
		exit();
	}

	public function sync_video_playback_noprev() {

	}


	public function tutor_place_rating() {
		global $wpdb;

		tutor_utils()->checking_nonce();

		$moderation= tutor_utils()->get_option('enable_course_review_moderation', false, true, true);
		$rating    = sanitize_text_field( tutor_utils()->avalue_dot( 'tutor_rating_gen_input', $_POST ) );
		$course_id = sanitize_text_field( tutor_utils()->avalue_dot( 'course_id', $_POST ) );
		$review    = sanitize_textarea_field( tutor_utils()->avalue_dot( 'review', $_POST ) );

		! $rating ? $rating   = 0 : 0;
		$rating > 5 ? $rating = 5 : 0;

		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );
		$date    = date( 'Y-m-d H:i:s', tutor_time() );

		if ( ! tutor_utils()->has_enrolled_content_access( 'course', $course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
			exit;
		}

		do_action( 'tutor_before_rating_placed' );

		$previous_rating_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT comment_ID
			from {$wpdb->comments}
			WHERE comment_post_ID = %d AND
				user_id = %d AND
				comment_type = 'tutor_course_rating'
			LIMIT 1;",
				$course_id,
				$user_id
			)
		);

		$review_ID = $previous_rating_id;
		if ( $previous_rating_id ) {
			$wpdb->update(
				$wpdb->comments,
				array( 
					'comment_content' => $review,
					'comment_approved' => $moderation ? 'hold' : 'approved',
					'comment_date'     => $date,
					'comment_date_gmt' => get_gmt_from_date( $date ),
				),
				array( 'comment_ID' => $previous_rating_id )
			);

			$rating_info = $wpdb->get_row( $wpdb->prepare( 
				"SELECT * FROM {$wpdb->commentmeta} 
				WHERE comment_id = %d 
					AND meta_key = 'tutor_rating'; ", 
				$previous_rating_id 
			) );

			if ( $rating_info ) {
				$wpdb->update(
					$wpdb->commentmeta,
					array( 'meta_value' => $rating ),
					array(
						'comment_id' => $previous_rating_id,
						'meta_key'   => 'tutor_rating',
					)
				);
			} else {
				$wpdb->insert(
					$wpdb->commentmeta,
					array(
						'comment_id' => $previous_rating_id,
						'meta_key'   => 'tutor_rating',
						'meta_value' => $rating,
					)
				);
			}
		} else {
			$data = array(
				'comment_post_ID'  => esc_sql( $course_id ),
				'comment_approved' => $moderation ? 'hold' : 'approved',
				'comment_type'     => 'tutor_course_rating',
				'comment_date'     => $date,
				'comment_date_gmt' => get_gmt_from_date( $date ),
				'user_id'          => $user_id,
				'comment_author'   => $user->user_login,
				'comment_agent'    => 'TutorLMSPlugin',
			);
			if ( $review ) {
				$data['comment_content'] = $review;
			}

			$wpdb->insert( $wpdb->comments, $data );
			$comment_id = (int) $wpdb->insert_id;
			$review_ID  = $comment_id;

			if ( $comment_id ) {
				$result = $wpdb->insert(
					$wpdb->commentmeta,
					array(
						'comment_id' => $comment_id,
						'meta_key'   => 'tutor_rating',
						'meta_value' => $rating,
					)
				);

				do_action( 'tutor_after_rating_placed', $comment_id );
			}
		}

		wp_send_json_success(
			array(
				'message'   => __( 'Rating placed successsully!', 'tutor' ),
				'review_id' => $review_ID,
			)
		);
	}

	public function delete_tutor_review() {
		tutor_utils()->checking_nonce();

		$review_id = sanitize_text_field( tutor_utils()->array_get( 'review_id', $_POST ) );

		if ( ! tutor_utils()->can_user_manage( 'review', $review_id, get_current_user_id() ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissioned Denied!', 'tutor' ) ) );
			exit;
		}

		global $wpdb;
		$wpdb->delete( $wpdb->commentmeta, array( 'comment_id' => $review_id ) );
		$wpdb->delete( $wpdb->comments, array( 'comment_ID' => $review_id ) );

		wp_send_json_success();
	}

	public function tutor_course_add_to_wishlist() {
		tutor_utils()->checking_nonce();

		// Redirect login since only logged in user can add courses to wishlist
		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				array(
					'redirect_to' => wp_login_url( wp_get_referer() ),
				)
			);
		}

		global $wpdb;
		$user_id   = get_current_user_id();
		$course_id = (int) sanitize_text_field( $_POST['course_id'] );

		$if_added_to_list = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * from {$wpdb->usermeta}
			WHERE user_id = %d
				AND meta_key = '_tutor_course_wishlist'
				AND meta_value = %d;",
				$user_id,
				$course_id
			)
		);

		if ( $if_added_to_list ) {
			$wpdb->delete(
				$wpdb->usermeta,
				array(
					'user_id'    => $user_id,
					'meta_key'   => '_tutor_course_wishlist',
					'meta_value' => $course_id,
				)
			);
			wp_send_json_success(
				array(
					'status'  => 'removed',
					'message' => __( 'Course removed from wish list', 'tutor' ),
				)
			);
		} else {
			add_user_meta( $user_id, '_tutor_course_wishlist', $course_id );
			wp_send_json_success(
				array(
					'status'  => 'added',
					'message' => __( 'Course added to wish list', 'tutor' ),
				)
			);
		}
	}

	/**
	 * Prepare addons data
	 */
	public function prepare_addons_data() {
		$addons       = apply_filters( 'tutor_addons_lists_config', array() );
		$plugins_data = $addons;

		if ( is_array( $addons ) && count( $addons ) ) {
			foreach ( $addons as $base_name => $addon ) {
				$addon_config = tutor_utils()->get_addon_config( $base_name );
				$is_enabled   = (bool) tutor_utils()->avalue_dot( 'is_enable', $addon_config );

				$plugins_data[ $base_name ]['is_enabled'] = $is_enabled;

				$thumbnail_url = tutor()->url . 'assets/images/tutor-plugin.png';
				if ( file_exists( $addon['path'] . 'assets/images/thumbnail.png' ) ) {
					$thumbnail_url = $addon['url'] . 'assets/images/thumbnail.png';
				} elseif ( file_exists( $addon['path'] . 'assets/images/thumbnail.jpg' ) ) {
					$thumbnail_url = $addon['url'] . 'assets/images/thumbnail.jpg';
				} elseif ( file_exists( $addon['path'] . 'assets/images/thumbnail.svg' ) ) {
					$thumbnail_url = $addon['url'] . 'assets/images/thumbnail.svg';
				}

				$plugins_data[ $base_name ]['thumb_url'] = $thumbnail_url;

				/**
				 * Checking if there any dependant plugin exists
				 */
				$depends          = tutor_utils()->array_get( 'depend_plugins', $addon );
				$plugins_required = array();
				if ( tutor_utils()->count( $depends ) ) {
					foreach ( $depends as $plugin_base => $plugin_name ) {
						if ( ! is_plugin_active( $plugin_base ) ) {
							$plugins_required[ $plugin_base ] = $plugin_name;
						}
					}
				}

				$depended_plugins = array();
				foreach ( $plugins_required as $required_plugin ) {
					array_push( $depended_plugins, $required_plugin );
				}

				$plugins_data[ $base_name ]['plugins_required'] = $depended_plugins;

				// Check if it's notifications.
				if ( function_exists( 'tutor_notifications' ) && $base_name == tutor_notifications()->basename ) {

					$required = array();
					version_compare( PHP_VERSION, '7.2.5', '>=' ) ? 0 : $required[] = __( 'PHP 7.2.5 or greater is required', 'tutor' );
					! is_ssl() ? $required[]                                        = __( 'SSL certificate', 'tutor' ) : 0;

					foreach ( array( 'curl', 'gmp', 'mbstring', 'openssl' ) as $ext ) {
						! extension_loaded( $ext ) ? $required[] = 'PHP extension <strong>' . $ext . '</strong>' : 0;
					}

					$plugins_data[ $base_name ]['ext_required'] = $required;
				}
			}
		}

		$prepared_addons = array();
		foreach ( $plugins_data as $tutor_addon ) {
			array_push( $prepared_addons, $tutor_addon );
		}

		return $prepared_addons;
	}

	/**
	 * Get all notifications
	 */
	public function tutor_get_all_addons() {

		// Check and verify the request.
		tutor_utils()->checking_nonce();

		// All good, let's proceed.
		$all_addons = $this->prepare_addons_data();

		wp_send_json_success(
			array(
				'addons' => $all_addons,
			)
		);
	}

	/**
	 * Method for enable / disable addons
	 */
	public function addon_enable_disable() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$addonsConfig = maybe_unserialize( get_option( 'tutor_addons_config' ) );

		// $isEnable = (bool) sanitize_text_field( tutor_utils()->avalue_dot( 'isEnable', $_POST ) );
		// $addonFieldName = sanitize_text_field( tutor_utils()->avalue_dot( 'addonFieldName', $_POST ) );
		$addonFieldNames = json_decode( stripslashes( ( tutor_utils()->avalue_dot( 'addonFieldNames', $_POST ) ) ), true );

		foreach ( $addonFieldNames as $addonFieldName => $isEnable ) {
			do_action( 'tutor_addon_before_enable_disable' );
			if ( $isEnable ) {
				do_action( "tutor_addon_before_enable_{$addonFieldName}" );
				do_action( 'tutor_addon_before_enable', $addonFieldName );
				$addonsConfig[ $addonFieldName ]['is_enable'] = 1;
				update_option( 'tutor_addons_config', $addonsConfig );

				do_action( 'tutor_addon_after_enable', $addonFieldName );
				do_action( "tutor_addon_after_enable_{$addonFieldName}" );
			} else {
				do_action( "tutor_addon_before_disable_{$addonFieldName}" );
				do_action( 'tutor_addon_before_disable', $addonFieldName );
				$addonsConfig[ $addonFieldName ]['is_enable'] = 0;
				update_option( 'tutor_addons_config', $addonsConfig );

				do_action( 'tutor_addon_after_disable', $addonFieldName );
				do_action( "tutor_addon_after_disable_{$addonFieldName}" );
			}
			do_action( 'tutor_addon_after_enable_disable' );
		}

		wp_send_json_success();
	}

	/**
	 * Process ajax login
	 *
	 * @since v.1.6.3
	 */
	public function process_ajax_login() {
		tutor_utils()->checking_nonce();

		$username    = tutor_utils()->array_get( 'log', $_POST );
		$password    = tutor_utils()->array_get( 'pwd', $_POST );
		$redirect_to = tutor_utils()->array_get( 'redirect_to', $_POST );

		try {
			$creds = array(
				'user_login'    => trim( wp_unslash( $username ) ),
				'user_password' => $password,
				'remember'      => isset( $_POST['rememberme'] ),
			);

			$validation_error = new \WP_Error();
			$validation_error = apply_filters( 'tutor_process_login_errors', $validation_error, $creds['user_login'], $creds['user_password'] );

			if ( $validation_error->get_error_code() ) {
				wp_send_json_error(
					array(
						'message' => $validation_error->get_error_message(),
					)
				);
			}

			if ( empty( $creds['user_login'] ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'Username is required.', 'tutor' ),
					)
				);
			}

			// On multisite, ensure user exists on current site, if not add them before allowing login.
			if ( is_multisite() ) {
				$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

				if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
					add_user_to_blog( get_current_blog_id(), $user_data->ID, 'customer' );
				}
			}

			// Perform the login.
			$user = wp_signon( apply_filters( 'tutor_login_credentials', $creds ), is_ssl() );

			if ( is_wp_error( $user ) ) {
				wp_send_json_error(
					array(
						'message' => $user->get_error_message(),
					)
				);
			} else {
				// since 1.9.8 do enroll if guest attempt to enroll
				if ( ! empty( $_POST['tutor_course_enroll_attempt'] ) && is_a( $user, 'WP_User' ) ) {
					do_action( 'tutor_do_enroll_after_login_if_attempt', $_POST['tutor_course_enroll_attempt'], $user->ID );
				}
				wp_send_json_success(
					array(
						'redirect_to' => apply_filters( 'tutor_login_redirect_url', $redirect_to, $user ),
					)
				);
			}
		} catch ( \Exception $e ) {
			do_action( 'tutor_login_failed' );
			wp_send_json_error( apply_filters( 'login_errors', $e->getMessage() ) );
		}
	}

	/**
	 * Create/Update announcement
	 *
	 * @since  v.1.7.9
	 */
	public function create_or_update_annoucement() {
		tutor_utils()->checking_nonce();

		$error                = array();
		$course_id            = sanitize_text_field( $_POST['tutor_announcement_course'] );
		$announcement_title   = sanitize_text_field( $_POST['tutor_announcement_title'] );
		$announcement_summary = sanitize_textarea_field( $_POST['tutor_announcement_summary'] );

		// Check if user can manage this announcment
		if ( ! tutor_utils()->can_user_manage( 'course', $course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		// set data and sanitize it
		$form_data = array(
			'post_type'    => 'tutor_announcements',
			'post_title'   => $announcement_title,
			'post_content' => $announcement_summary,
			'post_parent'  => $course_id,
			'post_status'  => 'publish',
		);

		if ( isset( $_POST['announcement_id'] ) ) {
			$form_data['ID'] = sanitize_text_field( $_POST['announcement_id'] );
		}

		// validation message set
		if ( empty( $form_data['post_parent'] ) ) {
			$error['post_parent'] = __( 'Course name required', 'tutor' );

		}

		if ( empty( $form_data['post_title'] ) ) {
			$error['post_title'] = __( 'Announcement title required', 'tutor' );
		}

		if ( empty( $form_data['post_content'] ) ) {
			$error['post_content'] = __( 'Announcement summary required', 'tutor' );

		}

		if ( empty( $form_data['post_content'] ) ) {
			$error['post_content'] = __( 'Announcement summary required', 'tutor' );

		}

		// If validation fails
		if ( count( $error ) > 0 ) {
			wp_send_json_error(
				array(
					'message' => __( 'All fields required!', 'tutor' ),
					'fields'  => $error,
				)
			);
		}

		// insert or update post
		$post_id = wp_insert_post( $form_data );
		if ( $post_id > 0 ) {
			$announcement = get_post( $post_id );
			$action_type  = sanitize_textarea_field( $_POST['action_type'] );

			do_action( 'tutor_announcements/after/save', $post_id, $announcement, $action_type );

			$resp_message = $action_type == 'create' ? __( 'Announcement created successfully', 'tutor' ) : __( 'Announcement updated successfully', 'tutor' );
			wp_send_json_success( array( 'message' => $resp_message ) );
		}

		wp_send_json_error( array( 'message' => __( 'Something Went Wrong!', 'tutor' ) ) );
	}

	/**
	 * Delete announcement
	 *
	 * @since  v.1.7.9
	 */
	public function delete_annoucement() {
		$announcement_id = sanitize_text_field( $_POST['announcement_id'] );
		tutor_utils()->checking_nonce();

		if ( ! tutor_utils()->can_user_manage( 'announcement', $announcement_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$delete = wp_delete_post( $announcement_id );
		if ( $delete ) {
			wp_send_json_success( array( 'message' => __( 'Announcement deleted successfully', 'tutor' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Announcement delete failed', 'tutor' ) ) );
	}
}
