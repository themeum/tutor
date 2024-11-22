<?php
/**
 * Manage Course Related Logic
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use stdClass;
use Tutor\Ecommerce\Ecommerce;
use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use Tutor\Traits\JsonResponse;
use TutorPro\CourseBundle\Models\BundleModel;

/**
 * Course Class
 *
 * @since 1.0.0
 */
class Course extends Tutor_Base {
	use JsonResponse;

	/**
	 * Course Price type
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PRICE_TYPE_FREE         = 'free';
	const PRICE_TYPE_PAID         = 'paid';
	const PRICE_TYPE_SUBSCRIPTION = 'subscription';

	/**
	 * Course price and sale price
	 *
	 * @since 3.0.0
	 */
	const COURSE_PRICE_TYPE_META     = '_tutor_course_price_type';
	const COURSE_PRICE_META          = 'tutor_course_price';
	const COURSE_SALE_PRICE_META     = 'tutor_course_sale_price';
	const COURSE_SELLING_OPTION_META = 'tutor_course_selling_option';
	const COURSE_PRODUCT_ID_META     = '_tutor_course_product_id';

	/**
	 * Selling option constants
	 *
	 * @since 3.0.0
	 */
	const SELLING_OPTION_ONE_TIME     = 'one_time';
	const SELLING_OPTION_SUBSCRIPTION = 'subscription';
	const SELLING_OPTION_BOTH         = 'both';


	/**
	 * Additional course meta info
	 *
	 * @var array
	 */
	private $additional_meta = array(
		'_tutor_enable_qa',
		'_tutor_is_public_course',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @since 3.0.0 $register_hooks param added to reuse this class.
	 *
	 * @param bool $register_hooks register hooks.
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		parent::__construct();

		if ( ! $register_hooks ) {
			return;
		}

		add_action( 'save_post_' . $this->course_post_type, array( $this, 'save_course_meta' ), 10, 2 );

		add_action( 'wp_ajax_tutor_save_topic', array( $this, 'tutor_save_topic' ) );
		add_action( 'wp_ajax_tutor_delete_topic', array( $this, 'tutor_delete_topic' ) );

		/**
		 * Frontend Action
		 */
		add_action( 'template_redirect', array( $this, 'enroll_now' ) );
		add_action( 'init', array( $this, 'mark_course_complete' ) );

		/**
		 * Frontend Dashboard
		 */
		add_action( 'wp_ajax_tutor_delete_dashboard_course', array( $this, 'tutor_delete_dashboard_course' ) );

		/**
		 * Gutenberg author support
		 */
		add_filter( 'wp_insert_post_data', array( $this, 'tutor_add_gutenberg_author' ), 99, 2 );

		/**
		 * Do Stuff for the course save from frontend
		 */
		add_action( 'save_tutor_course', array( $this, 'attach_product_with_course' ), 10, 2 );

		/**
		 * Add course level to course settings
		 *
		 * @since v.1.4.1
		 */
		add_filter( 'tutor_course_settings_tabs', array( $this, 'add_course_level_to_settings' ) );

		/**
		 * Enable Disable Course Details Page Feature
		 *
		 * @since v.1.4.8
		 */
		$this->course_elements_enable_disable();

		/**
		 * Check if course starting, set meta if starting
		 *
		 * @since v.1.4.8
		 */
		add_action( 'tutor_lesson_load_before', array( $this, 'tutor_lesson_load_before' ) );

		/**
		 * Filter product in shop page
		 *
		 * @since v.1.4.9
		 */
		$this->filter_product_in_shop_page();

		/**
		 * Remove the course price if enrolled
		 *
		 * @since 1.5.8
		 */
		add_filter( 'tutor_course_price', array( $this, 'remove_price_if_enrolled' ) );

		/**
		 * Remove course complete button if course completion is strict mode
		 *
		 * @since v.1.6.1
		 */
		add_filter( 'tutor_course/single/complete_form', array( $this, 'tutor_lms_hide_course_complete_btn' ) );
		add_filter( 'get_gradebook_generate_form_html', array( $this, 'get_generate_greadbook' ) );

		/**
		 * Add social share content in header
		 *
		 * @since v.1.6.3
		 */
		add_action( 'wp_head', array( $this, 'social_share_content' ) );

		/**
		 * Delete course data after deleted course
		 *
		 * @since v.1.6.6
		 */
		add_action( 'deleted_post', array( new CourseModel(), 'delete_course_data' ) );

		/**
		 * Delete course data after deleted course
		 *
		 * @since v.1.8.2
		 */
		add_action( 'before_delete_post', array( $this, 'delete_associated_enrollment' ) );

		/**
		 * Show only own uploads in media library if user is instructor
		 *
		 * @since v1.8.9
		 */
		add_filter( 'posts_where', array( $this, 'restrict_media' ) );

		/**
		 * Restrict new enrol/purchase button if course member limit reached
		 *
		 * @since v1.9.0
		 */
		add_filter( 'tutor_course_restrict_new_entry', array( $this, 'restrict_new_student_entry' ) );

		/**
		 * Reset course progress on retake
		 *
		 * @since v1.9.5
		 */
		add_action( 'wp_ajax_tutor_reset_course_progress', array( $this, 'tutor_reset_course_progress' ) );

		/**
		 * Popup for review
		 *
		 * @since v1.9.7
		 */
		add_action( 'wp_footer', array( $this, 'popup_review_form' ) );
		add_action( 'wp_ajax_tutor_clear_review_popup_data', array( $this, 'clear_review_popup_data' ) );

		/**
		 * Do enroll after login if guest take enroll attempt
		 *
		 * @since 1.9.8
		 */
		add_action( 'tutor_do_enroll_after_login_if_attempt', array( $this, 'enroll_after_login_if_attempt' ), 10, 2 );

		add_action( 'wp_ajax_tutor_update_course_content_order', array( $this, 'tutor_update_course_content_order' ) );

		add_action( 'wp_ajax_tutor_get_wc_product', array( $this, 'get_wc_product' ) );
		add_action( 'wp_ajax_tutor_get_wc_products', array( $this, 'get_wc_products' ) );

		add_action( 'wp_ajax_tutor_course_enrollment', array( $this, 'course_enrollment' ) );

		/**
		 * After trash a course redirect to course list page
		 *
		 * @since 2.1.7
		 */
		add_action( 'trashed_post', __CLASS__ . '::redirect_to_course_list_page' );

		add_filter( 'tutor_enroll_required_login_class', array( $this, 'add_enroll_required_login_class' ) );

		/**
		 * Remove wp trash button if instructor settings is disabled
		 *
		 * @since 2.7.3
		 */
		add_action( 'tutor_option_save_after', array( $this, 'disable_course_trash_instructor' ) );

		/**
		 * New course builder
		 *
		 * @since 3.0.0
		 */
		add_action( 'admin_init', array( $this, 'load_course_builder' ) );
		add_action( 'template_redirect', array( $this, 'load_course_builder' ) );
		add_action( 'tutor_before_course_builder_load', array( $this, 'enqueue_course_builder_assets' ) );
		add_action( 'tutor_course_builder_footer', array( $this, 'load_wp_link_modal' ) );
		add_action( 'admin_menu', array( $this, 'load_media_scripts' ) );
		add_action( 'init', array( $this, 'load_media_scripts' ) );

		/**
		 * Ajax list
		 *
		 * @since 3.0.0
		 */
		add_action( 'wp_ajax_tutor_create_new_draft_course', array( $this, 'ajax_create_new_draft_course' ) );
		add_action( 'wp_ajax_tutor_course_list', array( $this, 'ajax_course_list' ) );
		add_action( 'wp_ajax_tutor_create_course', array( $this, 'ajax_create_course' ) );
		add_action( 'wp_ajax_tutor_course_details', array( $this, 'ajax_course_details' ) );
		add_action( 'wp_ajax_tutor_course_contents', array( $this, 'ajax_course_contents' ) );
		add_action( 'wp_ajax_tutor_update_course', array( $this, 'ajax_update_course' ) );
		add_action( 'wp_ajax_tutor_unlink_page_builder', array( $this, 'ajax_unlink_page_builder' ) );

		add_filter( 'tutor_user_list_access', array( $this, 'user_list_access_for_instructor' ) );
		add_filter( 'tutor_user_list_args', array( $this, 'user_list_args_for_instructor' ) );

		add_filter( 'template_include', array( $this, 'handle_password_protected' ) );
	}

	/**
	 * Handle password protected course/bundle.
	 *
	 * @since 3.0.0
	 *
	 * @param string $template template path.
	 *
	 * @return string template path.
	 */
	public function handle_password_protected( $template ) {
		if ( is_single() ) {
			$current_post_type = get_post_type( get_the_ID() );
			$post_types        = array( tutor()->course_post_type, tutor()->bundle_post_type );
			if ( in_array( $current_post_type, $post_types, true ) && post_password_required() ) {
				remove_all_filters( 'template_include' );
				return tutor()->path . '/templates/single/password-protected.php';
			}
		}

		return $template;
	}

	/**
	 * Remove move to trash button on WordPress editor for instructor.
	 *
	 * @since 2.7.3
	 *
	 * @return void
	 */
	public function disable_course_trash_instructor() {
		$can_trash_post = tutor_utils()->get_option( 'instructor_can_delete_course' );
		$role           = get_role( tutor()->instructor_role );
		if ( ! $can_trash_post ) {
			$role->remove_cap( 'delete_tutor_courses' );
			$role->remove_cap( 'delete_tutor_course' );
		} else {
			$role->add_cap( 'delete_tutor_courses' );
			$role->add_cap( 'delete_tutor_course' );
		}
	}

	/**
	 * Check if the video source type is valid
	 *
	 * @since 3.0.0
	 *
	 * @param string $source_type source type.
	 *
	 * @return boolean
	 */
	private function is_valid_video_source_type( string $source_type ): bool {
		$supported_types = tutor_utils()->get_option( 'supported_video_sources', array() );
		if ( is_string( $supported_types ) ) {
			$supported_types = array( $supported_types );
		}

		return in_array( $source_type, $supported_types, true );
	}

	/**
	 * Get course selling options.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_selling_options() {
		return array(
			self::SELLING_OPTION_ONE_TIME,
			self::SELLING_OPTION_SUBSCRIPTION,
			self::SELLING_OPTION_BOTH,
		);
	}

	/**
	 * Get course selling option
	 *
	 * @since 3.0.0
	 *
	 * @param int $course_id course id.
	 *
	 * @return string
	 */
	public static function get_selling_option( $course_id ) {
		return get_post_meta( $course_id, self::COURSE_SELLING_OPTION_META, true );
	}

	/**
	 * Validate video source
	 *
	 * @since 3.0.0
	 *
	 * @param array $params array of params.
	 * @param array $errors array of errors.
	 *
	 * @return void
	 */
	public function validate_video_source( $params, &$errors ) {
		if ( isset( $params['video'] ) ) {
			$video_source = isset( $params['video']['source'] ) ? $params['video']['source'] : '';

			if ( '' === $video_source ) {
				$errors['video_source'] = __( 'Video source is required', 'tutor' );
			} else {
				if ( ! $this->is_valid_video_source_type( $video_source ) ) {
					$errors['video_source'] = __( 'Invalid video source', 'tutor' );
				}
			}
		}
	}

