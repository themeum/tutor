<?php
/**
 * Manage Course API
 *
 * @package Tutor\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.7.1
 */

namespace TUTOR;

use WP_REST_Request;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rest_Course class
 */
class REST_Course {

	use REST_Response;


	/**
	 * The post type associated with the course handler.
	 *
	 * @since 1.7.1
	 *
	 * @var string $post_type The post type for courses.
	 */
	private $post_type;

	/**
	 * The taxonomy for course categories.
	 *
	 * @since 1.7.1
	 *
	 * @var string $course_cat_tax The taxonomy for course categories.
	 */
	private $course_cat_tax = 'course-category';

	/**
	 * The taxonomy for course tags.
	 *
	 * @since 1.7.1
	 *
	 * @var string $course_tag_tax The taxonomy for course tags.
	 */
	private $course_tag_tax = 'course-tag';

	/**
	 * Constructor for the Tutor_Course_Handler class.
	 *
	 * Initializes the post type property.
	 *
	 * @since 1.7.1
	 */
	public function __construct() {
		$this->post_type = tutor()->course_post_type;
	}

	/**
	 * Course read API handler
	 *
	 * Get course list along with pagination, categories, tags
	 * author details, reviews
	 *
	 * @param WP_REST_Request $request request data.
	 *
	 * @return WP_REST_Response
	 */
	public function course( WP_REST_Request $request ) {
		$order   = sanitize_text_field( $request->get_param( 'order' ) );
		$orderby = sanitize_text_field( $request->get_param( 'orderby' ) );
		$paged   = sanitize_text_field( $request->get_param( 'paged' ) );

		$args = array(
			'post_type'      => $this->post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'paged'          => $paged ? $paged : 1,
			'order'          => $order ? $order : 'ASC',
			'orderby'        => $orderby ? $orderby : 'title',
		);

		$args = apply_filters( 'tutor_rest_course_query_args', $args );

		$query = new WP_Query( $args );

		// if post found.
		if ( count( $query->posts ) > 0 ) {
			// unset filter property.
			array_map(
				function( $post ) {
					unset( $post->filter );
				},
				$query->posts
			);

			$data = array(
				'posts'        => array(),
				'total_course' => $query->found_posts,
				'total_page'   => $query->max_num_pages,
			);

			foreach ( $query->posts as $post ) {
				$category = wp_get_post_terms( $post->ID, $this->course_cat_tax );

				$tag = wp_get_post_terms( $post->ID, $this->course_tag_tax );

				$author = get_userdata( $post->post_author );

				if ( $author ) {
					// Unset user pass & key.
					unset( $author->data->user_pass );
					unset( $author->data->user_activation_key );
				}

				is_a( $author, 'WP_User' ) ? $post->post_author = $author->data : new \stdClass();

				$thumbnail_size      = apply_filters( 'tutor_rest_course_thumbnail_size', 'post-thumbnail' );
				$post->thumbnail_url = get_the_post_thumbnail_url( $post->ID, $thumbnail_size );

				$post->additional_info = $this->course_additional_info( $post->ID );

				$post->ratings = tutor_utils()->get_course_rating( $post->ID );

				$post->course_category = $category;

				$post->course_tag = $tag;

				$post = apply_filters( 'tutor_rest_course_single_post', $post );
				array_push( $data['posts'], $post );
			}

			$response = array(
				'status_code' => 'success',
				'message'     => __( 'Course retrieved successfully', 'tutor' ),
				'data'        => $data,
			);

			return self::send( $response );
		}

		$response = array(
			'status_code' => 'not_found',
			'message'     => __( 'Course not found', 'tutor' ),
			'data'        => array(),
		);

		return self::send( $response );
	}

	/**
	 * Course Details API handler
	 *
	 * @since 1.7.1
	 *
	 * @param WP_REST_Request $request request params.
	 *
	 * @return WP_REST_Response
	 */
	public function course_detail( WP_REST_Request $request ) {
		$post_id = $request->get_param( 'id' );

		$detail = $this->course_additional_info( $post_id );
		if ( $detail ) {
			$response = array(
				'status_code' => 'course_detail',
				'message'     => __( 'Course detail retrieved successfully', 'tutor' ),
				'data'        => $detail,
			);
			return self::send( $response );
		}
		$response = array(
			'status_code' => 'course_detail',
			'message'     => __( 'Detail not found for given ID', 'tutor' ),
			'data'        => array(),
		);

		return self::send( $response );
	}

