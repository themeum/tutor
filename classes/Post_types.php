<?php
namespace LMS;
if ( ! defined( 'ABSPATH' ) )
	exit;

class Post_types{
	
	public $course_post_type;
	public $lesson_post_type;

	public function __construct() {
		$this->course_post_type = lms()->course_post_type;
		$this->lesson_post_type = lms()->lesson_post_type;
		
		add_action( 'init', array($this, 'register_course_post_types') );
		add_action( 'init', array($this, 'register_lesson_post_types') );
	}
	
	public function register_course_post_types() {
		$labels = array(
			'name'               => _x( 'Courses', 'post type general name', 'lms' ),
			'singular_name'      => _x( 'Course', 'post type singular name', 'lms' ),
			'menu_name'          => _x( 'Courses', 'admin menu', 'lms' ),
			'name_admin_bar'     => _x( 'Course', 'add new on admin bar', 'lms' ),
			'add_new'            => _x( 'Add New', $this->course_post_type, 'lms' ),
			'add_new_item'       => __( 'Add New Course', 'lms' ),
			'new_item'           => __( 'New Course', 'lms' ),
			'edit_item'          => __( 'Edit Course', 'lms' ),
			'view_item'          => __( 'View Course', 'lms' ),
			'all_items'          => __( 'All Courses', 'lms' ),
			'search_items'       => __( 'Search Courses', 'lms' ),
			'parent_item_colon'  => __( 'Parent Courses:', 'lms' ),
			'not_found'          => __( 'No courses found.', 'lms' ),
			'not_found_in_trash' => __( 'No courses found in Trash.', 'lms' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'lms' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $this->course_post_type ),
			'menu_icon'         => 'dashicons-book-alt',
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt')
		);

		register_post_type( $this->course_post_type, $args );


		/**
		 * Taxonomy
		 */

		$labels = array(
			'name'                       => _x( 'Categories', 'taxonomy general name', 'lms' ),
			'singular_name'              => _x( 'Category', 'taxonomy singular name', 'lms' ),
			'search_items'               => __( 'Search Categories', 'lms' ),
			'popular_items'              => __( 'Popular Categories', 'lms' ),
			'all_items'                  => __( 'All Categories', 'lms' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Category', 'lms' ),
			'update_item'                => __( 'Update Category', 'lms' ),
			'add_new_item'               => __( 'Add New Category', 'lms' ),
			'new_item_name'              => __( 'New Category Name', 'lms' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'lms' ),
			'add_or_remove_items'        => __( 'Add or remove categories', 'lms' ),
			'choose_from_most_used'      => __( 'Choose from the most used categories', 'lms' ),
			'not_found'                  => __( 'No categories found.', 'lms' ),
			'menu_name'                  => __( 'Categories', 'lms' ),
		);

		$args = array(
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'course-category' ),
		);

		register_taxonomy( 'course-category', $this->course_post_type, $args );

		$labels = array(
			'name'                       => _x( 'Tags', 'taxonomy general name', 'lms' ),
			'singular_name'              => _x( 'Tag', 'taxonomy singular name', 'lms' ),
			'search_items'               => __( 'Search Tags', 'lms' ),
			'popular_items'              => __( 'Popular Tags', 'lms' ),
			'all_items'                  => __( 'All Tags', 'lms' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tag', 'lms' ),
			'update_item'                => __( 'Update Tag', 'lms' ),
			'add_new_item'               => __( 'Add New Tag', 'lms' ),
			'new_item_name'              => __( 'New Tag Name', 'lms' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'lms' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'lms' ),
			'choose_from_most_used'      => __( 'Choose from the most used tags', 'lms' ),
			'not_found'                  => __( 'No tags found.', 'lms' ),
			'menu_name'                  => __( 'Tags', 'lms' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'course-tag' ),
		);

		register_taxonomy( 'course-tag', $this->course_post_type, $args );
	}

	public function register_lesson_post_types() {
		$labels = array(
			'name'               => _x( 'Lessons', 'post type general name', 'lms' ),
			'singular_name'      => _x( 'Lesson', 'post type singular name', 'lms' ),
			'menu_name'          => _x( 'Lessons', 'admin menu', 'lms' ),
			'name_admin_bar'     => _x( 'Lesson', 'add new on admin bar', 'lms' ),
			'add_new'            => _x( 'Add New', $this->lesson_post_type, 'lms' ),
			'add_new_item'       => __( 'Add New Lesson', 'lms' ),
			'new_item'           => __( 'New Lesson', 'lms' ),
			'edit_item'          => __( 'Edit Lesson', 'lms' ),
			'view_item'          => __( 'View Lesson', 'lms' ),
			'all_items'          => __( 'All Lessons', 'lms' ),
			'search_items'       => __( 'Search Lessons', 'lms' ),
			'parent_item_colon'  => __( 'Parent Lessons:', 'lms' ),
			'not_found'          => __( 'No lessons found.', 'lms' ),
			'not_found_in_trash' => __( 'No lessons found in Trash.', 'lms' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'lms' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $this->lesson_post_type ),
			'menu_icon'    => 'dashicons-list-view',
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail')
		);

		register_post_type( $this->lesson_post_type, $args );

	}



}