	/**
	 * Prepare course categories & tags
	 *
	 * @since 3.0.0
	 *
	 * @param array $params post params.
	 * @param array $errors array of errors.
	 *
	 * @return void
	 */
	public function prepare_course_cats_tags( &$params, &$errors ) {
		if ( isset( $params['course_categories'] ) ) {
			if ( ! is_array( $params['course_categories'] ) || empty( $params['course_categories'] ) ) {
				$errors['course_categories'] = __( 'Invalid course categories', 'tutor' );
			} else {
				$params['course_categories'] = $params['course_categories'];
			}
		}

		if ( isset( $params['course_tags'] ) ) {
			if ( ! is_array( $params['course_tags'] ) || empty( $params['course_tags'] ) ) {
				$errors['course_tags'] = __( 'Invalid course tags', 'tutor' );
			} else {
				$params['course_tags'] = $params['course_tags'];
			}
		}
	}

	/**
	 * Setup course categories and tags
	 *
	 * @since 3.0.0
	 *
	 * @param int   $post_id post id.
	 * @param array $params  array of params.
	 *
	 * @return void
	 */
	public function setup_course_categories_tags( $post_id, $params ) {
		if ( isset( $params['course_categories'] ) && is_array( $params['course_categories'] ) ) {
			$category_names = array();

			foreach ( $params['course_categories'] as $category_id ) {
				$term = get_term( $category_id, 'course-category' );

				if ( ! is_wp_error( $term ) && $term ) {
					$category_names[] = $term->name;
				}
			}

			// Set category names on the post.
			wp_set_object_terms( $post_id, $category_names, 'course-category' );
		} else {
			wp_set_object_terms( $post_id, array(), 'course-category' );
		}

		if ( isset( $params['course_tags'] ) && is_array( $params['course_tags'] ) ) {
			$tag_names = array();

			foreach ( $params['course_tags'] as $tag_id ) {
				$term = get_term( $tag_id, 'course-tag' );

				if ( ! is_wp_error( $term ) && $term ) {
					$tag_names[] = $term->name;
				}
			}

			// Set tag names on the post.
			wp_set_object_terms( $post_id, $tag_names, 'course-tag' );
		} else {
			wp_set_object_terms( $post_id, array(), 'course-tag' );
		}
	}

