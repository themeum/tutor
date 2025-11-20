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

?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<!-- Join The Conversation Section -->
	<div class="tutor-mb-6">
		<h3 class="tutor-text-lg tutor-font-semibold tutor-mb-4">Join The Conversation</h3>
		<div class="tutor-input-field">
			<div class="tutor-input-wrapper">
				<textarea 
					placeholder="<?php esc_attr_e( 'Write your comment...', 'tutor' ); ?>"
					class="tutor-input tutor-text-area"
					rows="4"
				></textarea>
			</div>
		</div>
	</div>

	<!-- Comments Header -->
	<div class="tutor-comments-header tutor-flex tutor-items-center tutor-justify-between tutor-mb-4">
		<h3 class="tutor-comments-header__title tutor-text-lg tutor-font-semibold">
			<?php
			/* translators: %d: number of comments */
			printf( esc_html__( 'Comments (%d)', 'tutor' ), 37 );
			?>
		</h3>
		<button 
			class="tutor-comment__action-btn tutor-comment__action-btn--menu"
			type="button"
			aria-label="<?php esc_attr_e( 'Sort comments', 'tutor' ); ?>"
		>
			<?php tutor_utils()->render_svg_icon( Icon::ARROWS_OUT, 16, 16 ); ?>
		</button>
	</div>

	<!-- Comments List -->
	<div class="tutor-comments-list">
		<?php foreach ( $sample_comments_full_demo as $tutor_comment ) : ?>
			<?php
			tutor_load_template(
				'demo-components.learning-area.components.comments',
				array( 'comment' => $tutor_comment )
			);
			?>
		<?php endforeach; ?>
	</div>
</section>

