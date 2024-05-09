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
		$order      = sanitize_text_field( $request->get_param( 'order' ) );
		$orderby    = sanitize_text_field( $request->get_param( 'orderby' ) );
		$paged      = sanitize_text_field( $request->get_param( 'paged' ) );
		$categories = null;
		if ( isset( $request['categories'] ) ) {
			$categories = sanitize_term( explode( ',', $request['categories'] ), $this->course_cat_tax, $context = 'db' );
		}
		$tags = null;
		if ( isset( $request['tags'] ) ) {
			$tags = sanitize_term( explode( ',', $request['tags'] ), $this->course_tag_tax, $context = 'db' );
		}

		$post_per_page = tutor_utils()->get_option( 'pagination_per_page' );

		$args = array(
			'post_type'      => $this->post_type,
			'post_status'    => 'publish',
			'posts_per_page' => $post_per_page,
			'paged'          => $paged ? $paged : 1,
			'order'          => $order ? $order : 'ASC',
			'orderby'        => $orderby ? $orderby : 'title',
		);

		if ( isset( $categories ) || isset( $tags ) ) {
			$args['tax_query'] = array(
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
			);
		}

		if ( 'price' === $orderby ) {
			$args['post_type']  = 'product';
			$args['meta_key']   = '_regular_price';
			$args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'   => '_tutor_product',
					'value' => 'yes',
				),
			);
		}

		$args = apply_filters( 'tutor_rest_course_query_args', $args );

		$query = new WP_Query( $args );

		// if post found.
		if ( count( $query->posts ) > 0 ) {
			// unset filter property.
			array_map(
				function ( $post ) {
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

				$post->price = get_post_meta( $post->ID, '_regular_price', true );

				$post = apply_filters( 'tutor_rest_course_single_post', $post );

				array_push( $data['posts'], $post );
			}

			$response = array(
				'code'    => 'success',
				'message' => __( 'Course retrieved successfully', 'tutor' ),
				'data'    => $data,
			);

			return self::send( $response );
		}

		$response = array(
			'code'    => 'not_found',
			'message' => __( 'Course not found', 'tutor' ),
			'data'    => array(),
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
				'code'    => 'course_detail',
				'message' => __( 'Course detail retrieved successfully', 'tutor' ),
				'data'    => $detail,
			);
			return self::send( $response );
		}
		$response = array(
			'code'    => 'course_detail',
			'message' => __( 'Detail not found for given ID', 'tutor' ),
			'data'    => array(),
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
	 * Retrieve the course contents for a given course id
	 *
	 * @since 2.7.0
	 *
	 * @param WP_REST_Request $request request params.
	 *
	 * @return WP_REST_Response
	 */
	public function course_contents( WP_REST_Request $request ) {
		$course_id = $request->get_param( 'id' );
		$topics    = tutor_utils()->get_topics( $course_id );

		if ( $topics->have_posts() ) {
			$data = array();
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
						array_push( $current_topic['contents'], $post );
					}
				}

				array_push( $data, $current_topic );
			}

			$response = array(
				'code'    => 'success',
				'message' => __( 'Course contents retrieved successfully', 'tutor' ),
				'data'    => $data,
			);
			return self::send( $response );
		}

		$response = array(
			'code'    => 'not_found',
			'message' => __( 'Contents for this course with the given course id not found', 'tutor' ),
			'data'    => array(),
		);

		return self::send( $response );
	}
}
