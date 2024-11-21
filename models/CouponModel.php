<?php
/**
 * Coupon Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Models;

use TUTOR\Course;
use Tutor\Ecommerce\Settings;
use Tutor\Ecommerce\Tax;
use Tutor\Helpers\QueryHelper;

/**
 * Coupon model class
 */
class CouponModel {

	/**
	 * Coupon status
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const STATUS_ACTIVE   = 'active';
	const STATUS_INACTIVE = 'inactive';
	const STATUS_TRASH    = 'trash';

	/**
	 * Coupon type
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const TYPE_CODE      = 'code';
	const TYPE_AUTOMATIC = 'automatic';

	/**
	 * Coupon applies to
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const APPLIES_TO_ALL_COURSES_AND_BUNDLES = 'all_courses_and_bundles';
	const APPLIES_TO_ALL_COURSES             = 'all_courses';
	const APPLIES_TO_ALL_BUNDLES             = 'all_bundles';
	const APPLIES_TO_SPECIFIC_COURSES        = 'specific_courses';
	const APPLIES_TO_SPECIFIC_BUNDLES        = 'specific_bundles';
	const APPLIES_TO_SPECIFIC_CATEGORY       = 'specific_category';

	/**
	 * Coupon purchase requirement
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const REQUIREMENT_NO_MINIMUM       = 'no_minimum';
	const REQUIREMENT_MINIMUM_PURCHASE = 'minimum_purchase';
	const REQUIREMENT_MINIMUM_QUANTITY = 'minimum_quantity';

	/**
	 * Discount type
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const DISCOUNT_TYPE_FLAT       = 'flat';
	const DISCOUNT_TYPE_PERCENTAGE = 'percentage';

	/**
	 * Coupon table name
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $table_name = 'tutor_coupons';

	/**
	 * Coupon usage table name
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $coupon_usage_table = 'tutor_coupon_usages';

	/**
	 * Coupon application table
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $coupon_applies_to_table = 'tutor_coupon_applications';

	/**
	 * Fillable fields
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'coupon_status',
		'coupon_type',
		'coupon_code',
		'coupon_title',
		'coupon_description',
		'discount_type',
		'discount_amount',
		'applies_to',
		'applies_to_items',
		'total_usage_limit',
		'per_user_usage_limit',
		'purchase_requirement',
		'purchase_requirement_value',
		'start_date_gmt',
		'expire_date_gmt',
	);

	/**
	 * Fillable fields
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private $required_fields = array(
		'coupon_status',
		'coupon_type',
		'coupon_title',
		'discount_type',
		'discount_amount',
		'applies_to',
		'start_date_gmt',
	);

	/**
	 * Resolve props & dependencies
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name              = $wpdb->prefix . $this->table_name;
		$this->coupon_usage_table      = $wpdb->prefix . $this->coupon_usage_table;
		$this->coupon_applies_to_table = $wpdb->prefix . $this->coupon_applies_to_table;
	}

	/**
	 * Get table name with wp prefix
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_table_name() {
		return $this->table_name;
	}

	/**
	 * Get fillable fields
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_fillable_fields() {
		return $this->fillable_fields;
	}

	/**
	 * Get required fields
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_required_fields() {
		return $this->required_fields;
	}

	/**
	 * Get all coupon statuses
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_coupon_status() {
		return array(
			self::STATUS_ACTIVE   => __( 'Active', 'tutor' ),
			self::STATUS_INACTIVE => __( 'Inactive', 'tutor' ),
			self::STATUS_TRASH    => __( 'Trash', 'tutor' ),
		);
	}

	/**
	 * Get all coupon applies to
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_coupon_applies_to() {
		return array(
			self::APPLIES_TO_ALL_COURSES_AND_BUNDLES => __( 'All courses and bundles', 'tutor' ),
			self::APPLIES_TO_ALL_COURSES             => __( 'All courses', 'tutor' ),
			self::APPLIES_TO_ALL_BUNDLES             => __( 'All bundles', 'tutor' ),
			self::APPLIES_TO_SPECIFIC_COURSES        => __( 'Specific courses', 'tutor' ),
			self::APPLIES_TO_SPECIFIC_BUNDLES        => __( 'Specific bundles', 'tutor' ),
			self::APPLIES_TO_SPECIFIC_CATEGORY       => __( 'Specific category', 'tutor' ),
		);
	}

	/**
	 * Get all coupon purchase requirements
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_coupon_purchase_requirements() {
		return array(
			self::REQUIREMENT_NO_MINIMUM       => __( 'no_minimum', 'tutor' ),
			self::REQUIREMENT_MINIMUM_PURCHASE => __( 'minimum_purchase', 'tutor' ),
			self::REQUIREMENT_MINIMUM_QUANTITY => __( 'minimum_quantity', 'tutor' ),
		);
	}

	/**
	 * Get all coupon types
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_coupon_type() {
		return array(
			self::TYPE_CODE      => __( 'Code', 'tutor' ),
			self::TYPE_AUTOMATIC => __( 'Automatic', 'tutor' ),
		);
	}

	/**
	 * Get searchable fields
	 *
	 * This method is intendant to use with get order list
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function get_searchable_fields() {
		return array(
			'id',
			'coupon_status',
			'coupon_code',
			'coupon_title',
		);
	}

	/**
	 * Create coupon using the data argument
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Array as per table column.
	 *
	 * @throws \Exception Database error if occur.
	 *
	 * @return int Coupon id or 0 if failed
	 */
	public function create_coupon( array $data ) {
		try {
			return QueryHelper::insert( $this->table_name, $data );
		} catch ( \Throwable $th ) {
			throw new \Exception( $th->getMessage() );
		}
	}

