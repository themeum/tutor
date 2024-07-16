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
	 * Resolve props & dependencies
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name         = $wpdb->prefix . $this->table_name;
		$this->coupon_usage_table = $wpdb->prefix . $this->coupon_usage_table;
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

		$coupon_data->id                  = (int) $coupon_data->id;
		$coupon_data->usage_limit_status  = ! empty( $coupon_data->total_usage_limit ) ? true : false;
		$coupon_data->total_usage_limit   = (int) $coupon_data->total_usage_limit;
		$coupon_data->is_one_use_per_user = ! empty( $coupon_data->is_one_use_per_user ) ? true : false;
		$coupon_data->discount_amount     = (float) $coupon_data->discount_amount;
		$coupon_data->created_by          = get_userdata( $coupon_data->created_by )->display_name;
		$coupon_data->updated_by          = get_userdata( $coupon_data->updated_by )->display_name;
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

				$course_prices      = tutor_utils()->get_course_raw_prices( (int) $course->id );
				$course->id         = (int) $course->id;
				$course->price      = $course_prices->price;
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
}