	/**
	 * Get course additional info
	 *
	 * @since 2.6.1
	 *
	 * @param integer $post_id post id.
	 *
	 * @return array
	 */
	public function course_additional_info( int $post_id ) {
		$detail = array(

			'course_settings'          => get_post_meta( $post_id, '_tutor_course_settings', false ),

			'course_price_type'        => get_post_meta( $post_id, '_tutor_course_price_type', false ),

			'course_duration'          => get_post_meta( $post_id, '_course_duration', false ),

			'course_level'             => get_post_meta( $post_id, '_tutor_course_level', false ),

			'course_benefits'          => get_post_meta( $post_id, '_tutor_course_benefits', false ),

			'course_requirements'      => get_post_meta( $post_id, '_tutor_course_requirements', false ),

			'course_target_audience'   => get_post_meta( $post_id, '_tutor_course_target_audience', false ),

			'course_material_includes' => get_post_meta( $post_id, '_tutor_course_material_includes', false ),

			'video'                    => get_post_meta( $post_id, '_video', false ),

			'disable_qa'               => get_post_meta( $post_id, '_tutor_enable_qa', true ) != 'yes',
		);

		return apply_filters( 'tutor_course_additional_info', $detail );

	}

	/**
	 * Get Course by Terms API handler
	 *
	 * @since 1.7.1
	 *
	 * @param WP_REST_Request $request request params.
	 *
	 * @return WP_REST_Response
	 */
	public function course_by_terms( WP_REST_Request $request ) {
		$post_fields  = $request->get_params();
		$validate_err = $this->validate_terms( $post_fields );

		// check array or not.
		if ( count( $validate_err ) > 0 ) {
			$response = array(
				'status_code' => 'validation_error',
				'message'     => $validate_err,
				'data'        => array(),
			);

			return self::send( $response );
		}

		// sanitize terms.
		$categories = sanitize_term( $request['categories'], $this->course_cat_tax, $context = 'db' );

		$tags = sanitize_term( $request['tags'], $this->course_tag_tax, $context = 'db' );

		$args = array(
			'post_type' => $this->post_type,
			'tax_query' => array(
				'relation' => 'OR',
				array(
					'taxonomy' => $this->course_cat_tax,
					'field'    => 'name',
					'terms'    => $categories,
				),
				array(
					'taxonomy' => $this->course_tag_tax,
					'field'    => 'name',
					'terms'    => $tags,

				),
			),
		);

		$args  = apply_filters( 'tutor_rest_course_by_terms_query_args', $args );
		$query = new WP_Query( $args );

		if ( count( $query->posts ) > 0 ) {
			// unset filter property.
			array_map(
				function( $post ) {
					unset( $post->filter );
				},
				$query->posts
			);

			$response = array(
				'status_code' => 'success',
				'message'     => __( 'Course retrieved successfully', 'tutor' ),
				'data'        => $query->posts,
			);

			return self::send( $response );
		}

		$response = array(
			'status_code' => 'not_found',
			'message'     => __( 'Course not found for given terms', 'tutor' ),
			'data'        => array(),
		);
		return self::send( $response );
	}

	/**
	 * Validate terms
	 *
	 * @since 1.7.1
	 *
	 * @param array $post post array.
	 *
	 * @return array validation errors.
	 */
	public function validate_terms( array $post ) {
		$categories = $post['categories'];
		$tags       = $post['tags'];

		$error = array();

		if ( ! is_array( $categories ) ) {
			array_push( $error, __( 'Categories field is not an array', 'tutor' ) );
		}

		if ( ! is_array( $tags ) ) {
			array_push( $error, __( 'Tags field is not an array', 'tutor' ) );
		}

		return $error;
	}

	/**
	 * Course sort by price API handler
	 *
	 * @since 1.7.1
	 *
	 * @param WP_REST_Request $request request params.
	 *
	 * @return WP_REST_Response
	 */
	public function course_sort_by_price( WP_REST_Request $request ) {
		$order = $request->get_param( 'order' );
		$paged = $request->get_param( 'page' );

		$order = sanitize_text_field( $order );
		$paged = sanitize_text_field( $paged );

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'paged'          => $paged ? $paged : 1,

			'meta_key'       => '_regular_price',
			'orderby'        => 'meta_value_num',
			'order'          => $order,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'   => '_tutor_product',
					'value' => 'yes',

				),
			),
		);
		$args = apply_filters( 'tutor_rest_course_sort_by_price_args', $args );

		$query = new WP_Query( $args );

		if ( count( $query->posts ) > 0 ) {
			// unset filter property.
			array_map(
				function( $post ) {
					unset( $post->filter );
				},
				$query->posts
			);

			$posts = array();

			foreach ( $query->posts as $post ) {
				$post->price = get_post_meta( $post->ID, '_regular_price', true );
				array_push( $posts, $post );
			}

			$data = array(
				'posts'        => $posts,
				'total_course' => $query->found_posts,
				'total_page'   => $query->max_num_pages,
			);

			$response = array(
				'status_code' => 'success',
				'message'     => __( 'Course retrieved successfully', 'tutor' ),
				'data'        => $data,
			);

			return self::send( $response );
		}

		$response = array(
			'status'  => 'not_found',
			'message' => __( 'Course not found', 'tutor' ),
			'data'    => array(),
		);
		return self::send( $response );
	}
}
