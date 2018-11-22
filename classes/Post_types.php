<?php
namespace DOZENT;
if ( ! defined( 'ABSPATH' ) )
	exit;

class Post_types{
	
	public $course_post_type;
	public $lesson_post_type;

	public function __construct() {
		$this->course_post_type = dozent()->course_post_type;
		$this->lesson_post_type = dozent()->lesson_post_type;
		
		add_action( 'init', array($this, 'register_course_post_types') );
		add_action( 'init', array($this, 'register_lesson_post_types') );
		add_action( 'init', array($this, 'register_quiz_post_types') );
		add_action( 'init', array($this, 'register_quiz_question_post_types') );
	}
	
	public function register_course_post_types() {
		$labels = array(
			'name'               => _x( 'Courses', 'post type general name', 'dozent' ),
			'singular_name'      => _x( 'Course', 'post type singular name', 'dozent' ),
			'menu_name'          => _x( 'Courses', 'admin menu', 'dozent' ),
			'name_admin_bar'     => _x( 'Course', 'add new on admin bar', 'dozent' ),
			'add_new'            => _x( 'Add New', $this->course_post_type, 'dozent' ),
			'add_new_item'       => __( 'Add New Course', 'dozent' ),
			'new_item'           => __( 'New Course', 'dozent' ),
			'edit_item'          => __( 'Edit Course', 'dozent' ),
			'view_item'          => __( 'View Course', 'dozent' ),
			'all_items'          => __( 'Courses', 'dozent' ),
			'search_items'       => __( 'Search Courses', 'dozent' ),
			'parent_item_colon'  => __( 'Parent Courses:', 'dozent' ),
			'not_found'          => __( 'No courses found.', 'dozent' ),
			'not_found_in_trash' => __( 'No courses found in Trash.', 'dozent' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'dozent' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'dozent',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $this->course_post_type ),
			'menu_icon'         => 'dashicons-book-alt',
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'taxonomies'         => array( 'course-category', 'course-tag' ),
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt'),

			'capabilities' => array(
				'edit_post'          => 'edit_dozent_course',
				'read_post'          => 'read_dozent_course',
				'delete_post'        => 'delete_dozent_course',
				'delete_posts'       => 'delete_dozent_courses',
				'edit_posts'         => 'edit_dozent_courses',
				'edit_others_posts'  => 'edit_others_dozent_courses',
				'publish_posts'      => 'publish_dozent_courses',
				'read_private_posts' => 'read_private_dozent_courses',
				'create_posts'       => 'edit_dozent_courses',
			),
		);

		register_post_type($this->course_post_type, $args);

		/**
		 * Taxonomy
		 */
		$labels = array(
			'name'                       => _x( 'Categories', 'taxonomy general name', 'dozent' ),
			'singular_name'              => _x( 'Category', 'taxonomy singular name', 'dozent' ),
			'search_items'               => __( 'Search Categories', 'dozent' ),
			'popular_items'              => __( 'Popular Categories', 'dozent' ),
			'all_items'                  => __( 'All Categories', 'dozent' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Category', 'dozent' ),
			'update_item'                => __( 'Update Category', 'dozent' ),
			'add_new_item'               => __( 'Add New Category', 'dozent' ),
			'new_item_name'              => __( 'New Category Name', 'dozent' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'dozent' ),
			'add_or_remove_items'        => __( 'Add or remove categories', 'dozent' ),
			'choose_from_most_used'      => __( 'Choose from the most used categories', 'dozent' ),
			'not_found'                  => __( 'No categories found.', 'dozent' ),
			'menu_name'                  => __( 'Categories', 'dozent' ),
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
			'name'                       => _x( 'Tags', 'taxonomy general name', 'dozent' ),
			'singular_name'              => _x( 'Tag', 'taxonomy singular name', 'dozent' ),
			'search_items'               => __( 'Search Tags', 'dozent' ),
			'popular_items'              => __( 'Popular Tags', 'dozent' ),
			'all_items'                  => __( 'All Tags', 'dozent' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tag', 'dozent' ),
			'update_item'                => __( 'Update Tag', 'dozent' ),
			'add_new_item'               => __( 'Add New Tag', 'dozent' ),
			'new_item_name'              => __( 'New Tag Name', 'dozent' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'dozent' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'dozent' ),
			'choose_from_most_used'      => __( 'Choose from the most used tags', 'dozent' ),
			'not_found'                  => __( 'No tags found.', 'dozent' ),
			'menu_name'                  => __( 'Tags', 'dozent' ),
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
			'name'               => _x( 'Lessons', 'post type general name', 'dozent' ),
			'singular_name'      => _x( 'Lesson', 'post type singular name', 'dozent' ),
			'menu_name'          => _x( 'Lessons', 'admin menu', 'dozent' ),
			'name_admin_bar'     => _x( 'Lesson', 'add new on admin bar', 'dozent' ),
			'add_new'            => _x( 'Add New', $this->lesson_post_type, 'dozent' ),
			'add_new_item'       => __( 'Add New Lesson', 'dozent' ),
			'new_item'           => __( 'New Lesson', 'dozent' ),
			'edit_item'          => __( 'Edit Lesson', 'dozent' ),
			'view_item'          => __( 'View Lesson', 'dozent' ),
			'all_items'          => __( 'Lessons', 'dozent' ),
			'search_items'       => __( 'Search Lessons', 'dozent' ),
			'parent_item_colon'  => __( 'Parent Lessons:', 'dozent' ),
			'not_found'          => __( 'No lessons found.', 'dozent' ),
			'not_found_in_trash' => __( 'No lessons found in Trash.', 'dozent' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'dozent' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'dozent',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $this->lesson_post_type ),
			'menu_icon'    => 'dashicons-list-view',
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail'),
			'capabilities' => array(
				'edit_post'          => 'edit_dozent_lesson',
				'read_post'          => 'read_dozent_lesson',
				'delete_post'        => 'delete_dozent_lesson',
				'delete_posts'       => 'delete_dozent_lessons',
				'edit_posts'         => 'edit_dozent_lessons',
				'edit_others_posts'  => 'edit_others_dozent_lessons',
				'publish_posts'      => 'publish_dozent_lessons',
				'read_private_posts' => 'read_private_dozent_lessons',
				'create_posts'       => 'edit_dozent_lessons',
			),
		);

		register_post_type( $this->lesson_post_type, $args );
	}
	
	public function register_quiz_post_types() {
		$labels = array(
			'name'               => _x( 'Quizzes', 'post type general name', 'dozent' ),
			'singular_name'      => _x( 'Quiz', 'post type singular name', 'dozent' ),
			'menu_name'          => _x( 'Quizzes', 'admin menu', 'dozent' ),
			'name_admin_bar'     => _x( 'Quiz', 'add new on admin bar', 'dozent' ),
			'add_new'            => _x( 'Add New', $this->lesson_post_type, 'dozent' ),
			'add_new_item'       => __( 'Add New Quiz', 'dozent' ),
			'new_item'           => __( 'New Quiz', 'dozent' ),
			'edit_item'          => __( 'Edit Quiz', 'dozent' ),
			'view_item'          => __( 'View Quiz', 'dozent' ),
			'all_items'          => __( 'Quizzes', 'dozent' ),
			'search_items'       => __( 'Search Quizzes', 'dozent' ),
			'parent_item_colon'  => __( 'Parent Quizzes:', 'dozent' ),
			'not_found'          => __( 'No quizzes found.', 'dozent' ),
			'not_found_in_trash' => __( 'No quizzes found in Trash.', 'dozent' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'dozent' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'dozent',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $this->lesson_post_type ),
			'menu_icon'          => 'dashicons-editor-help',
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor'),
			'capabilities' => array(
				'edit_post'          => 'edit_dozent_quiz',
				'read_post'          => 'read_dozent_quiz',
				'delete_post'        => 'delete_dozent_quiz',
				'delete_posts'       => 'delete_dozent_quizzes',
				'edit_posts'         => 'edit_dozent_quizzes',
				'edit_others_posts'  => 'edit_others_dozent_quizzes',
				'publish_posts'      => 'publish_dozent_quizzes',
				'read_private_posts' => 'read_private_dozent_quizzes',
				'create_posts'       => 'edit_dozent_quizzes',
			),
		);

		register_post_type( 'dozent_quiz', $args );
	}
	
	public function register_quiz_question_post_types() {
		$labels = array(
			'name'               => _x( 'Questions', 'post type general name', 'dozent' ),
			'singular_name'      => _x( 'Question', 'post type singular name', 'dozent' ),
			'menu_name'          => _x( 'Questions', 'admin menu', 'dozent' ),
			'name_admin_bar'     => _x( 'Question', 'add new on admin bar', 'dozent' ),
			'add_new'            => _x( 'Add New', $this->lesson_post_type, 'dozent' ),
			'add_new_item'       => __( 'Add New Question', 'dozent' ),
			'new_item'           => __( 'New Question', 'dozent' ),
			'edit_item'          => __( 'Edit Question', 'dozent' ),
			'view_item'          => __( 'View Question', 'dozent' ),
			'all_items'          => __( 'Questions', 'dozent' ),
			'search_items'       => __( 'Search Questions', 'dozent' ),
			'parent_item_colon'  => __( 'Parent Questions:', 'dozent' ),
			'not_found'          => __( 'No questions found.', 'dozent' ),
			'not_found_in_trash' => __( 'No questions found in Trash.', 'dozent' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'dozent' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'dozent',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $this->lesson_post_type ),
			'menu_icon'          => 'dashicons-editor-help',
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( ''),
			'capabilities' => array(
				'edit_post'          => 'edit_dozent_question',
				'read_post'          => 'read_dozent_question',
				'delete_post'        => 'delete_dozent_question',
				'delete_posts'       => 'delete_dozent_questions',
				'edit_posts'         => 'edit_dozent_questions',
				'edit_others_posts'  => 'edit_others_dozent_questions',
				'publish_posts'      => 'publish_dozent_questions',
				'read_private_posts' => 'read_private_dozent_questions',
				'create_posts'       => 'edit_dozent_questions',
			),
		);

		register_post_type( 'dozent_question', $args );
	}
}