	/**
	 * Validate price for course create
	 *
	 * @since 3.0.0
	 *
	 * @param array $params array of params.
	 * @param array $errors array of errors.
	 *
	 * @return void
	 */
	public function validate_price( $params, &$errors ) {
		if ( isset( $params['pricing'] ) ) {
			$type = $params['pricing']['type'] ?? '';

			if ( '' === $type || ! in_array( $type, array( self::PRICE_TYPE_FREE, self::PRICE_TYPE_PAID ), true ) ) {
				$errors['pricing'] = __( 'Invalid price type', 'tutor' );
			}

			if ( self::PRICE_TYPE_PAID === $type ) {
				$monetize_by = tutor_utils()->get_option( 'monetize_by' );
				if ( 'wc' === $monetize_by ) {
					$product_id = (int) isset( $params['pricing']['product_id'] ) ? $params['pricing']['product_id'] : 0;
					// $product_id = 0 then new WC product will be created.
					if ( $product_id ) {
						$product = wc_get_product( $product_id );
						if ( is_a( $product, 'WC_Product' ) ) {
							$is_linked_with_course = tutor_utils()->product_belongs_with_course( $product_id );
							if ( $is_linked_with_course ) {
								$errors['pricing'] = __( 'Product already linked with course', 'tutor' );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Validate price
	 *
	 * @since 3.0.0
	 *
	 * @param array $params array of params.
	 * @param array $errors array of errors.
	 * @param int   $course_id course id.
	 *
	 * @return void
	 */
	public function validate_price_for_update( $params, &$errors, $course_id ) {
		if ( isset( $params['pricing'] ) ) {
			$type = $params['pricing']['type'] ?? '';

			if ( '' === $type || ! in_array( $type, array( self::PRICE_TYPE_FREE, self::PRICE_TYPE_PAID, self::PRICE_TYPE_SUBSCRIPTION ), true ) ) {
				$errors['pricing'] = __( 'Invalid price type', 'tutor' );
			}

			if ( self::PRICE_TYPE_PAID === $type ) {
				$monetize_by = tutor_utils()->get_option( 'monetize_by' );
				if ( 'wc' === $monetize_by ) {
					$course_product_id = tutor_utils()->get_course_product_id( $course_id );
					$product_id        = isset( $params['pricing']['product_id'] ) ? (int) $params['pricing']['product_id'] : 0;

					if ( $product_id ) {
						$product = wc_get_product( $product_id );
						if ( is_a( $product, 'WC_Product' ) ) {
							if ( $course_product_id !== $product_id ) {
								$is_linked_with_course = tutor_utils()->product_belongs_with_course( $product_id );
								if ( $is_linked_with_course ) {
									$errors['pricing'] = __( 'Product already linked with course', 'tutor' );
								}
							}
						} else {
							$errors['pricing'] = __( 'Invalid product', 'tutor' );
						}
					} else {
						/**
						 * If user does not select WC product
						 * Then automatic WC product will be create name with course title.
						 */
						if ( ! isset( $params['course_price'] ) || ! floatval( $params['course_price'] ) ) {
							$errors['pricing'] = __( 'Price is required', 'tutor' );
						}
					}
				}
			}
		}
	}

	/**
	 * Prepare course meta data for update
	 *
	 * @param array $params params.
	 *
	 * @return void
	 */
	public function prepare_create_post_meta( $params ) {
		$additional_content = isset( $params['additional_content'] ) ? $params['additional_content'] : array();

		$course_benefits = isset( $additional_content['course_benefits'] ) ? $additional_content['course_benefits'] : '';

		$course_target_audience = isset( $additional_content['course_target_audience'] ) ? $additional_content['course_target_audience'] : '';

		$course_duration = isset( $additional_content['course_duration'] ) ? array(
			'hours'   => $additional_content['course_duration']['hours'] ?? '',
			'minutes' => $additional_content['course_duration']['minutes'] ?? '',
		) : array();

		$course_materials = isset( $additional_content['course_material_includes'] ) ? $additional_content['course_material_includes'] : '';

		$course_requirements = isset( $additional_content['course_requirements'] ) ? $additional_content['course_requirements'] : '';

		$pricing = isset( $params['pricing'] ) ? array(
			'type'       => $params['pricing']['type'] ?? self::PRICE_TYPE_FREE,
			'product_id' => (int) $params['pricing']['product_id'] ?? -1,
		) : array(
			'type'       => self::PRICE_TYPE_FREE,
			'product_id' => -1,
		);

		// Setup global $_POST array.
		$_POST['_tutor_course_additional_data_edit'] = true;

		$_POST['tutor_course_price_type']  = $pricing['type'];
		$_POST['course_duration']          = $course_duration;
		$_POST['tutor_course_price_type']  = $pricing['type'];
		$_POST['_tutor_course_product_id'] = $pricing['product_id'];
		$_POST['_tutor_course_level']      = $params['course_level'];
		$_POST['course_benefits']          = $course_benefits;
		$_POST['course_requirements']      = $course_requirements;
		$_POST['course_target_audience']   = $course_target_audience;
		$_POST['course_material_includes'] = $course_materials;

		if ( isset( $params['enable_qna'] ) && 'yes' === $params['enable_qna'] ) {
			$_POST['_tutor_enable_qa'] = 'yes';
		}

		if ( isset( $params['_tutor_is_public_course'] ) && 'yes' === $params['_tutor_is_public_course'] ) {
			$_POST['_tutor_is_public_course'] = 'yes';
		}

		// Set course price.
		if ( -1 !== $pricing['product_id'] ) {
			$product = wc_get_product( $pricing['product_id'] );
			if ( is_a( $product, 'WC_Product' ) ) {
				$regular_price = $product->get_regular_price();
				$sale_price    = $product->get_sale_price();

				$_POST['course_price']      = $regular_price;
				$_POST['course_sale_price'] = $sale_price;
			}
		}
	}

	/**
	 * Prepare course meta data for update
	 *
	 * @since 3.0.0
	 *
	 * @param array $params params.
	 *
	 * @throws \Exception Throw new exception.
	 *
	 * @return mixed
	 */
	public function prepare_update_post_meta( $params ) {
		$post_id = (int) $params['ID'];

		$additional_content = isset( $params['additional_content'] ) ? $params['additional_content'] : array();

		if ( ! empty( $additional_content ) ) {

			$course_benefits = isset( $additional_content['course_benefits'] ) ? $additional_content['course_benefits'] : '';

			$course_target_audience = isset( $additional_content['course_target_audience'] ) ? $additional_content['course_target_audience'] : '';

			$course_duration = isset( $additional_content['course_duration'] ) ? array(
				'hours'   => $additional_content['course_duration']['hours'] ?? '',
				'minutes' => $additional_content['course_duration']['minutes'] ?? '',
			) : array();

			$course_materials = isset( $additional_content['course_material_includes'] ) ? $additional_content['course_material_includes'] : '';

			$course_requirements = isset( $additional_content['course_requirements'] ) ? $additional_content['course_requirements'] : '';

			if ( '' !== $course_benefits ) {
				update_post_meta( $post_id, '_tutor_course_benefits', $course_benefits );
			}

			if ( '' !== $course_requirements ) {
				update_post_meta( $post_id, '_tutor_course_requirements', $course_requirements );
			}

			if ( '' !== $course_target_audience ) {
				update_post_meta( $post_id, '_tutor_course_target_audience', $course_target_audience );
			}

			if ( '' !== $course_materials ) {
				update_post_meta( $post_id, '_tutor_course_material_includes', $course_materials );
			}

			if ( ! empty( $course_duration ) ) {
				update_post_meta( $post_id, '_course_duration', $course_duration );
			}
		}

		if ( isset( $params['pricing'] ) && ! empty( $params['pricing'] ) ) {
			try {
				if ( isset( $params['pricing']['type'] ) ) {
					update_post_meta( $post_id, self::COURSE_PRICE_TYPE_META, $params['pricing']['type'] );
				}
				if ( isset( $params['pricing']['product_id'] ) ) {
					update_post_meta( $post_id, '_tutor_course_product_id', $params['pricing']['product_id'] );
				}
			} catch ( \Throwable $th ) {
				throw new \Exception( $th->getMessage() );
			}
		}

		update_post_meta( $post_id, '_tutor_enable_qa', $params['enable_qna'] ?? 'yes' );
		update_post_meta( $post_id, '_tutor_is_public_course', $params['is_public_course'] ?? 'no' );
		update_post_meta( $post_id, '_tutor_course_level', $params['course_level'] );
	}

	/**
	 * Prepare course settings meta
	 *
	 * @since 3.0.0
	 *
	 * @param array $params params.
	 *
	 * @return void
	 */
	public function prepare_course_settings( $params ) {
		if ( isset( $params['course_settings'] ) ) {
			$_POST['_tutor_course_settings'] = $params['course_settings'];
		}
	}

	/**
	 * Check access before course builder ajax request.
	 *
	 * @since 3.0.0
	 *
	 * @param int $course_id course id.
	 *
	 * @return void
	 */
	public function check_access( $course_id = null ) {
		$has_access = false;

		if ( $course_id ) {
			$has_access = tutor_utils()->can_user_edit_course( get_current_user_id(), $course_id );
		} else {
			$has_access = User::is_admin() || User::is_instructor();
		}

		if ( ! $has_access ) {
			$this->json_response(
				tutor_utils()->error_message( HttpHelper::STATUS_UNAUTHORIZED ),
				null,
				HttpHelper::STATUS_UNAUTHORIZED
			);
		}
	}

	/**
	 * Validate request inputs.
	 *
	 * @param array $params input params.
	 * @param array $exclude exclude key from rules.
	 *
	 * @return object
	 */
	public function validate_inputs( $params, $exclude = array() ) {
		$status_str = implode( ',', CourseModel::get_status_list() );
		$rules      = array(
			'course_id'        => 'required|numeric',
			'post_title'       => 'required',
			'post_author'      => 'user_exists',
			'post_status'      => "required|match_string:{$status_str}",
			'enable_qna'       => 'if_input|match_string:yes,no',
			'is_public_course' => 'if_input|match_string:yes,no',
		);

		foreach ( $exclude as $key ) {
			if ( isset( $rules[ $key ] ) ) {
				unset( $rules[ $key ] );
			}
		}

		return ValidationHelper::validate( $rules, $params );
	}

	/**
	 * Create new draft course
	 *
	 * @since 3.0.0
	 *
	 * @return void  JSON response
	 */
	public function ajax_create_new_draft_course() {
		tutor_utils()->check_nonce();

		$this->check_access();

		$course_id = wp_insert_post(
			array(
				'post_title'  => __( 'New Course', 'tutor' ),
				'post_type'   => tutor()->course_post_type,
				'post_status' => 'draft',
				'post_name'   => 'new-course',
			)
		);

		if ( is_wp_error( $course_id ) ) {
			$this->json_response( $course_id->get_error_message(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}

		update_post_meta( $course_id, self::COURSE_PRICE_TYPE_META, self::PRICE_TYPE_FREE );

		$link = admin_url( 'admin.php?page=create-course' );
		if ( Input::post( 'from_dashboard', false, Input::TYPE_BOOL ) ) {
			$link = tutor_utils()->tutor_dashboard_url( 'create-course' );
		}

		$link = add_query_arg( array( 'course_id' => $course_id ), $link );

		$this->json_response(
			__( 'Draft course created', 'tutor' ),
			$link,
			HttpHelper::STATUS_CREATED
		);
	}

	/**
	 * Get course list
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_course_list() {
		$this->check_access();

		$args = array(
			'post_type'      => tutor()->course_post_type,
			'posts_per_page' => -1,
		);

		$exclude = Input::post( 'exclude', array(), Input::TYPE_ARRAY );
		if ( count( $exclude ) ) {
			$exclude         = array_filter(
				$exclude,
				function( $id ) {
					return is_numeric( $id );
				}
			);
			$args['exclude'] = $exclude;
		}

		$courses = get_posts( $args );

		$items = array();
		foreach ( $courses as $course ) {
			$tmp                 = new stdClass();
			$tmp->id             = $course->ID;
			$tmp->post_title     = $course->post_title;
			$tmp->featured_image = get_the_post_thumbnail_url( $course->ID );

			if ( ! $tmp->featured_image ) {
				$tmp->featured_image = CourseModel::get_course_preview_image_placeholder();
			}

			$items[] = $tmp;
		}

		$this->json_response(
			__( 'Course list fetched successfully', 'tutor' ),
			$items
		);
	}

	/**
	 * Create course by ajax request.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_create_course() {
		tutor_utils()->check_nonce();

		$this->check_access();

		$params = Input::sanitize_array(
			//phpcs:ignore WordPress.Security.NonceVerification.Missing
			$_POST,
			array(
				'post_content'             => 'wp_kses_post',
				'course_material_includes' => 'sanitize_textarea_field',
			)
		);

		$params['post_type'] = tutor()->course_post_type;

		// Validate inputs.
		$errors     = array();
		$validation = $this->validate_inputs( $params, array( 'course_id' ) );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		if ( User::is_instructor() ) {
			$params['post_author'] = get_current_user_id();
		}

		// Validate video source if user set video.
		$this->validate_video_source( $params, $errors );

		// Validate WC product.
		$this->validate_price( $params, $errors );

		// Set course categories and tags.
		$this->prepare_course_cats_tags( $params, $errors );
		$this->setup_course_price( $params );

		if ( ! empty( $errors ) ) {
			$this->json_response( __( 'Invalid input', 'tutor' ), $errors, HttpHelper::STATUS_UNPROCESSABLE_ENTITY );
		}

		$this->prepare_course_settings( $params );

		try {
			$this->prepare_create_post_meta( $params );
		} catch ( \Exception $e ) {
			$this->json_response( $e->getMessage(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}

		$course_id = wp_insert_post( $params );
		if ( is_wp_error( $course_id ) ) {
			$this->json_response( $course_id->get_error_message(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}

		// Set course cats & tags.
		$this->setup_course_categories_tags( $course_id, $params );

		// Update course thumb.
		if ( isset( $params['thumbnail_id'] ) ) {
			set_post_thumbnail( $course_id, $params['thumbnail_id'] );
		}

		$this->json_response(
			__( 'Course created successfully', 'tutor' ),
			$course_id,
			HttpHelper::STATUS_CREATED
		);
	}

	/**
	 * Setup course price
	 *
	 * @since 3.0.0
	 *
	 * @param array $params params.
	 *
	 * @return void
	 */
	public function setup_course_price( $params ) {
		if ( isset( $params['pricing'] )
			&& isset( $params['pricing']['product_id'] )
			&& is_numeric( $params['pricing']['product_id'] ) ) {
			$_POST['_tutor_course_product_id'] = $params['pricing']['product_id'];
		}
	}

	/**
	 * Update course by ajax request.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_update_course() {
		tutor_utils()->check_nonce();

		$params = Input::sanitize_array(
			//phpcs:ignore WordPress.Security.NonceVerification.Missing
			wp_slash( $_POST ),
			array(
				'post_content'             => 'wp_kses_post',
				'course_benefits'          => 'sanitize_textarea_field',
				'course_target_audience'   => 'sanitize_textarea_field',
				'course_material_includes' => 'sanitize_textarea_field',
				'course_requirements'      => 'sanitize_textarea_field',
			)
		);

		$course_id = (int) $params['course_id'];
		$this->check_access( $course_id );

		$errors     = array();
		$validation = $this->validate_inputs( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		// Validate video source if user set video.
		$this->validate_video_source( $params, $errors );

		// Validate WC product.
		$this->validate_price_for_update( $params, $errors, $course_id );

		// Set course categories and tags.
		$this->prepare_course_cats_tags( $params, $errors );

		$this->prepare_course_settings( $params );
		$this->setup_course_price( $params );

		if ( ! empty( $errors ) ) {
			$this->json_response( __( 'Invalid input', 'tutor' ), $errors, HttpHelper::STATUS_UNPROCESSABLE_ENTITY );
		}

		/**
		 * Can trash a course when user is admin or option `instructor_can_delete_course` is turned on.
		 */
		if ( CourseModel::STATUS_TRASH === $params['post_status'] ) {
			if ( User::is_admin() || tutor_utils()->get_option( 'instructor_can_delete_course', false ) ) {
				$params['post_status'] = CourseModel::STATUS_TRASH;
			} else {
				unset( $params['post_status'] );
			}
		}

		/**
		 * Can publish a course when user is admin or option `instructor_can_publish_course` is turned on.
		 * If instructor_can_publish_course is turned off then course status will be pending.
		 */
		if ( CourseModel::STATUS_PUBLISH === $params['post_status'] ) {
			$is_instructor_allowed_to_publish = (bool) tutor_utils()->get_option( 'instructor_can_publish_course', false );
			if ( ! User::is_admin() && ! $is_instructor_allowed_to_publish ) {
				$params['post_status'] = CourseModel::STATUS_PENDING;
			}
		}

		$params['ID'] = $course_id;
		$update_id    = wp_update_post( $params, true );
		if ( is_wp_error( $update_id ) ) {
			$this->json_response( $update_id->get_error_message(), null, HttpHelper::STATUS_INTERNAL_SERVER_ERROR );
		}

		$this->setup_course_categories_tags( $update_id, $params );
		$this->prepare_update_post_meta( $params );

		// Update course thumb.
		$thumbnail_id = Input::post( 'thumbnail_id', 0, Input::TYPE_INT );
		if ( $thumbnail_id ) {
			set_post_thumbnail( $update_id, $thumbnail_id );
		} else {
			delete_post_meta( $update_id, '_thumbnail_id' );
		}

		$this->json_response(
			__( 'Course update successfully', 'tutor' ),
			$update_id,
			HttpHelper::STATUS_OK
		);
	}

	/**
	 * Unlink page builder from editor.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_unlink_page_builder() {
		tutor_utils()->check_nonce();

		$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );
		$builder   = Input::post( 'builder' );
		$this->check_access( $course_id );

		if ( 'elementor' === $builder ) {
			delete_post_meta( $course_id, '_elementor_edit_mode' );
		} elseif ( 'droip' === $builder ) {
			delete_post_meta( $course_id, 'droip_editor_mode' );
		}

		$this->json_response(
			__( 'Builder unlinked successfully.', 'tutor' ),
			$course_id,
			HttpHelper::STATUS_OK
		);
	}

	/**
	 * Get all course contents by course id.
	 *
	 * @since 3.0.0
	 *
	 * @param int $course_id course id.
	 *
	 * @return array
	 */
	public function get_course_contents( $course_id ) {
		$data   = array();
		$topics = tutor_utils()->get_topics( $course_id );

		if ( $topics->have_posts() ) {
			foreach ( $topics->get_posts() as $post ) {
				$current_topic = array(
					'id'       => $post->ID,
					'title'    => $post->post_title,
					'summary'  => $post->post_content,
					'contents' => array(),
				);

				$topic_contents = tutor_utils()->get_course_contents_by_topic( $post->ID, -1 );

				if ( $topic_contents->have_posts() ) {
					foreach ( $topic_contents->get_posts() as $post ) {
						if ( tutor()->quiz_post_type === $post->post_type ) {
							$questions            = tutor_utils()->get_questions_by_quiz( $post->ID );
							$post->total_question = is_array( $questions ) ? count( $questions ) : 0;
						}

						array_push( $current_topic['contents'], $post );
					}
				}

				$current_topic = apply_filters( 'tutor_filter_course_content', $current_topic );

				array_push( $data, $current_topic );
			}
		}

		return $data;
	}

	/**
	 * Get course contents
	 *
	 * @since 3.0.0
	 */
	public function ajax_course_contents() {
		tutor_utils()->check_nonce();

		$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );

		$this->check_access( $course_id );

		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			$errors['course_id'] = __( 'Invalid course id', 'tutor' );
		}

		if ( ! empty( $errors ) ) {
			$this->json_response( __( 'Invalid input', 'tutor' ), $errors, HttpHelper::STATUS_UNPROCESSABLE_ENTITY );
		}

		$contents = $this->get_course_contents( $course_id );

		$this->json_response(
			__( 'Course contents fetched successfully', 'tutor' ),
			$contents
		);
	}

	/**
	 * Get course details by ID
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_course_details() {
		tutor_utils()->check_nonce();

		$errors    = array();
		$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );

		$this->check_access( $course_id );

		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			$errors['course_id'] = __( 'Invalid course id', 'tutor' );
		}

		if ( ! empty( $errors ) ) {
			$this->json_response( __( 'Invalid input', 'tutor' ), $errors, HttpHelper::STATUS_UNPROCESSABLE_ENTITY );
		}

		$price_type  = tutor_utils()->price_type( $course_id );
		$monetize_by = tutor_utils()->get_option( 'monetize_by' );

		$product_name = '';
		$price        = 0;
		$sale_price   = 0;
		$product_id   = tutor_utils()->get_course_product_id( $course_id );

		if ( 'wc' === $monetize_by ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$product_name = $product->get_name();
				$price        = $product->get_regular_price();
				$sale_price   = $product->get_sale_price();
			}
		}

		if ( 'tutor' === $monetize_by ) {
			$price      = get_post_meta( $course_id, self::COURSE_PRICE_META, true );
			$sale_price = get_post_meta( $course_id, self::COURSE_SALE_PRICE_META, true );
		}

		$course_pricing = array(
			'type'         => $price_type,
			'product_id'   => $product_id,
			'product_name' => $product_name,
			'price'        => $price,
			'sale_price'   => $sale_price,
		);

		$video_intro = get_post_meta( $course_id, '_video', true );
		if ( $video_intro ) {
			$source = $video_intro['source'] ?? '';
			if ( 'html5' === $source ) {
					$poster_url            = wp_get_attachment_url( $video['poster'] ?? 0 );
					$source_html5          = wp_get_attachment_url( $video['source_video_id'] ?? 0 );
					$video['poster_url']   = $poster_url;
					$video['source_html5'] = $source_html5;
			}
		}

		$course  = get_post( $course_id, ARRAY_A );
		$editors = tutor_utils()->get_editor_list( $course_id );

		$data = array(
			'editors'                  => array_values( $editors ),
			'editor_used'              => tutor_utils()->get_editor_used( $course_id ),
			'preview_link'             => get_preview_post_link( $course_id ),
			'post_author'              => tutor_utils()->get_tutor_user( $course['post_author'] ),
			'course_categories'        => wp_get_post_terms( $course_id, 'course-category' ),
			'course_tags'              => wp_get_post_terms( $course_id, 'course-tag' ),
			'thumbnail_id'             => get_post_meta( $course_id, '_thumbnail_id', true ),
			'thumbnail'                => get_the_post_thumbnail_url( $course_id ),

			'enable_qna'               => get_post_meta( $course_id, '_tutor_enable_qa', true ),
			'is_public_course'         => get_post_meta( $course_id, '_tutor_is_public_course', true ),
			'course_level'             => get_post_meta( $course_id, '_tutor_course_level', true ),
			'video'                    => $video_intro,
			'course_duration'          => get_post_meta( $course_id, '_course_duration', true ),
			'course_benefits'          => get_post_meta( $course_id, '_tutor_course_benefits', true ),
			'course_requirements'      => get_post_meta( $course_id, '_tutor_course_requirements', true ),
			'course_target_audience'   => get_post_meta( $course_id, '_tutor_course_target_audience', true ),
			'course_material_includes' => get_post_meta( $course_id, '_tutor_course_material_includes', true ),
			'monetize_by'              => $monetize_by,
			'course_pricing'           => $course_pricing,
			'course_settings'          => get_post_meta( $course_id, '_tutor_course_settings', true ),
			'step_completion_status'   => array(
				'basic'       => true,
				'curriculum'  => false,
				'additional'  => false,
				'certificate' => false,
			),
		);

		$data = apply_filters( 'tutor_course_details_response', array_merge( $course, $data ) );

		$this->json_response( __( 'Data retrieved successfully!' ), $data );
	}

	/**
	 * Load course builder.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function load_course_builder() {
		global $pagenow;

		$has_pro         = tutor()->has_pro;
		$has_access_role = User::has_any_role( array( User::ADMIN, User::INSTRUCTOR ) );

		$course_id       = Input::get( 'course_id', 0, Input::TYPE_INT );
		$backend_builder = is_admin() && 'admin.php' === $pagenow && 'create-course' === Input::get( 'page' );
		$backend_edit    = $backend_builder && $course_id;

		$is_frontend_builder = tutor_utils()->is_tutor_frontend_dashboard( 'create-course' );
		$frontend_edit       = $is_frontend_builder && $course_id;

		if ( $has_access_role && ( $backend_edit || ( $has_pro && $frontend_edit ) ) ) {
			$post_type       = get_post_type( $course_id );
			$can_edit_course = tutor_utils()->can_user_edit_course( get_current_user_id(), $course_id );

			if ( tutor()->course_post_type === $post_type && ( User::is_admin() || $can_edit_course ) ) {
				/**
				 * Edit trash course behavior
				 *
				 * @since 3.0.0
				 */
				if ( CourseModel::STATUS_TRASH === get_post_status( $course_id ) ) {
					$message = User::is_admin()
								? __( 'You cannot edit this course because it is in the Trash. Please restore it and try again', 'tutor' )
								: tutor_utils()->error_message();
					wp_die( esc_html( $message ) );
				}

				$this->load_course_builder_view();
			}
		}
	}

	/**
	 * Enqueue course builder assets like CSS, JS
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function enqueue_course_builder_assets() {
		// Fix: function print_emoji_styles is deprecated since version 6.4.0!
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		do_action( 'tutor_course_builder_before_wp_editor_load' );
		wp_enqueue_script( 'wp-tinymce' );
		wp_enqueue_script( 'mce-view' );
		wp_enqueue_editor();

		wp_enqueue_media();
		wp_enqueue_script( 'tutor-shared', tutor()->url . 'assets/js/tutor-shared.min.js', array( 'wp-i18n', 'wp-element' ), TUTOR_VERSION, true );
		wp_enqueue_script( 'tutor-course-builder', tutor()->url . 'assets/js/tutor-course-builder.min.js', array( 'wp-i18n', 'wp-element', 'tutor-shared' ), TUTOR_VERSION, true );

		$default_data = ( new Assets( false ) )->get_default_localized_data();

		if ( isset( $default_data['current_user']->data ) ) {
			$tutor_user = tutor_utils()->get_tutor_user( $default_data['current_user']->data->ID );
			$default_data['current_user']->data->tutor_profile_photo_url = $tutor_user->tutor_profile_photo_url;
		}

		/**
		 * Localized only options to protect sensitive info like API keys.
		 */
		$required_options = array(
			'monetize_by',
			'enable_course_marketplace',
			'course_permalink_base',
			'supported_video_sources',
			'enrollment_expiry_enabled',
			'enable_q_and_a_on_course',
			'instructor_can_delete_course',
			'chatgpt_enable',
			'hide_admin_bar_for_users',
			'enable_redirect_on_course_publish_from_frontend',
			'instructor_can_publish_course',
		);

		$full_settings                       = get_option( 'tutor_option', array() );
		$settings                            = Options_V2::get_only( $required_options );
		$settings['course_builder_logo_url'] = wp_get_attachment_image_url( $full_settings['tutor_frontend_course_page_logo_id'] ?? 0, 'full' );
		$settings['chatgpt_key_exist']       = tutor()->has_pro && ! empty( $full_settings['chatgpt_api_key'] ?? '' );
		$settings['youtube_api_key_exist']   = ! empty( $full_settings['lesson_video_duration_youtube_api_key'] ?? '' );

		$new_data = array( 'settings' => $settings );

		$data = array_merge( $default_data, $new_data );

		/**
		 * Course builder dashboard URL based on role and settings.
		 */
		$dashboard_url = tutor_utils()->tutor_dashboard_url();
		if ( User::is_admin() ) {
			$dashboard_url = get_admin_url();
		}

		/**
		 * EDD product list
		 */
		$monetize_by = tutor_utils()->get_option( 'monetize_by' );
		if ( 'edd' === $monetize_by && tutor_utils()->has_edd() ) {
			$data['edd_products'] = tutor_utils()->get_edd_products();
		}

		$difficulty_levels = array();
		foreach ( tutor_utils()->course_levels() as $value => $label ) {
			$difficulty_levels[] = array(
				'label' => $label,
				'value' => $value,
			);
		}

		$supported_video_sources = array();
		$saved_video_source_list = (array) ( $settings['supported_video_sources'] ?? array() );

		foreach ( tutor_utils()->get_video_sources( true ) as $value => $label ) {
			if ( in_array( $value, $saved_video_source_list, true ) ) {
				$supported_video_sources[] = array(
					'label' => $label,
					'value' => $value,
				);
			}
		}

		$data['dashboard_url']            = $dashboard_url;
		$data['backend_course_list_url']  = get_admin_url( null, 'admin.php?page=tutor' );
		$data['frontend_course_list_url'] = tutor_utils()->tutor_dashboard_url( 'my-courses' );
		$data['timezones']                = tutor_global_timezone_lists();
		$data['difficulty_levels']        = $difficulty_levels;
		$data['supported_video_sources']  = $supported_video_sources;
		$data['wp_rest_nonce']            = wp_create_nonce( 'wp_rest' );
		$data['max_upload_size']          = size_format( wp_max_upload_size() );

		$data = apply_filters( 'tutor_course_builder_localized_data', $data );

		wp_localize_script(
			'mce-view',
			'mceViewL10n',
			array(
				'shortcodes' => ! empty( $GLOBALS['shortcode_tags'] ) ? array_keys( $GLOBALS['shortcode_tags'] ) : array(),
			)
		);

		wp_localize_script( 'tutor-course-builder', '_tutorobject', $data );
		wp_set_script_translations( 'tutor-course-builder', 'tutor', tutor()->path . 'languages/' );
	}

	/**
	 * Load wp editor modal
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function load_wp_link_modal() {
		if ( is_admin() ) {
			include_once tutor()->path . 'views/modal/wp-editor-link.php';
		}
	}

	/**
	 * Load view for course builder.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function load_course_builder_view() {
		do_action( 'tutor_before_course_builder_load' );
		include_once tutor()->path . 'views/pages/course-builder.php';
		do_action( 'tutor_after_course_builder_load' );
		exit( 0 );
	}

	/**
	 * Add enroll require login class
	 *
	 * @since 2.6.0
	 *
	 * @param string $class_name css class name.
	 *
	 * @return string
	 */
	public function add_enroll_required_login_class( $class_name ) {
		$enabled_tutor_login = tutor_utils()->get_option( 'enable_tutor_native_login', null, true, true );
		if ( ! $enabled_tutor_login ) {
			return '';
		}

		return $class_name;
	}

	/**
	 * Get list of WC products.
	 *
	 * @since 2.5.0
	 * @since 3.0.0 exclude_linked_products, course_id are added.
	 *
	 * @return void
	 */
	public function get_wc_products() {
		$exclude                 = array();
		$exclude_linked_products = Input::has( 'exclude_linked_products' );
		$course_id               = Input::post( 'course_id', 0, Input::TYPE_INT );

		if ( $exclude_linked_products ) {
			$exclude = tutor_utils()->get_linked_product_ids();
		}

		if ( $course_id ) {
			$linked_product_id = tutor_utils()->get_course_product_id( $course_id );
			if ( $linked_product_id ) {
				$exclude = array_filter( $exclude, fn( $id )=> $linked_product_id !== (int) $id );
			}
		}

		$exclude = array_unique( $exclude );

		$this->json_response(
			__( 'Products retrieved successfully!', 'tutor' ),
			tutor_utils()->get_wc_products_db( $exclude ),
			HttpHelper::STATUS_OK
		);
	}

	/**
	 * Get course associate WC product info by Ajax request
	 *
	 * @since 2.0.7
	 *
	 * @return void
	 */
	public function get_wc_product() {
		tutor_utils()->checking_nonce();
		$product_id = Input::post( 'product_id' );
		$product    = wc_get_product( $product_id );
		$course_id  = Input::post( 'course_id', 0, Input::TYPE_INT );

		$is_linked_with_course = tutor_utils()->product_belongs_with_course( $product_id );

		/**
		 * If selected product is already linked with
		 * a course & it is not the current course the
		 * return error
		 *
		 * @since 2.1.0
		 */
		if ( is_object( $is_linked_with_course ) && $is_linked_with_course->post_id != $course_id ) {
			wp_send_json_error(
				__( 'One product can not be added to multiple course!', 'tutor' )
			);
		}

		if ( $product ) {
			$data = array(
				'name'          => $product->get_name(),
				'regular_price' => $product->get_regular_price(),
				'sale_price'    => $product->get_sale_price(),
			);
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( __( 'Product not found', 'tutor' ) );
		}
	}

	/**
	 * Update course content order
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tutor_update_course_content_order() {
		tutor_utils()->checking_nonce();

		if ( Input::has( 'content_parent' ) ) {
			$content_parent = Input::post( 'content_parent', array(), Input::TYPE_ARRAY );
			$topic_id       = tutor_utils()->array_get( 'parent_topic_id', $content_parent );
			$content_id     = tutor_utils()->array_get( 'content_id', $content_parent );

			if ( ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
				wp_send_json_success( array( 'message' => __( 'Access Denied!', 'tutor' ) ) );
				exit;
			}

			// Update the parent topic id of the content.
			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'post_parent' => $topic_id ), array( 'ID' => $content_id ) );
		}

		// Save course content order.
		$this->save_course_content_order();

		wp_send_json_success();
	}

	/**
	 * Restrict new student entry
	 *
	 * @since 1.0.0
	 * @param mixed $content content.
	 * @return mixed
	 */
	public function restrict_new_student_entry( $content ) {

		if ( ! tutor_utils()->is_course_fully_booked() ) {
			// No restriction if not fully booked.
			return $content;
		}

		return '<div class="list-item-booking booking-full tutor-d-flex tutor-align-center"><div class="booking-progress tutor-d-flex"><span class="tutor-mr-8 tutor-color-warning tutor-icon-circle-info"></span></div><div class="tutor-fs-7 tutor-fw-medium">' .
		__( 'Fully Booked', 'tutor' )
		. '</div></div>';
	}

	/**
	 * Restrict media
	 *
	 * @since 1.0.0
	 * @param string $where where clause.
	 * @return string
	 */
	public function restrict_media( $where ) {
		$action = Input::post( 'action' );
		if ( 'query-attachments' === $action && tutor_utils()->is_instructor() ) {
			if ( ! tutor_utils()->has_user_role( array( 'administrator', 'editor' ) ) ) {
				$where .= ' AND post_author=' . get_current_user_id();
			}
		}

		return $where;
	}

	/**
	 * Course meta box (Topics)
	 *
	 * @since 1.0.0
	 * @param boolean $echo display or not.
	 * @return string
	 */
	public function course_meta_box( $echo = true ) {
		$file_path = tutor()->path . 'views/metabox/course-topics.php';

		if ( $echo ) {
			/**
			 * Use echo raise WPCS security issue
			 * Helper wp_kses_post break content.
			 */
			include $file_path;
		} else {
			ob_start();
			include $file_path;
			return ob_get_clean();
		}
	}

	/**
	 * Save course content order
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function save_course_content_order() {
		global $wpdb;

		$new_order = Input::post( 'tutor_topics_lessons_sorting' );
		if ( ! empty( $new_order ) ) {
			$order = json_decode( $new_order, true );

			if ( is_array( $order ) && count( $order ) ) {
				$i = 0;
				foreach ( $order as $topic ) {
					$i++;
					$wpdb->update(
						$wpdb->posts,
						array( 'menu_order' => $i ),
						array( 'ID' => $topic['topic_id'] )
					);

					/**
					 * Removing All lesson with topic
					 */

					$wpdb->update(
						$wpdb->posts,
						array( 'post_parent' => 0 ),
						array( 'post_parent' => $topic['topic_id'] )
					);

					/**
					 * Lesson Attaching with topic ID
					 * Sorting lesson
					 */
					if ( isset( $topic['lesson_ids'] ) ) {
						$lesson_ids = $topic['lesson_ids'];
					} else {
						$lesson_ids = array();
					}
					if ( count( $lesson_ids ) ) {
						foreach ( $lesson_ids as $lesson_key => $lesson_id ) {
							$wpdb->update(
								$wpdb->posts,
								array(
									'post_parent' => $topic['topic_id'],
									'menu_order'  => $lesson_key,
								),
								array( 'ID' => $lesson_id )
							);
						}
					}
				}
			}
		}
	}

	/**
	 * Insert Topic and attached it with Course
	 *
	 * @since 1.0.0
	 *
	 * @param integer $post_ID post ID.
	 * @param object  $post post object.
	 *
	 * @return void
	 */
	public function save_course_meta( $post_ID, $post ) {
		global $wpdb;

		do_action( 'tutor_save_course', $post_ID, $post );

		/**
		 * Save course price type
		 */
		$price_type = Input::post( 'tutor_course_price_type' );
		if ( $price_type ) {
			update_post_meta( $post_ID, self::COURSE_PRICE_TYPE_META, $price_type );
		}

		//phpcs:disable WordPress.Security.NonceVerification.Missing
		// Course Duration.
		if ( ! empty( $_POST['course_duration'] ) ) {
			$video = Input::post( 'course_duration', array(), Input::TYPE_ARRAY );
			update_post_meta( $post_ID, '_course_duration', $video );
		}

		if ( ! empty( $_POST['_tutor_course_level'] ) ) {
			$course_level = Input::post( '_tutor_course_level' );
			update_post_meta( $post_ID, '_tutor_course_level', $course_level );
		}

		$additional_data_edit = Input::post( '_tutor_course_additional_data_edit' );
		if ( $additional_data_edit ) {
			if ( ! empty( $_POST['course_benefits'] ) ) {
				$course_benefits = Input::post( 'course_benefits', '', Input::TYPE_KSES_POST );
				update_post_meta( $post_ID, '_tutor_course_benefits', $course_benefits );
			} else {
				if ( ! tutor_is_rest() ) {
					delete_post_meta( $post_ID, '_tutor_course_benefits' );
				}
			}

			if ( ! empty( $_POST['course_requirements'] ) ) {
				$requirements = Input::post( 'course_requirements', '', Input::TYPE_KSES_POST );
				update_post_meta( $post_ID, '_tutor_course_requirements', $requirements );
			} else {
				if ( ! tutor_is_rest() ) {
					delete_post_meta( $post_ID, '_tutor_course_requirements' );
				}
			}

			if ( ! empty( $_POST['course_target_audience'] ) ) {
				$target_audience = Input::post( 'course_target_audience', '', Input::TYPE_KSES_POST );
				update_post_meta( $post_ID, '_tutor_course_target_audience', $target_audience );
			} else {
				if ( ! tutor_is_rest() ) {
					delete_post_meta( $post_ID, '_tutor_course_target_audience' );
				}
			}

			if ( ! empty( $_POST['course_material_includes'] ) ) {
				$material_includes = Input::post( 'course_material_includes', '', Input::TYPE_KSES_POST );
				update_post_meta( $post_ID, '_tutor_course_material_includes', $material_includes );
			} else {
				if ( ! tutor_is_rest() ) {
					delete_post_meta( $post_ID, '_tutor_course_material_includes' );
				}
			}
			//phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		/**
		 * Sorting Topics and lesson
		 */
		$this->save_course_content_order();

		// Additional data like course intro video.
		if ( $additional_data_edit ) {
			// Sanitize data through helper method.
			$video        = Input::sanitize_array(
				$_POST['video'] ?? array(), //phpcs:ignore
				array(
					'source_external_url' => 'esc_url',
					'source_embedded'     => 'wp_kses_post',
				),
				true
			);
			$video_source = tutor_utils()->array_get( 'source', $video );
			if ( -1 !== $video_source ) {
				update_post_meta( $post_ID, '_video', $video );
			} else {
				if ( ! tutor_is_rest() ) {
					delete_post_meta( $post_ID, '_video' );
				}
			}
		}

		/**
		 * Adding author to instructor automatically
		 */

		// Override post author id.
		$author_id = isset( $_POST['post_author_override'] ) ? $_POST['post_author_override'] : $post->post_author; //phpcs:ignore
		$attached  = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(umeta_id) FROM {$wpdb->usermeta}
					WHERE user_id = %d
						AND meta_key = '_tutor_instructor_course_id'
						AND meta_value = %d ",
				$author_id,
				$post_ID
			)
		);

		if ( ! $attached ) {
			add_user_meta( $author_id, '_tutor_instructor_course_id', $post_ID );
		}

		/**
		 * Disable question and answer for this course
		 *
		 * @since 1.7.0
		 */
		if ( $additional_data_edit ) {
			foreach ( $this->additional_meta as $key ) {
				//phpcs:ignore WordPress.Security.NonceVerification.Missing
				update_post_meta( $post_ID, $key, ( isset( $_POST[ $key ] ) ? 'yes' : 'no' ) );
			}
		}

		do_action( 'tutor_save_course_after', $post_ID, $post );
	}

	/**
	 * Save course topic
	 *
	 * @since 1.0.0
	 * @since 3.0.0 response and input name updated.
	 *
	 * @return void
	 */
	public function tutor_save_topic() {
		tutor_utils()->check_nonce();

		$is_update   = false;
		$errors      = array();
		$topic_title = Input::post( 'title' );

		if ( empty( $topic_title ) ) {
			$errors['topic_title'] = __( 'Topic title is required!', 'tutor' );
			$this->json_response(
				__( 'Invalid inputs' ),
				$errors,
				HttpHelper::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		// Gather parameters.
		$course_id     = Input::post( 'course_id', 0, Input::TYPE_INT );
		$topic_id      = Input::post( 'topic_id', 0, Input::TYPE_INT );
		$topic_summary = Input::post( 'summary', '', Input::TYPE_KSES_POST );

		$next_topic_order_id = tutor_utils()->get_next_topic_order_id( $course_id, $topic_id );

		// Validate if user can manage the topic.
		if ( ! tutor_utils()->can_user_manage( 'course', $course_id ) || ( $topic_id && ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		// Create payload to create/update the topic.
		$post_arr = array(
			'post_type'    => 'topics',
			'post_title'   => $topic_title,
			'post_content' => $topic_summary,
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_parent'  => $course_id,
			'menu_order'   => $next_topic_order_id,
		);

		if ( $topic_id ) {
			$is_update      = true;
			$post_arr['ID'] = $topic_id;
		}

		$current_topic_id = wp_insert_post( $post_arr );

		if ( $is_update ) {
			$this->json_response(
				__( 'Topic updated successfully!', 'tutor' ),
				$current_topic_id
			);
		} else {
			$this->json_response(
				__( 'Topic created successfully!', 'tutor' ),
				$current_topic_id,
				HttpHelper::STATUS_CREATED
			);
		}
	}

	/**
	 * Delete a course topic
	 *
	 * @since 1.0.0
	 * @since 3.0.0 code refactor and response updated.
	 *
	 * @return void
	 */
	public function tutor_delete_topic() {
		tutor_utils()->check_nonce();

		$topic_id = Input::post( 'topic_id', 0, Input::TYPE_INT );
		if ( ! $topic_id || ! is_numeric( $topic_id ) || ! tutor_utils()->can_user_manage( 'topic', $topic_id ) ) {
			$this->json_response(
				tutor_utils()->error_message(),
				null,
				HttpHelper::STATUS_FORBIDDEN
			);
		}

		global $wpdb;

		// Assign course ID to orphan content IDs since the topic will be deleted.
		$course_id   = tutor_utils()->get_course_id_by( 'topic', $topic_id );
		$content_ids = tutor_utils()->get_course_content_ids_by( null, 'topic', $topic_id );
		foreach ( $content_ids as $content_id ) {
			update_post_meta( $content_id, '_tutor_course_id_for_lesson', $course_id );
			// Actually all kind of contents.
			// This keyword '_tutor_course_id_for_lesson' used just to support backward compatibility.
		}

		// Set contents under the topic orphan.
		$wpdb->update( $wpdb->posts, array( 'post_parent' => 0 ), array( 'post_parent' => $topic_id ) );

		// Then delete the topic from database.
		$wpdb->delete( $wpdb->postmeta, array( 'post_id' => $topic_id ) );
		wp_delete_post( $topic_id );

		$this->json_response(
			__( 'Topic deleted successfully!', 'tutor' )
		);
	}

	/**
	 * Handle enroll now action
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enroll_now() {

		if ( '_tutor_course_enroll_now' !== Input::post( 'tutor_course_action' ) || ! Input::has( 'tutor_course_id' ) ) {
			return;
		}

		// Checking Nonce.
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			exit( esc_html__( 'Please Sign In first', 'tutor' ) );
		}

		$course_id = Input::post( 'tutor_course_id', 0, Input::TYPE_INT );

		/**
		 * TODO: need to check purchase information
		 */

		$is_purchasable = tutor_utils()->is_course_purchasable( $course_id );

		/**
		 * If is is not purchasable, it's free, and enroll right now
		 * If purchasable, then process purchase.
		 *
		 * @since: v.1.0.0
		 */
		if ( $is_purchasable ) { //phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
			// Process purchase.

		} else {
			// Free enroll.
			tutor_utils()->do_enroll( $course_id );
		}

		$referer_url = wp_get_referer();
		wp_safe_redirect( tutor_utils()->get_nocache_url( $referer_url ) );
		exit;
	}

	/**
	 * Mark complete completed
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function mark_course_complete() {
		$tutor_action = Input::post( 'tutor_action' );
		$course_id    = Input::post( 'course_id', 0, Input::TYPE_INT );
		if ( 'tutor_complete_course' !== $tutor_action || ! $course_id ) {
			return;
		}

		// Checking nonce.
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();

		// TODO: need to show view if not signed_in.
		if ( ! $user_id ) {
			die( esc_html__( 'Please Sign-In', 'tutor' ) );
		}

		CourseModel::mark_course_as_completed( $course_id, $user_id );

		$permalink = get_the_permalink( $course_id );

		// Set temporary identifier to show review pop up.
		self::set_review_popup_data( $user_id, $course_id, $permalink );

		wp_safe_redirect( $permalink );
		exit;
	}

	/**
	 * Set data for review popup.
	 *
	 * @since 2.2.5
	 * @since 2.4.0 removed $permalink param. store user meta instead of option data.
	 *
	 * @param int $user_id user id.
	 * @param int $course_id course id.
	 *
	 * @return void
	 */
	public static function set_review_popup_data( $user_id, $course_id ) {
		if ( get_tutor_option( 'enable_course_review' ) ) {
			$rating = tutor_utils()->get_course_rating_by_user( $course_id, $user_id );
			if ( ! $rating || ( empty( $rating->rating ) && empty( $rating->review ) ) ) {
				$meta_key = User::get_review_popup_meta( $course_id );
				add_user_meta( $user_id, $meta_key, $course_id, true );
			}
		}
	}

	/**
	 * Popup review form on course details
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function popup_review_form() {
		if ( is_user_logged_in() ) {
			$user_id          = get_current_user_id();
			$course_id        = get_the_ID();
			$meta_key         = User::get_review_popup_meta( $course_id );
			$review_course_id = (int) get_user_meta( $user_id, $meta_key, true );

			if ( is_single() && $course_id === $review_course_id ) {
				include tutor()->path . 'views/modal/review.php';
			}
		}
	}

	/**
	 * Review popup data clear
	 *
	 * @since 2.4.0
	 *
	 * @return void
	 */
	public function clear_review_popup_data() {
		tutils()->checking_nonce();

		if ( is_user_logged_in() ) {
			$user_id   = get_current_user_id();
			$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );

			if ( $course_id ) {
				$meta_key = User::get_review_popup_meta( $course_id );
				delete_user_meta( $user_id, $meta_key, $course_id );
			}

			wp_send_json_success();
		}
	}

	/**
	 * Delete course delete from frontend dashboard
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function tutor_delete_dashboard_course() {
		tutor_utils()->checking_nonce();

		$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );
		if ( ! tutor_utils()->can_user_manage( 'course', $course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		/**
		 * Co-instructor can not delete a course
		 *
		 * @since 2.1.6
		 */
		if ( false === CourseModel::is_main_instructor( $course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Only main instructor can delete this course', 'tutor' ) ) );
		}

		// Check if user is only an instructor.
		if ( ! current_user_can( 'administrator' ) ) {
			// Check if instructor can trash course.
			$can_trash_post = tutor_utils()->get_option( 'instructor_can_delete_course' );

			if ( ! $can_trash_post ) {
				wp_send_json_error( tutor_utils()->error_message() );
			}
		}

		$trash_course = wp_update_post(
			array(
				'ID'          => $course_id,
				'post_status' => 'trash',
			)
		);

		if ( $trash_course ) {
			wp_send_json_success( __( 'Course has been trashed successfully ', 'tutor' ) );
		}
		wp_send_json_success();
	}

	/**
	 * Main author change from gutenberg editor
	 *
	 * @since 2.0.0
	 *
	 * @param array $data data.
	 * @param array $postarr post array.
	 *
	 * @return mixed
	 */
	public function tutor_add_gutenberg_author( $data, $postarr ) {
		$gutenberg_enabled = tutor_utils()->get_option( 'enable_gutenberg_course_edit' );
		$post_type         = $postarr['post_type'];
		$courses_post_type = tutor()->course_post_type;

		if ( false === is_admin() || false === $gutenberg_enabled || $post_type !== $courses_post_type ) {
			return $data;
		}

		/**
		 * Only admin can change main author
		 */
		if ( $courses_post_type === $post_type && ! current_user_can( 'administrator' ) ) {
			global $wpdb;
			$post_ID     = (int) tutor_utils()->avalue_dot( 'ID', $postarr );
			$post_author = (int) $wpdb->get_var( $wpdb->prepare( "SELECT post_author FROM {$wpdb->posts} WHERE ID = %d ", $post_ID ) );

			if ( $post_author > 0 ) {
				$data['post_author'] = $post_author;
			} else {
				$data['post_author'] = get_current_user_id();
			}
		}

		return $data;
	}


	/**
	 * Attach product with course when course save from frontend or backend.
	 *
	 * @since 1.3.4
	 *
	 * @since 3.0.0 Store regular & sale price in meta to make compatible with Tutor monetization
	 *
	 * @param integer $post_ID  course ID.
	 * @param array   $post_data created course post details.
	 *
	 * @return void
	 */
	public function attach_product_with_course( $post_ID, $post_data ) {

		$monetize_by = tutor_utils()->get_option( 'monetize_by' );
		$product_id  = Input::post( '_tutor_course_product_id', 0, Input::TYPE_INT );

		if ( Ecommerce::MONETIZE_BY === $monetize_by ) {
			return;
		}

		/**
		 * Unlink product from course.
		 */
		if ( -1 === $product_id ) {
			delete_post_meta( $post_ID, self::COURSE_PRODUCT_ID_META );
			return;
		}

		/**
		 * Free user can only select product from dropdown
		 */
		if ( tutor()->has_pro === false && 'wc' === $monetize_by ) {
			if ( $product_id > 0 ) {
				update_post_meta( $post_ID, self::COURSE_PRODUCT_ID_META, $product_id );
			}

			return;
		}

		$attached_product_id = tutor_utils()->get_course_product_id( $post_ID );
		$course_price        = Input::post( 'course_price', 0, Input::TYPE_NUMERIC );
		$sale_price          = Input::post( 'course_sale_price', 0, Input::TYPE_NUMERIC );

		if ( ! $course_price || $sale_price >= $course_price ) {
			return;
		}

		$course = get_post( $post_ID );

		update_post_meta( $post_ID, self::COURSE_PRICE_TYPE_META, self::PRICE_TYPE_PAID );

		if ( 'wc' === $monetize_by ) {

			$is_update = ( $attached_product_id && wc_get_product( $attached_product_id ) ) ? true : false;

			if ( $is_update ) {
				$attached_product_id = $product_id;
				update_post_meta( $post_ID, self::COURSE_PRODUCT_ID_META, $product_id );

				$product_id  = self::create_wc_product( $course->post_title, $course_price, $sale_price, $attached_product_id );
				$product_obj = wc_get_product( $product_id );
				if ( $product_obj->is_type( 'subscription' ) ) {
					update_post_meta( $attached_product_id, '_subscription_price', $course_price );
				}

				// Set course regular & sale price.
				update_post_meta( $post_ID, self::COURSE_PRICE_META, $product_obj->get_regular_price() );
				update_post_meta( $post_ID, self::COURSE_SALE_PRICE_META, $product_obj->get_sale_price() );
			} else {
				// Create new WC product name with course title.
				$product_id = self::create_wc_product( $course->post_title, $course_price, $sale_price );
				if ( $product_id ) {
					$product_obj = wc_get_product( $product_id );
					update_post_meta( $post_ID, self::COURSE_PRODUCT_ID_META, $product_id );
					// Mark product for woocommerce.
					update_post_meta( $product_id, '_virtual', 'yes' );
					update_post_meta( $product_id, '_tutor_product', 'yes' );

					$course_post_thumbnail = get_post_meta( $post_ID, '_thumbnail_id', true );
					if ( $course_post_thumbnail ) {
						set_post_thumbnail( $product_id, $course_post_thumbnail );
					}

					// Set course regular & sale price.
					update_post_meta( $post_ID, self::COURSE_PRICE_META, $product_obj->get_regular_price() );
					update_post_meta( $post_ID, self::COURSE_SALE_PRICE_META, $product_obj->get_sale_price() );
				}
			}
		} elseif ( 'edd' === $monetize_by ) {

			$is_update = false;

			if ( $attached_product_id ) {
				$edd_price = get_post_meta( $attached_product_id, 'edd_price', true );
				if ( $edd_price ) {
					$is_update = true;
				}
			}

			if ( $is_update ) {
				// Update the product.
				update_post_meta( $attached_product_id, 'edd_price', $course_price );
			} else {
				// Create new product.

				$post_arr    = array(
					'post_type'   => 'download',
					'post_title'  => $course->post_title,
					'post_status' => 'publish',
					'post_author' => get_current_user_id(),
				);
				$download_id = wp_insert_post( $post_arr );
				if ( $download_id ) {
					// EDD edd_price.
					update_post_meta( $download_id, 'edd_price', $course_price );

					update_post_meta( $post_ID, self::COURSE_PRODUCT_ID_META, $download_id );
					// Mark product for EDD.
					update_post_meta( $download_id, '_tutor_product', 'yes' );

					$course_post_thumbnail = get_post_meta( $post_ID, '_thumbnail_id', true );
					if ( $course_post_thumbnail ) {
						set_post_thumbnail( $download_id, $course_post_thumbnail );
					}
				}
			}
		}
	}

	/**
	 * Add Course level to course settings
	 *
	 * @since 1.4.1
	 *
	 * @param array $args arguments.
	 * @return array
	 */
	public function add_course_level_to_settings( $args ) {
		$course_id    = get_the_ID();
		$levels       = tutor_utils()->course_levels();
		$course_level = get_post_meta( $course_id, '_tutor_course_level', true );

		$args['general']['fields']['_tutor_course_level'] = array(
			'type'        => 'select',
			'label'       => __( 'Difficulty Level', 'tutor' ),
			'label_title' => __( 'Enable', 'tutor' ),
			'options'     => $levels,
			'value'       => $course_level ? $course_level : 'intermediate',
			'desc'        => __( 'Course difficulty level', 'tutor' ),
		);

		return $args;
	}

	/**
	 * Check if course starting
	 *
	 * @since 1.4.8
	 * @return void
	 */
	public function tutor_lesson_load_before() {
		$course_id         = tutor_utils()->get_course_id_by_content( get_the_ID() );
		$completed_lessons = tutor_utils()->get_completed_lesson_count_by_course( $course_id );
		if ( is_user_logged_in() ) {
			$is_course_started = get_post_meta( $course_id, '_tutor_course_started', true );
			if ( ! $completed_lessons && ! $is_course_started ) {
				update_post_meta( $course_id, '_tutor_course_started', tutor_time() );
				do_action( 'tutor/course/started', $course_id );
			}
		}
	}

	/**
	 * Add Course level to course settings
	 *
	 * @since 1.4.8
	 * @return void
	 */
	public function course_elements_enable_disable() {
		add_filter( 'tutor_course/single/completing-progress-bar', array( $this, 'enable_disable_course_progress_bar' ) );
		add_filter( 'tutor_course/single/material_includes', array( $this, 'enable_disable_material_includes' ) );
		add_filter( 'tutor_course/single/content', array( $this, 'enable_disable_course_content' ) );
		add_filter( 'tutor_course/single/benefits_html', array( $this, 'enable_disable_course_benefits' ) );
		add_filter( 'tutor_course/single/requirements_html', array( $this, 'enable_disable_course_requirements' ) );
		add_filter( 'tutor_course/single/audience_html', array( $this, 'enable_disable_course_target_audience' ) );
		add_filter( 'tutor_course/single/nav_items', array( $this, 'enable_disable_course_nav_items' ), 999, 2 );
	}

	/**
	 * Enable disable course progress bar
	 *
	 * @since 1.4.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function enable_disable_course_progress_bar( $html ) {
		$disable_option = ! (bool) tutor_utils()->get_option( 'enable_course_progress_bar', true, true );
		if ( $disable_option ) {
			return '';
		}
		return $html;
	}

	/**
	 * Enable disable material includes
	 *
	 * @since 1.4.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function enable_disable_material_includes( $html ) {
		$disable_option = ! (bool) get_tutor_option( 'enable_course_material', true, true );
		if ( $disable_option ) {
			return '';
		}
		return $html;
	}

	/**
	 * Enable disable course content
	 *
	 * @since 1.4.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function enable_disable_course_content( $html ) {
		$disable_option = ! (bool) tutor_utils()->get_option( 'enable_course_description', true, true );
		if ( $disable_option ) {
			return '';
		}
		return $html;
	}

	/**
	 * Enable disable course benefits
	 *
	 * @since 1.4.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function enable_disable_course_benefits( $html ) {
		$disable_option = ! (bool) tutor_utils()->get_option( 'enable_course_benefits', true, true );
		if ( $disable_option ) {
			return '';
		}
		return $html;
	}

	/**
	 * Enable disable course requirements
	 *
	 * @since 1.4.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function enable_disable_course_requirements( $html ) {
		$disable_option = ! (bool) tutor_utils()->get_option( 'enable_course_requirements', true, true );
		if ( $disable_option ) {
			return '';
		}
		return $html;
	}

	/**
	 * Enable disable course target audience
	 *
	 * @since 1.4.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function enable_disable_course_target_audience( $html ) {
		$disable_option = ! (bool) tutor_utils()->get_option( 'enable_course_target_audience', true, true );
		if ( $disable_option ) {
			return '';
		}
		return $html;
	}

	/**
	 * Enable disable course nav items
	 *
	 * @since 1.4.8
	 *
	 * @param array   $items item list.
	 * @param integer $course_id course ID.
	 *
	 * @return array
	 */
	public function enable_disable_course_nav_items( $items, $course_id ) {
		global $wp_query, $post;
		$enable_q_and_a_on_course     = (bool) get_tutor_option( 'enable_q_and_a_on_course' );
		$disable_course_announcements = ! (bool) tutor_utils()->get_option( 'enable_course_announcements', true, true );
		$disable_qa_for_this_course   = ( $wp_query->is_single && ! empty( $post ) ) ? get_post_meta( $post->ID, '_tutor_enable_qa', true ) != 'yes' : false;

		// Whether Q&A enabled.
		if ( ! $enable_q_and_a_on_course || $disable_qa_for_this_course ) {
			if ( tutor_utils()->array_get( 'questions', $items ) ) {
				unset( $items['questions'] );
			}
		}

		// Whether announcment enabled.
		if ( $disable_course_announcements ) {
			if ( tutor_utils()->array_get( 'announcements', $items ) ) {
				unset( $items['announcements'] );
			}
		}

		// Hide review section if disabled.
		if ( ! get_tutor_option( 'enable_course_review' ) ) {
			unset( $items['reviews'] );
		}

		// Whether enrollment require.
		$is_enrolled = tutor_utils()->is_enrolled();

		return array_filter(
			$items,
			function ( $item ) use ( $is_enrolled ) {
				if ( isset( $item['require_enrolment'] ) && $item['require_enrolment'] ) {
					return $is_enrolled;
				}
				return true;
			}
		);
	}

	/**
	 * Filter product in shop page
	 *
	 * @since 1.4.9
	 * @return void|null
	 */
	public function filter_product_in_shop_page() {
		$hide_course_from_shop_page = (bool) get_tutor_option( 'hide_course_from_shop_page' );
		if ( ! $hide_course_from_shop_page ) {
			return;
		}
		add_action( 'woocommerce_product_query', array( $this, 'filter_woocommerce_product_query' ) );
		add_filter( 'edd_downloads_query', array( $this, 'filter_edd_downloads_query' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'filter_archive_meta_query' ), 1 );
	}


	/**
	 * Tutor product meta query
	 *
	 * @since 1.4.9
	 * @return array
	 */
	public function tutor_product_meta_query() {
		$meta_query = array(
			'key'     => '_tutor_product',
			'compare' => 'NOT EXISTS',
		);
		return $meta_query;
	}

	/**
	 * Filter product in woocommerce shop page
	 *
	 * @since 1.4.9
	 *
	 * @param \WP_Query $wp_query WP Query instance.
	 * @return \WP_Query
	 */
	public function filter_woocommerce_product_query( $wp_query ) {
		$product_ids = $this->get_connected_wc_product_ids();
		$wp_query->set( 'post__not_in', $product_ids );
		return $wp_query;
	}

	/**
	 * Get connected woocommerce product ids for course and course bundle
	 *
	 * @since 2.7.2
	 *
	 * @return array
	 */
	public function get_connected_wc_product_ids() {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT pm.meta_value product_id
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
				AND pm.meta_key = %s
				WHERE post_type IN( 'courses','course-bundle' )",
				'_tutor_course_product_id'
			)
		);

		$ids = array();
		if ( is_array( $results ) && count( $results ) ) {
			$ids = array_column( $results, 'product_id' );
		}

		return $ids;
	}

	/**
	 * Filter product in edd downloads shortcode page
	 *
	 * @since 1.4.9
	 *
	 * @param \WP_Query $query WP Query instance.
	 * @return \WP_Query
	 */
	public function filter_edd_downloads_query( $query ) {
		$query['meta_query'][] = $this->tutor_product_meta_query();
		return $query;
	}

	/**
	 * Filter product in edd downloads archive page
	 *
	 * @since 1.4.9
	 *
	 * @param \WP_Query $wp_query WP Query instance.
	 * @return \WP_Query
	 */
	public function filter_archive_meta_query( $wp_query ) {
		if ( ! is_admin() && $wp_query->is_archive && $wp_query->get( 'post_type' ) === 'download' ) {
			$wp_query->set( 'meta_query', array( $this->tutor_product_meta_query() ) );
		}
		return $wp_query;
	}

	/**
	 * Removed course price if already enrolled at single course
	 *
	 * @since 1.5.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function remove_price_if_enrolled( $html ) {
		$should_removed = apply_filters( 'should_remove_price_if_enrolled', true );

		if ( $should_removed ) {
			$course_id = get_the_ID();
			$enrolled  = tutor_utils()->is_enrolled( $course_id );
			if ( $enrolled ) {
				$html = '';
			}
		}
		return $html;
	}

	/**
	 * Check if all lessons and quizzes done before mark course complete.
	 *
	 * @since 1.5.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function tutor_lms_hide_course_complete_btn( $html ) {

		$completion_mode = tutor_utils()->get_option( 'course_completion_process' );
		if ( 'strict' !== $completion_mode ) {
			return $html;
		}

		$completed_lesson = tutor_utils()->get_completed_lesson_count_by_course();
		$lesson_count     = tutor_utils()->get_lesson_count_by_course();

		if ( $completed_lesson < $lesson_count ) {
			return '<div class="tutor-alert tutor-warning tutor-mt-28">
						<div class="tutor-alert-text">
							<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-info tutor-mr-12"></span>
							<span>' . __( 'Complete all lessons to mark this course as complete', 'tutor' ) . '</span>
						</div>
					</div>';
		}

		$quizzes     = array();
		$assignments = array();

		$course_contents = tutor_utils()->get_course_contents_by_id();
		if ( tutor_utils()->count( $course_contents ) ) {
			foreach ( $course_contents as $content ) {
				if ( 'tutor_quiz' === $content->post_type ) {
					$quizzes[] = $content;
				}
				if ( 'tutor_assignments' === $content->post_type ) {
					$assignments[] = $content;
				}
			}
		}

		$required_assignment_pass = 0;

		foreach ( $assignments as $row ) {

			$submitted_assignment      = tutor_utils()->is_assignment_submitted( $row->ID );
			$is_reviewed_by_instructor = null === $submitted_assignment
											? false
											: get_comment_meta( $submitted_assignment->comment_ID, 'evaluate_time', true );

			if ( $submitted_assignment && $is_reviewed_by_instructor ) {
				$pass_mark  = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'pass_mark' );
				$given_mark = get_comment_meta( $submitted_assignment->comment_ID, 'assignment_mark', true );

				if ( $given_mark < $pass_mark ) {
					$required_assignment_pass++;
				}
			} else {
				$required_assignment_pass++;
			}
		}

		$is_quiz_pass       = true;
		$required_quiz_pass = 0;

		if ( tutor_utils()->count( $quizzes ) ) {
			foreach ( $quizzes as $quiz ) {

				$attempt = tutor_utils()->get_quiz_attempt( $quiz->ID );
				if ( $attempt ) {
					$passing_grade     = tutor_utils()->get_quiz_option( $quiz->ID, 'passing_grade', 0 );
					$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;

					if ( $earned_percentage < $passing_grade ) {
						$required_quiz_pass++;
						$is_quiz_pass = false;
					}
				} else {
					$required_quiz_pass++;
					$is_quiz_pass = false;
				}
			}
		}

		if ( ! $is_quiz_pass || $required_assignment_pass > 0 ) {
			$_msg           = '';
			$quiz_str       = _n( 'quiz', 'quizzes', $required_quiz_pass, 'tutor' );
			$assignment_str = _n( 'assignment', 'assignments', $required_assignment_pass, 'tutor' );

			if ( ! $is_quiz_pass && 0 == $required_assignment_pass ) {
				/* translators: %1$s: number of quiz/assignment pass required; %2$s: quiz/assignment string */
				$_msg = sprintf( __( 'You have to pass %1$s %2$s to complete this course.', 'tutor' ), $required_quiz_pass, $quiz_str );
			}

			if ( $is_quiz_pass && $required_assignment_pass > 0 ) {
				//phpcs:ignore
				$_msg = sprintf( __( 'You have to pass %1$s %2$s to complete this course.', 'tutor' ), $required_assignment_pass, $assignment_str );
			}

			if ( ! $is_quiz_pass && $required_assignment_pass > 0 ) {
				/* translators: %1$s: number of quiz pass required; %2$s: quiz string; %3$s: number of assignment pass required; %4$s: assignment string */
				$_msg = sprintf( __( 'You have to pass %1$s %2$s and %3$s %4$s to complete this course.', 'tutor' ), $required_quiz_pass, $quiz_str, $required_assignment_pass, $assignment_str );
			}

			return '<div class="tutor-alert tutor-warning tutor-mt-28">
						<div class="tutor-alert-text">
							<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-info tutor-mr-12"></span>
							<span>' . $_msg . '</span>
						</div>
					</div>';
		}

		return $html;
	}

	/**
	 * Generate Gradebook
	 *
	 * @since 1.5.8
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	public function get_generate_greadbook( $html ) {
		if ( ! tutor_utils()->is_completed_course() ) {
			return '';
		}
		return $html;
	}

	/**
	 * Add social share content in header
	 *
	 * @since 1.6.3
	 * @return void
	 */
	public function social_share_content() {
		global $wp_query, $post;
		if ( $wp_query->is_single && ! empty( $wp_query->query_vars['post_type'] ) && $wp_query->query_vars['post_type'] === $this->course_post_type ) { ?>
			<!--Facebook-->
			<meta property="og:type" content="website"/>
			<meta property="og:image" content="<?php echo esc_url( get_tutor_course_thumbnail_src() ); ?>" />
			<meta property="og:description" content="<?php echo esc_html( $post->post_content ); ?>" />
			<!--Twitter-->
			<meta name="twitter:image" content="<?php echo esc_url( get_tutor_course_thumbnail_src() ); ?>">
			<meta name="twitter:description" content="<?php echo esc_html( $post->post_content ); ?>">
			<!--Google+-->
			<meta itemprop="image" content="<?php echo esc_url( get_tutor_course_thumbnail_src() ); ?>">
			<meta itemprop="description" content="<?php echo esc_html( $post->post_content ); ?>">
			<?php
		}
	}

	/**
	 * Delete associated enrollment
	 *
	 * @since 1.8.2
	 *
	 * @param integer $post_id post ID.
	 * @return void
	 */
	public function delete_associated_enrollment( $post_id ) {
		global $wpdb;

		$enroll_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT
				post_id
			FROM
				{$wpdb->postmeta}
			WHERE
				meta_key='_tutor_enrolled_by_order_id'
				AND meta_value = %d
			",
				$post_id
			)
		);

		if ( is_numeric( $enroll_id ) && $enroll_id > 0 ) {

			$course_id = get_post_field( 'post_parent', $enroll_id );
			$user_id   = get_post_field( 'post_author', $enroll_id );

			tutor_utils()->cancel_course_enrol( $course_id, $user_id );
		}
	}

	/**
	 * Reset course progress.
	 *
	 * @since 1.5.8
	 * @return void
	 */
	public function tutor_reset_course_progress() {
		tutor_utils()->checking_nonce();
		$course_id = Input::post( 'course_id' );

		if ( ! $course_id || ! is_numeric( $course_id ) || ! tutor_utils()->is_enrolled( $course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid Course ID or Access Denied.', 'tutor' ) ) );
			return;
		}

		tutor_utils()->delete_course_progress( $course_id );
		wp_send_json_success( array( 'redirect_to' => tutor_utils()->get_course_first_lesson( $course_id ) ) );
	}

	/**
	 * Do enroll if guest attempt to enroll and course is free
	 *
	 * @since 1.9.8
	 *
	 * @param integer $course_id course ID.
	 * @param integer $user_id user ID.

	 * @return void
	 */
	public function enroll_after_login_if_attempt( int $course_id, int $user_id ) {
		$course_id = sanitize_text_field( $course_id );
		if ( $course_id ) {
			$is_purchasable = tutor_utils()->is_course_purchasable( $course_id );
			if ( ! $is_purchasable ) {
				tutor_utils()->do_enroll( $course_id, $order_id = 0, $user_id );
				do_action( 'guest_attempt_after_enrollment', $course_id );
			}
		}
	}

	/**
	 * Handle course enrollment
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function course_enrollment() {
		tutor_utils()->checking_nonce();

		$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );
		$user_id   = get_current_user_id();

		if ( $course_id ) {
			$enroll = tutor_utils()->do_enroll( $course_id, 0, $user_id );
			if ( $enroll ) {
				wp_send_json_success( __( 'Enrollment successfully done!', 'tutor' ) );
			} else {
				wp_send_json_error( __( 'Enrollment failed, please try again!', 'tutor' ) );
			}
		} else {
			wp_send_json_error( __( 'Invalid course ID', 'tutor' ) );
		}
	}

	/**
	 * After trash a course direct to the course list page
	 *
	 * @since 2.1.7
	 *
	 * @param integer $post_id int course id.
	 *
	 * @return void
	 */
	public static function redirect_to_course_list_page( int $post_id ): void {
		$post = get_post( $post_id );
		if ( tutor()->course_post_type === $post->post_type ) {
			$is_gutenberg_enabled = tutor_utils()->get_option( 'enable_gutenberg_course_edit' );
			if ( ! $is_gutenberg_enabled ) {
				wp_safe_redirect( admin_url( 'admin.php?page=tutor' ) );
				exit;
			}
		}
	}

	/**
	 * Create or update WooCommerce product
	 *
	 * If product id not set it will create new one.
	 *
	 * @since 2.2.0
	 *
	 * @param string $title product title.
	 * @param string $reg_price product price.
	 * @param string $sale_price product sale price.
	 * @param int    $product_id product ID.
	 * @param string $status product status.
	 *
	 * @return integer Product id or return 0 if WC not exists
	 */
	public static function create_wc_product( $title, $reg_price, $sale_price, $product_id = 0, $status = 'publish' ) {
		if ( ! tutor_utils()->has_wc() ) {
			return 0;
		}

		$product_obj = new \WC_Product();
		if ( $product_id ) {
			$product_obj = wc_get_product( $product_id );
		}

		$product_obj->set_name( $title );
		$product_obj->set_status( $status );
		$product_obj->set_price( $reg_price );
		$product_obj->set_regular_price( $reg_price );

		if ( $sale_price > 0 ) {
			$product_obj->set_sale_price( $sale_price );
		} else {
			$product_obj->set_sale_price( null );
		}

		$product_obj->set_sold_individually( true );

		return $product_obj->save();
	}

	/**
	 * Load media scripts
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function load_media_scripts() {
		// Add style on the head tag.
		$screen_reader_text_style = '
			.screen-reader-text
			{
				position: absolute;
				top: -10000em;
				width: 1px;
				height: 1px;
				margin: -1px;
				padding: 0;
				overflow: hidden;
				clip: rect(0,0,0,0);
				border: 0;
			}
		';

		wp_add_inline_style(
			'media-views',
			$screen_reader_text_style
		);

		add_action(
			'wp_print_footer_scripts',
			function () {
				if ( function_exists( 'wp_print_media_templates' ) ) {
					wp_print_media_templates();
				}
			}
		);
	}

	/**
	 * Get course/bundle mini info
	 *
	 * @since 3.0.0
	 *
	 * @param object $post Course or bundle post.
	 *
	 * @return array
	 */
	public static function get_mini_info( object $post ) {
		$is_purchasable = tutor_utils()->is_course_purchasable( $post->ID );
		$course_price   = tutor_utils()->get_raw_course_price( $post->ID );
		$regular_price  = tutor_get_formatted_price( $course_price->regular_price );
		$sale_price     = ! empty( $course_price->sale_price ) ? tutor_get_formatted_price( $course_price->sale_price ) : null;

		$info = array(
			'id'             => $post->ID,
			'title'          => $post->post_title,
			'image'          => get_tutor_course_thumbnail_src( 'post-thumbnail', $post->ID ),
			'is_purchasable' => $is_purchasable,
			'regular_price'  => $regular_price,
			'sale_price'     => $sale_price,
		);

		if ( 'course-bundle' === $post->post_type && tutor_utils()->is_addon_enabled( 'tutor-pro/addons/course-bundle/course-bundle.php' ) ) {
			$info['total_course'] = count( BundleModel::get_bundle_course_ids( $post->ID ) );
		}

		$card_data = apply_filters( 'tutor_add_course_plan_info', $info, $post );

		return $card_data;
	}

	/**
	 * Get course/bundle card data
	 *
	 * This method will return all data that contain in
	 * course card
	 *
	 * @since 3.0.0
	 *
	 * @param object $post Course or bundle post.
	 *
	 * @return array
	 */
	public static function get_card_data( object $post ) {
		$info = self::get_mini_info( $post );

		$info['last_updated']    = tutor_i18n_get_formated_date( $post->post_modified_at );
		$info['course_duration'] = tutor_utils()->get_course_duration( $post->ID, false );
		$info['total_enrolled']  = tutor_utils()->count_enrolled_users_by_course( $post->ID );

		$card_data = apply_filters( 'tutor_add_course_plan_info', $info, $post );

		return $card_data;
	}

	/**
	 * Filter user list access for instructor
	 *
	 * @since 3.0.0
	 *
	 * @param bool $access access.
	 *
	 * @return bool
	 */
	public function user_list_access_for_instructor( $access ) {
		$is_instructor = User::is_instructor();
		return $access || $is_instructor;
	}

	/**
	 * Filter user list args for instructor
	 *
	 * @since 3.0.0
	 *
	 * @param array $args args.
	 *
	 * @return array
	 */
	public function user_list_args_for_instructor( $args ) {
		if ( User::is_instructor() ) {
			if ( isset( $args['fields'] ) && isset( $args['fields']['user_email'] ) ) {
				unset( $args['fields']['user_email'] );
			}
		}

		$filter = json_decode( wp_unslash( $_POST['filter'] ?? '{}' ) );//phpcs:ignore
		if ( isset( $filter->role ) && is_array( $filter->role ) ) {
			$args['role__in'] = array_map( 'sanitize_text_field', $filter->role );
		}

		return $args;
	}

}
