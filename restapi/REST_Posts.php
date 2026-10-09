<?php
/**
 * Shared REST post helpers.
 *
 * @package Tutor\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.2.0
 */

namespace TUTOR;

use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class REST_Posts
 */
class REST_Posts {

	/**
	 * Map a post to public REST fields (no password/guid/status/etc).
	 *
	 * @since 4.2.0
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return object
	 */
	public static function to_public_post_dto( WP_Post $post ) {
		return (object) array(
			'ID'           => (int) $post->ID,
			'post_title'   => $post->post_title,
			'post_content' => $post->post_content,
			'post_name'    => $post->post_name,
		);
	}

	/**
	 * Curriculum outline DTO (titles only — guest / locked curriculum parity).
	 *
	 * @since 4.2.0
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return object
	 */
	public static function to_outline_post_dto( WP_Post $post ) {
		$dto = (object) array(
			'ID'         => (int) $post->ID,
			'post_title' => $post->post_title,
			'post_name'  => $post->post_name,
			'post_type'  => $post->post_type,
		);

		$video_info = tutor_utils()->get_video_info( $post->ID );
		if ( $video_info && ! empty( $video_info->playtime ) ) {
			$dto->duration = $video_info->playtime;
		}

		return $dto;
	}

	/**
	 * Fetch published child posts as public DTOs.
	 *
	 * @since 4.2.0
	 *
	 * @param string $post_type   Post type slug.
	 * @param int    $post_parent Parent post ID.
	 * @param array  $args        Optional get_posts() overrides (orderby, order, …).
	 *
	 * @return object[]
	 */
	public static function get_published_child_posts( $post_type, $post_parent, $args = array() ) {
		$query_args = wp_parse_args(
			$args,
			array(
				'post_type'      => $post_type,
				'post_parent'    => absint( $post_parent ),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			)
		);

		$posts  = get_posts( $query_args );
		$result = array();

		foreach ( $posts as $post ) {
			$result[] = self::to_public_post_dto( $post );
		}

		return $result;
	}
}