	/**
	 * Insert applies to
	 *
	 * @since 3.0.0
	 *
	 * @param string $applies_to Applies to type.
	 * @param array  $applies_to_ids Applies to ids.
	 * @param mixed  $coupon_code Coupon code.
	 *
	 * @return mixed true|false on insert, void if not insert-able
	 */
	public function insert_applies_to( string $applies_to, array $applies_to_ids, $coupon_code ) {
		$specific_applies = array( self::APPLIES_TO_SPECIFIC_BUNDLES, self::APPLIES_TO_SPECIFIC_COURSES, self::APPLIES_TO_SPECIFIC_CATEGORY );
		if ( in_array( $applies_to, $specific_applies ) ) {
			$data = array();

			foreach ( $applies_to_ids as $id ) {
				$data[] = array(
					'coupon_code'  => $coupon_code,
					'reference_id' => $id,
				);
			}

			if ( count( $data ) ) {
				return QueryHelper::insert_multiple_rows( $this->coupon_applies_to_table, $data );
			}
		}
	}

	/**
	 * Delete applies to
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $coupon_code Coupon code.
	 *
	 * @return bool
	 */
	public function delete_applies_to( $coupon_code ) {
		return QueryHelper::delete( $this->coupon_applies_to_table, array( 'coupon_code' => $coupon_code ) );
	}

	/**
	 * Get coupons list
	 *
	 * @since 3.0.0
	 *
	 * @param array  $where where clause conditions.
	 * @param string $search_term search clause conditions.
	 * @param int    $limit limit default 10.
	 * @param int    $offset default 0.
	 * @param string $order_by column default 'o.id'.
	 * @param string $order list Coupon default 'desc'.
	 *
	 * @return array
	 */
	public function get_coupons( array $where = array(), $search_term = '', int $limit = 10, int $offset = 0, string $order_by = 'id', string $order = 'desc' ) {

		$search_clause = array();
		if ( '' !== $search_term ) {
			foreach ( $this->get_searchable_fields() as $column ) {
				$search_clause[ $column ] = $search_term;
			}
		}

		$response = array(
			'results'     => array(),
			'total_count' => 0,
		);

		try {
			$response = QueryHelper::get_all_with_search( $this->table_name, $where, $search_clause, $order_by, $limit, $offset, $order );

			// Add coupon usage count.
			foreach ( $response['results'] as $result ) {
				$result->usage_count = $this->get_coupon_usage_count( $result->coupon_code );
			}

			return $response;
		} catch ( \Throwable $th ) {
			// Log with error, line & file name.
			error_log( $th->getMessage() . ' in ' . $th->getFile() . ' at line ' . $th->getLine() );
			return $response;
		}
	}

