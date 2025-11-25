<?php
/**
 * Comments component documentation and demo.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

// Sample data matching the attached image demo.
$sample_comments_full_demo = array(
	array(
		'id'     => 1,
		'author' => 'Donald',
		'avatar' => 'https://i.pravatar.cc/150?u=donald',
		'time'   => '2 days ago',
		'text'   => "It's so nerve-racking. :(",
		'likes'  => 5,
		'liked'  => false,
	),
	array(
		'id'      => 2,
		'author'  => 'Donald',
		'avatar'  => 'https://i.pravatar.cc/150?u=donald2',
		'time'    => '2 days ago',
		'text'    => 'Every class to revise and for quick referrence.',
		'likes'   => 5,
		'liked'   => false,
		'replies' => array(
			array(
				'id'     => 3,
				'author' => 'Reply Author 1',
				'avatar' => 'https://i.pravatar.cc/150?u=reply1',
				'time'   => '2 days ago',
				'text'   => 'Reply comment 1',
				'likes'  => 0,
				'liked'  => false,
			),
			array(
				'id'     => 4,
				'author' => 'Reply Author 2',
				'avatar' => 'https://i.pravatar.cc/150?u=reply2',
				'time'   => '2 days ago',
				'text'   => 'Reply comment 2',
				'likes'  => 0,
				'liked'  => false,
			),
			array(
				'id'     => 5,
				'author' => 'Reply Author 3',
				'avatar' => 'https://i.pravatar.cc/150?u=reply3',
				'time'   => '2 days ago',
				'text'   => 'Reply comment 3',
				'likes'  => 0,
				'liked'  => false,
			),
		),
	),
	array(
		'id'     => 6,
		'author' => 'Donald',
		'avatar' => 'https://i.pravatar.cc/150?u=donald3',
		'time'   => '2 days ago',
		'text'   => 'Great courese for beginners, Good explanations but I would love if they provide cheat sheet after every class to revise and for quick referrence.',
		'likes'  => 5,
		'liked'  => false,
	),
	array(
		'id'     => 7,
		'author' => 'Donald',
		'avatar' => 'https://i.pravatar.cc/150?u=donald4',
		'time'   => '2 days ago',
		'text'   => 'these helpfull videos.',
		'likes'  => 1,
		'liked'  => true,
	),
);

$comment_sort_options = array(
	array(
		'label' => __( 'Newest first', 'tutor' ),
		'value' => 'newest',
	),
	array(
		'label' => __( 'Oldest first', 'tutor' ),
		'value' => 'oldest',
	),
	array(
		'label' => __( 'Most liked', 'tutor' ),
		'value' => 'liked',
	),
);

?>

<?php
tutor_load_template(
	'demo-components.learning-area.components.comments',
	array(
		'comments'       => $sample_comments_full_demo,
		'comment_total'  => count( $sample_comments_full_demo ),
		'sort_options'   => $comment_sort_options,
		'sort_value'     => 'newest',
		'show_form'      => true,
		'comments_title' => __( 'Comments', 'tutor' ),
		'form_title'     => __( 'Join The Conversation', 'tutor' ),
	)
);

