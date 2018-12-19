<?php
/**
 * Gutenberg class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.0.0
 */


namespace TUTOR;


if ( ! defined( 'ABSPATH' ) )
	exit;

class Gutenberg {
	
	public function __construct() {
		add_action( 'init', array($this, 'register_blocks') );
		add_filter('block_categories', array($this, 'registering_new_block_category'), 10, 2);
	}
	
	function register_blocks() {
		wp_register_script(
			'tutor-student-registration-block', tutor()->url . 'assets/js/gutenberg_blocks.js', array( 'wp-blocks', 'wp-element' )
		);

		register_block_type( 'tutor-gutenberg/student-registration', array(
			'editor_script' => 'tutor-student-registration-block',
		) );
		register_block_type( 'tutor-gutenberg/student-dashboard', array(
			'editor_script' => 'tutor-student-registration-block',
		) );
		register_block_type( 'tutor-gutenberg/instructor-registration', array(
			'editor_script' => 'tutor-student-registration-block',
		) );
	}

	public function registering_new_block_category($categories, $post ){
		return array_merge(
			array(
				array(
					'slug' => 'tutor',
					'title' => __( 'Tutor LMS', 'tutor' ),
				),
			),
			$categories
		);
	}




}