	/**
	 * Update coupon
	 *
	 * @since 3.0.0
	 *
	 * @param int|array $coupon_id Integer or array of ids sql escaped.
	 * @param array     $data Data to update, escape data.
	 *
	 * @return bool
	 */
	public function update_coupon( $coupon_id, array $data ) {
		$coupon_ids = is_array( $coupon_id ) ? $coupon_id : array( $coupon_id );
		$coupon_ids = QueryHelper::prepare_in_clause( $coupon_ids );
		try {
			QueryHelper::update_where_in(
				$this->table_name,
				$data,
				$coupon_ids
			);
			return true;
		} catch ( \Throwable $th ) {
			error_log( $th->getMessage() . ' in ' . $th->getFile() . ' at line ' . $th->getLine() );
			return false;
		}
	}

	/**
	 * Update coupon
	 *
	 * @since 3.0.0
	 *
	 * @param int|array $coupon_id Integer or array of ids sql escaped.
	 *
	 * @return bool
	 */
	public function delete_coupon( $coupon_id ) {
		$coupon_ids = is_array( $coupon_id ) ? $coupon_id : array( $coupon_id );

		try {
			QueryHelper::bulk_delete_by_ids(
				$this->table_name,
				$coupon_ids
			);
			return true;
		} catch ( \Throwable $th ) {
			error_log( $th->getMessage() . ' in ' . $th->getFile() . ' at line ' . $th->getLine() );
			return false;
		}
	}

	/**
	 * Get Coupon count
	 *
	 * @since 3.0.0
	 *
	 * @param array  $where Where conditions, sql esc data.
	 * @param string $search_term Search terms, sql esc data.
	 *
	 * @return int
	 */
	public function get_coupon_count( $where = array(), string $search_term = '' ) {
		$search_clause = array();
		if ( '' !== $search_term ) {
			foreach ( $this->get_searchable_fields() as $column ) {
				$search_clause[ $column ] = $search_term;
			}
		}

		return QueryHelper::get_count( $this->table_name, $where, $search_clause, '*' );
	}

	/**
	 * Get coupon usage count
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $coupon_code Coupon code.
	 *
	 * @return int
	 */
	public function get_coupon_usage_count( $coupon_code ) {
		return QueryHelper::get_count(
			$this->coupon_usage_table,
			array( 'coupon_code' => $coupon_code ),
			array(),
			'*'
		);
	}

	/**
	 * Get coupon usage count for a user
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $coupon_code Coupon code.
	 * @param int   $user_id User id.
	 *
	 * @return int
	 */
	public function get_user_usage_count( $coupon_code, $user_id ) {
		return QueryHelper::get_count(
			$this->coupon_usage_table,
			array(
				'coupon_code' => $coupon_code,
				'user_id'     => $user_id,
			),
			array(),
			'*'
		);
	}

	/**
	 * Retrieve a coupon by its ID.
	 *
	 * This function fetches the coupon data from the database based on the provided coupon ID.
	 * If the coupon is found, it returns the coupon data; otherwise, it returns false.
	 *
	 * @since 3.0.0
	 *
	 * @param int $coupon_id The ID of the coupon to retrieve.
	 *
	 * @return object|false The coupon data as an object if found, or false if not found.
	 */
	public function get_coupon_by_id( $coupon_id ) {
		$coupon_data = QueryHelper::get_row(
			$this->table_name,
			array( 'id' => $coupon_id ),
			'id'
		);

		if ( ! $coupon_data ) {
			return false;
		}

		return $this->process_coupon_data( $coupon_data );
	}

	public function get_coupon_by_code( $coupon_code ) {
		$coupon_data = QueryHelper::get_row(
			$this->table_name,
			array( 'coupon_code' => $coupon_code ),
			'id'
		);

		if ( ! $coupon_data ) {
			return false;
		}

		return $this->process_coupon_data( $coupon_data );
	}

