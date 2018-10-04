<?php
namespace TUTOR;
if ( ! defined( 'ABSPATH' ) )
	exit;

class Post_types{
	
	public $course_post_type;
	public $lesson_post_type;

	public function __construct() {
		$this->course_post_type = tutor()->course_post_type;
		$this->lesson_post_type = tutor()->lesson_post_type;
		
		add_action( 'init', array($this, 'register_course_post_types') );
		add_action( 'init', array($this, 'register_lesson_post_types') );
	}
	
	public function register_course_post_types() {
		$labels = array(
			'name'               => _x( 'Courses', 'post type general name', 'tutor' ),
			'singular_name'      => _x( 'Course', 'post type singular name', 'tutor' ),
			'menu_name'          => _x( 'Courses', 'admin menu', 'tutor' ),
			'name_admin_bar'     => _x( 'Course', 'add new on admin bar', 'tutor' ),
			'add_new'            => _x( 'Add New', $this->course_post_type, 'tutor' ),
			'add_new_item'       => __( 'Add New Course', 'tutor' ),
			'new_item'           => __( 'New Course', 'tutor' ),
			'edit_item'          => __( 'Edit Course', 'tutor' ),
			'view_item'          => __( 'View Course', 'tutor' ),
			'all_items'          => __( 'All Courses', 'tutor' ),
			'search_items'       => __( 'Search Courses', 'tutor' ),
			'parent_item_colon'  => __( 'Parent Courses:', 'tutor' ),
			'not_found'          => __( 'No courses found.', 'tutor' ),
			'not_found_in_trash' => __( 'No courses found in Trash.', 'tutor' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'tutor' ),
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
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt'),

			'capabilities' => array(
				'edit_post'          => 'edit_tutor_course',
				'read_post'          => 'read_tutor_course',
				'delete_post'        => 'delete_tutor_course',
				'delete_posts'       => 'delete_tutor_courses',
				'edit_posts'         => 'edit_tutor_courses',
				'edit_others_posts'  => 'edit_others_tutor_courses',
				'publish_posts'      => 'publish_tutor_courses',
				'read_private_posts' => 'read_private_tutor_courses',
				'create_posts'       => 'edit_tutor_courses',
			),
		);

		register_post_type( $this->course_post_type, $args );


		/**
		 * Taxonomy
		 */

		$labels = array(
			'name'                       => _x( 'Categories', 'taxonomy general name', 'tutor' ),
			'singular_name'              => _x( 'Category', 'taxonomy singular name', 'tutor' ),
			'search_items'               => __( 'Search Categories', 'tutor' ),
			'popular_items'              => __( 'Popular Categories', 'tutor' ),
			'all_items'                  => __( 'All Categories', 'tutor' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Category', 'tutor' ),
			'update_item'                => __( 'Update Category', 'tutor' ),
			'add_new_item'               => __( 'Add New Category', 'tutor' ),
			'new_item_name'              => __( 'New Category Name', 'tutor' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'tutor' ),
			'add_or_remove_items'        => __( 'Add or remove categories', 'tutor' ),
			'choose_from_most_used'      => __( 'Choose from the most used categories', 'tutor' ),
			'not_found'                  => __( 'No categories found.', 'tutor' ),
			'menu_name'                  => __( 'Categories', 'tutor' ),
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
			'name'                       => _x( 'Tags', 'taxonomy general name', 'tutor' ),
			'singular_name'              => _x( 'Tag', 'taxonomy singular name', 'tutor' ),
			'search_items'               => __( 'Search Tags', 'tutor' ),
			'popular_items'              => __( 'Popular Tags', 'tutor' ),
			'all_items'                  => __( 'All Tags', 'tutor' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tag', 'tutor' ),
			'update_item'                => __( 'Update Tag', 'tutor' ),
			'add_new_item'               => __( 'Add New Tag', 'tutor' ),
			'new_item_name'              => __( 'New Tag Name', 'tutor' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'tutor' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'tutor' ),
			'choose_from_most_used'      => __( 'Choose from the most used tags', 'tutor' ),
			'not_found'                  => __( 'No tags found.', 'tutor' ),
			'menu_name'                  => __( 'Tags', 'tutor' ),
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
			'name'               => _x( 'Lessons', 'post type general name', 'tutor' ),
			'singular_name'      => _x( 'Lesson', 'post type singular name', 'tutor' ),
			'menu_name'          => _x( 'Lessons', 'admin menu', 'tutor' ),
			'name_admin_bar'     => _x( 'Lesson', 'add new on admin bar', 'tutor' ),
			'add_new'            => _x( 'Add New', $this->lesson_post_type, 'tutor' ),
			'add_new_item'       => __( 'Add New Lesson', 'tutor' ),
			'new_item'           => __( 'New Lesson', 'tutor' ),
			'edit_item'          => __( 'Edit Lesson', 'tutor' ),
			'view_item'          => __( 'View Lesson', 'tutor' ),
			'all_items'          => __( 'All Lessons', 'tutor' ),
			'search_items'       => __( 'Search Lessons', 'tutor' ),
			'parent_item_colon'  => __( 'Parent Lessons:', 'tutor' ),
			'not_found'          => __( 'No lessons found.', 'tutor' ),
			'not_found_in_trash' => __( 'No lessons found in Trash.', 'tutor' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'tutor' ),
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
			'supports'           => array( 'title', 'editor', 'thumbnail'),
			'capabilities' => array(
				'edit_post'          => 'edit_tutor_lesson',
				'read_post'          => 'read_tutor_lesson',
				'delete_post'        => 'delete_tutor_lesson',
				'delete_posts'       => 'delete_tutor_lessons',
				'edit_posts'         => 'edit_tutor_lessons',
				'edit_others_posts'  => 'edit_others_tutor_lessons',
				'publish_posts'      => 'publish_tutor_lessons',
				'read_private_posts' => 'read_private_tutor_lessons',
				'create_posts'       => 'edit_tutor_lessons',
			),
		);

		register_post_type( $this->lesson_post_type, $args );

	}



}