	/**
	 * Get the list of the all automatic coupons.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_automatic_coupons() {
		$coupons = $this->get_coupons(
			array(
				'coupon_type'   => self::TYPE_AUTOMATIC,
				'coupon_status' => self::STATUS_ACTIVE,
			),
			'',
			1000,
			0
		);

		if ( empty( $coupons['results'] ) ) {
			return array();
		}

		return $coupons['results'];
	}

	private function process_coupon_data( $coupon_data ) {
		$coupon_data->id                  = (int) $coupon_data->id;
		$coupon_data->usage_limit_status  = ! empty( $coupon_data->total_usage_limit ) ? true : false;
		$coupon_data->total_usage_limit   = (int) $coupon_data->total_usage_limit;
		$coupon_data->is_one_use_per_user = ! empty( $coupon_data->per_user_usage_limit ) ? true : false;
		$coupon_data->discount_amount     = (float) $coupon_data->discount_amount;
		$coupon_data->created_by          = get_userdata( $coupon_data->created_by )->display_name ?? '';
		$coupon_data->updated_by          = get_userdata( $coupon_data->updated_by )->display_name ?? '';
		$coupon_data->courses             = array();
		$coupon_data->categories          = array();

		if ( 'specific_courses' === $coupon_data->applies_to || 'specific_bundles' === $coupon_data->applies_to ) {
			$coupon_data->courses = $this->get_coupon_courses_by_code( $coupon_data->coupon_code );
		}

		if ( 'specific_category' === $coupon_data->applies_to ) {
			$coupon_data->categories = $this->get_coupon_categories_by_code( $coupon_data->coupon_code );
		}

		return $coupon_data;
	}



	/**
	 * Retrieve courses associated with a given coupon code.
	 *
	 * This function fetches courses that have been associated with a specified coupon code
	 * from the WordPress database, using the `tutor_coupon_applications` table and joining
	 * it with the `posts` table to get course details.
	 *
	 * @since 3.0.0
	 *
	 * @param string $coupon_code The coupon code to search for associated courses.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return array An array of course objects, each containing:
	 *               - id: The ID of the course.
	 *               - title: The title of the course.
	 *               - type: The post type of the course (e.g., 'course', 'course-bundle').
	 *               - price: The price of the course.
	 *               - sale_price: The sale price of the course.
	 *               - image: The URL of the course's thumbnail image.
	 *               - total_courses: (optional) The total number of courses in a bundle, if applicable.
	 */
	public function get_coupon_courses_by_code( $coupon_code ) {
		global $wpdb;

		$primary_table  = "{$wpdb->prefix}tutor_coupon_applications AS ca";
		$joining_tables = array(
			array(
				'type'  => 'LEFT',
				'table' => "{$wpdb->prefix}posts AS p",
				'on'    => 'p.ID = ca.reference_id',
			),
		);

		$where = array( 'ca.coupon_code' => $coupon_code );

		$select_columns = array( 'ca.reference_id AS id', 'p.post_title AS title', 'p.post_type AS type' );

		$courses_data = QueryHelper::get_joined_data( $primary_table, $joining_tables, $select_columns, $where, array(), 'id', 0, 0 );
		$courses      = $courses_data['results'];

		if ( tutor()->has_pro ) {
			$bundle_model = new \TutorPro\CourseBundle\Models\BundleModel();
		}

		if ( ! empty( $courses_data['total_count'] ) ) {
			foreach ( $courses as &$course ) {
				if ( tutor()->has_pro && 'course-bundle' === $course->type ) {
					$course->total_courses = count( $bundle_model->get_bundle_course_ids( $course->id ) );
				}

				$course_prices      = tutor_utils()->get_raw_course_price( $course->id );
				$course->id         = (int) $course->id;
				$course->price      = $course_prices->regular_price;
				$course->sale_price = $course_prices->sale_price;
				$course->image      = get_the_post_thumbnail_url( $course->id );
			}
		}

		unset( $course );

		return ! empty( $courses ) ? $courses : array();
	}

	/**
	 * Retrieve course categories associated with a given coupon code.
	 *
	 * This function fetches categories that have been associated with a specified coupon code
	 * from the WordPress database, using the `tutor_coupon_applications` table and retrieving
	 * category details from the terms database.
	 *
	 * @since 3.0.0
	 *
	 * @param string $coupon_code The coupon code to search for associated categories.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return array An array of category objects, each containing:
	 *               - term_id: The ID of the category.
	 *               - name: The name of the category.
	 *               - slug: The slug of the category.
	 *               - term_group: The term group of the category.
	 *               - term_taxonomy_id: The taxonomy ID of the category.
	 *               - taxonomy: The taxonomy type of the category.
	 *               - description: The description of the category.
	 *               - parent: The parent ID of the category.
	 *               - count: The number of items in the category.
	 */
	public function get_coupon_categories_by_code( $coupon_code ) {
		global $wpdb;

		$table = "{$wpdb->prefix}tutor_coupon_applications";
		$where = array( 'coupon_code' => $coupon_code );

		$categories = QueryHelper::get_all( $table, $where, 'reference_id' );
		$response   = array();

		foreach ( $categories as $category ) {
			$category_data = get_term_by( 'id', $category->reference_id, 'course-category' );

			if ( $category_data ) {
				// Fetch the thumbnail_id from the wp_termmeta table.
				$thumbnail_id = get_term_meta( $category_data->term_id, 'thumbnail_id', true );

				// If the thumbnail ID is retrieved, get the image URL.
				if ( $thumbnail_id ) {
					$image = wp_get_attachment_url( $thumbnail_id );
				} else {
					$image = ''; // Or set a default image URL if needed.
				}

				$final_data                    = new \stdClass();
				$final_data->id                = $category_data->term_id;
				$final_data->title             = $category_data->name;
				$final_data->number_of_courses = $category_data->count;
				$final_data->image             = $image;

				$response[] = $final_data;
			}
		}

		return $response;
	}

	/**
	 * Get coupon info by coupon code
	 *
	 * @since 3.0.0
	 *
	 * @param array $where Where condition.
	 *
	 * @return mixed
	 */
	public function get_coupon( array $where ) {
		return QueryHelper::get_row(
			$this->table_name,
			$where,
			'id'
		);
	}

	/**
	 * Get coupon details for checkout.
	 *
	 * @param string $coupon_code coupon code.
	 *
	 * @return object
	 */
	public function get_coupon_details_for_checkout( $coupon_code = '' ) {
		$coupon = null;
		if ( empty( $coupon_code ) ) {
			$coupon = $this->get_coupon(
				array(
					'coupon_type'   => self::TYPE_AUTOMATIC,
					'coupon_status' => self::STATUS_ACTIVE,
				)
			);
		} else {
			$coupon = $this->get_coupon(
				array(
					'coupon_code'   => $coupon_code,
					'coupon_status' => self::STATUS_ACTIVE,
				)
			);
		}

		return $coupon;
	}

	/**
	 * Deduct coupon discount
	 *
	 * @since 3.0.0
	 *
	 * @param mixed  $regular_price Regular price.
	 * @param string $discount_type Discount type.
	 * @param mixed  $discount_value Discount value.
	 *
	 * @return float Deducted price
	 */
	public function deduct_coupon_discount( $regular_price, $discount_type, $discount_value ) {
		$deducted_price = $regular_price;
		if ( self::DISCOUNT_TYPE_PERCENTAGE === $discount_type ) {
			$deducted_price = $regular_price - ( $regular_price * ( $discount_value / 100 ) );
		} else {
			$deducted_price = $regular_price - $discount_value;
		}

		return tutor_get_locale_price( max( 0, $deducted_price ) );
	}

	/**
	 * Check whether this coupon is valid or not.
	 *
	 * Considering start-expire time & use limit.
	 *
	 * @since 3.0.0
	 *
	 * @param object $coupon Coupon object.
	 *
	 * @return bool
	 */
	public function is_coupon_valid( object $coupon ): bool {
		return self::STATUS_ACTIVE === $coupon->coupon_status && $this->has_coupon_validity( $coupon ) && $this->has_user_usage_limit( $coupon, get_current_user_id() );
	}

	/**
	 * Check whether this coupon is applicable to the given course or not.
	 *
	 * Applicable is getting determined by the coupon applies_to value
	 *
	 * @since 3.0.0
	 *
	 * @param object $coupon Coupon object.
	 * @param int    $object_id Course/Bundle id.
	 *
	 * @return bool
	 */
	public function is_coupon_applicable( object $coupon, int $object_id ): bool {
		$is_applicable = false;

		$object_id = apply_filters( 'tutor_subscription_course_by_plan', $object_id );

		$course_post_type = tutor()->course_post_type;
		$bundle_post_type = 'course-bundle';
		$object_type      = get_post_type( $object_id );

		$applies_to   = $coupon->applies_to;
		$applications = $this->get_coupon_applications( $coupon->coupon_code );

		switch ( $applies_to ) {
			case self::APPLIES_TO_ALL_COURSES_AND_BUNDLES:
				$is_applicable = true;
				break;

			case self::APPLIES_TO_ALL_COURSES:
			case self::APPLIES_TO_SPECIFIC_COURSES:
				if ( self::APPLIES_TO_ALL_COURSES === $applies_to ) {
					$is_applicable = $object_type === $course_post_type;
				} else {
					$is_applicable = in_array( $object_id, $applications );
				}
				break;

			case self::APPLIES_TO_ALL_BUNDLES:
			case self::APPLIES_TO_SPECIFIC_BUNDLES:
				if ( self::APPLIES_TO_ALL_BUNDLES === $applies_to ) {
					$is_applicable = $object_type === $bundle_post_type;
				} else {
					$is_applicable = in_array( $object_id, $applications );
				}
				break;

			case self::APPLIES_TO_SPECIFIC_CATEGORY:
				$course_categories = wp_get_post_terms( $object_id, 'course-category' );
				if ( ! is_wp_error( $course_categories ) ) {
					$term_ids      = array_column( $course_categories, 'term_id' );
					$is_applicable = count( array_intersect( $applications, $term_ids ) );
				}
				break;
		}

		return apply_filters( 'tutor_coupon_is_applicable', $is_applicable, $coupon, $object_id );
	}

	/**
	 * Check whether meet coupon requirement or not
	 *
	 * @since 3.0.0
	 *
	 * @param int|array $item_id Item id or array of ids. May consist course, bundle or plan.
	 * @param object    $coupon Coupon object.
	 * @param string    $order_type Order type.
	 *
	 * @return boolean
	 */
	public function is_coupon_requirement_meet( $item_id, object $coupon, $order_type = OrderModel::TYPE_SINGLE_ORDER ) {
		$is_meet_requirement = true;
		$item_ids            = is_array( $item_id ) ? $item_id : array( $item_id );

		$total_price              = 0;
		$min_amount               = $coupon->purchase_requirement_value;
		$regular_price_item_count = 0;

		foreach ( $item_ids as $item_id ) {
			$course_price = tutor_utils()->get_raw_course_price( $item_id );
			if ( OrderModel::TYPE_SINGLE_ORDER !== $order_type ) {
				$plan_info = apply_filters( 'tutor_checkout_plan_info', null, $item_id );
				if ( $plan_info ) {
					$course_price->regular_price = $plan_info->regular_price;
					$course_price->sale_price    = $plan_info->in_sale_price ? $plan_info->sale_price : 0;
				}
			}

			$total_price += $course_price->sale_price ? $course_price->sale_price : $course_price->regular_price;
			if ( ! $course_price->sale_price ) {
				$regular_price_item_count++;
			}
		}

		if ( self::REQUIREMENT_MINIMUM_QUANTITY === $coupon->purchase_requirement ) {
			$min_quantity        = $coupon->purchase_requirement_value;
			$is_meet_requirement = count( $item_ids ) >= $min_quantity;
		} elseif ( self::REQUIREMENT_MINIMUM_PURCHASE === $coupon->purchase_requirement && $total_price < $min_amount ) {
			$is_meet_requirement = false;
		}

		/**
		 * If there is no regular price item in the cart, then it's not meet requirement.
		 *
		 * @since 3.0.0
		 */
		if ( 0 === $regular_price_item_count ) {
			$is_meet_requirement = false;
		}

		return apply_filters( 'tutor_coupon_is_meet_requirement', $is_meet_requirement, $coupon, $item_id );
	}

	/**
	 * Check coupon time validity
	 *
	 * @since 3.0.0
	 *
	 * @param object $coupon coupon object.
	 *
	 * @return boolean
	 */
	public function has_coupon_validity( object $coupon ): bool {
		$now         = time();
		$start_date  = strtotime( $coupon->start_date_gmt );
		$expire_date = $coupon->expire_date_gmt ? strtotime( $coupon->expire_date_gmt ) : 0;

		// Check if the current time is within the start and expiry dates.
		return ( $now >= $start_date ) && ( $expire_date ? $now <= $expire_date : true );
	}

	/**
	 * Check coupon usage limit
	 *
	 * @since 3.0.0
	 *
	 * @param object $coupon coupon object.
	 * @param int    $user_id user id.
	 *
	 * @return bool true if has usage limit otherwise false
	 */
	public function has_user_usage_limit( object $coupon, int $user_id ): bool {
		$has_limit = true;

		$total_usage_limit = (int) $coupon->total_usage_limit;
		$user_usage_limit  = (int) $coupon->per_user_usage_limit;

		if ( $total_usage_limit > 0 ) {
			$coupon_usage_count = $this->get_coupon_usage_count( $coupon->coupon_code );
			if ( $coupon_usage_count >= $total_usage_limit ) {
				$has_limit = false;
			}
		}

		if ( $user_usage_limit > 0 ) {
			$user_usage_count = $this->get_user_usage_count( $coupon->coupon_code, $user_id );
			if ( $user_usage_count >= $user_usage_limit ) {
				$has_limit = false;
			}
		}

		return apply_filters( 'tutor_coupon_has_user_usage_limit', $has_limit, $coupon, $user_id );
	}

	/**
	 * Get coupon applications
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $coupon_code Coupon code.
	 *
	 * @return array [1,2,4]
	 */
	public function get_coupon_applications( $coupon_code ): array {
		$response = array();

		$result = QueryHelper::get_all(
			$this->coupon_applies_to_table,
			array( 'coupon_code' => $coupon_code ),
			'coupon_code'
		);

		if ( is_array( $result ) && count( $result ) ) {
			$response = array_column( $result, 'reference_id' );
		}

		return $response;
	}

	/**
	 * Get formatted coupon application items
	 *
	 * @since 3.0.0
	 *
	 * @param object $coupon Coupon object.
	 *
	 * @return array
	 */
	public function get_formatted_coupon_applications( object $coupon ): array {
		$applications = $this->get_coupon_applications( $coupon->coupon_code );
		$response     = array();

		foreach ( $applications as $application_id ) {
			$application = $this->get_application_details( $application_id, $coupon->applies_to );

			if ( $application ) {
				$response[] = $application;
			}
		}

		return $response;
	}

	/**
	 * Get coupon application details
	 *
	 * @since 3.0.0
	 *
	 * @param int $id Application id.
	 *
	 * @return array
	 */
	public function get_application_details( int $id, string $applies_to ): array {
		$response = array();
		if ( self::APPLIES_TO_SPECIFIC_BUNDLES === $applies_to || self::APPLIES_TO_SPECIFIC_COURSES === $applies_to ) {
			$post = get_post( $id );

			if ( $post ) {
				$response = array(
					'id'            => $id,
					'title'         => get_the_title( $id ),
					'image'         => get_the_post_thumbnail_url( $id ),
					'regular_price' => tutor_get_formatted_price( get_post_meta( $id, Course::COURSE_PRICE_META, true ) ),
					'sale_price'    => tutor_get_formatted_price( get_post_meta( $id, Course::COURSE_SALE_PRICE_META, true ) ),
				);
			}
		} elseif ( term_exists( $id ) ) {
			$term = get_term( $id );

			if ( $term ) {
				$thumb_id = get_term_meta( $id, 'thumbnail_id', true );
				$response = array(
					'id'            => $id,
					'title'         => $term->name,
					'image'         => $thumb_id ? wp_get_attachment_thumb_url( $thumb_id ) : '',
					'total_courses' => (int) $term->count,
				);
			}
		}

		return $response;
	}

	/**
	 * Check if applies to is specific
	 *
	 * @since 3.0.0
	 *
	 * @param string $applies_to Applies to.
	 *
	 * @return boolean
	 */
	public function is_specific_applies_to( string $applies_to ) {
		return in_array( $applies_to, array( self::APPLIES_TO_SPECIFIC_BUNDLES, self::APPLIES_TO_SPECIFIC_COURSES, self::APPLIES_TO_SPECIFIC_CATEGORY ) );
	}

	/**
	 * Store coupon usage by using the provided data
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Data to store.
	 *
	 * @throws \Throwable If database error occur.
	 *
	 * @return mixed
	 */
	public function store_coupon_usage( array $data ) {
		try {
			return QueryHelper::insert( $this->coupon_usage_table, $data );
		} catch ( \Throwable $th ) {
			throw $th;
		}
	}

	/**
	 * Delete coupon usage by using the where condition
	 *
	 * @since 3.0.0
	 *
	 * @param array $where Where condition.
	 *
	 * @return mixed
	 */
	public function delete_coupon_usage( array $where ) {
		return QueryHelper::delete( $this->coupon_usage_table, $where );
	}
}
