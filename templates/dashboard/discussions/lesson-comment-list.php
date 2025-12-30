<?php
/**
 * Lesson comments list template.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Pagination;
use Tutor\Components\Sorting;
use TUTOR\Icon;
use TUTOR\Lesson;

$lesson_ids = get_posts(
	array(
		'post_type'      => tutor()->lesson_post_type,
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

$list_args = array(
	'post__in' => $lesson_ids,
	'status'   => 'approve',
	'parent'   => 0,
	'number'   => $item_per_page,
	'offset'   => $offset,
	'orderby'  => 'comment_date',
	'order'    => $order_filter,
);

$count_args = array(
	'post__in' => $lesson_ids,
	'status'   => 'approve',
	'parent'   => 0,
	'count'    => true,
);

$lesson_comments = Lesson::get_comments( $list_args );
$total_items     = Lesson::get_comments( $count_args );
?>

<div class="tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
	<div class="tutor-small tutor-text-secondary">
		<?php esc_html_e( 'Comments', 'tutor' ); ?>
		<span class="tutor-text-primary tutor-font-medium">(<?php echo esc_html( $total_items ); ?>)</span>
	</div>
	<div class="tutor-qna-filter-right">
		<?php Sorting::make()->order( $order_filter )->render(); ?>
	</div>
</div>

<div class="tutor-flex tutor-flex-column tutor-gap-4 tutor-p-6">
	<?php
	foreach ( $lesson_comments as $lesson_comment ) :
		tutor_load_template(
			'dashboard.discussions.lesson-comment-card',
			array(
				'lesson_comment' => $lesson_comment,
				'discussion_url' => $discussion_url,
			)
		);
	endforeach;
	?>
</div>
<div class="tutor-px-6 tutor-pb-6">
	<?php if ( $total_items > $item_per_page ) : ?>
		<?php Pagination::make()->current( $current_page )->total( $total_items )->limit( $item_per_page )->render(); ?>
	<?php endif; ?>
</div>

<?php if ( ! empty( $lesson_comments ) ) : ?>
<div x-data="tutorModal({ id: 'tutor-comment-delete-modal' })" x-cloak>
	<template x-teleport="body">
		<div x-bind="getModalBindings()">
			<div x-bind="getBackdropBindings()"></div>
			<div x-bind="getModalContentBindings()" style="max-width: 426px;">
				<button x-data="tutorIcon({ name: 'cross', width: 16, height: 16})", x-bind="getCloseButtonBindings()"></button>

				<div class="tutor-p-7 tutor-pt-10 tutor-flex tutor-flex-column tutor-items-center">
					<?php tutor_utils()->render_svg_icon( Icon::BIN, 100, 100 ); ?>
					<h5 class="tutor-h5 tutor-font-medium tutor-mt-8">
						<?php esc_html_e( 'Delete This Comment?', 'tutor' ); ?>
					</h5>
					<p class="tutor-p3 tutor-text-secondary tutor-mt-2 tutor-text-center">
						<?php esc_html_e( 'All the replies also will be deleted.', 'tutor' ); ?>
					</p>
				</div>

				<div class="tutor-modal-footer">
					<button class="tutor-btn tutor-btn-ghost tutor-btn-small" @click="TutorCore.modal.closeModal('tutor-comment-delete-modal')">
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>
					<button 
						class="tutor-btn tutor-btn-destructive tutor-btn-small"
						:class="deleteCommentMutation?.isPending ? 'tutor-btn-loading' : ''"
						@click="handleDeleteComment(payload?.commentId)"
						:disabled="deleteCommentMutation?.isPending"
					>
						<?php esc_html_e( 'Yes, Delete This', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</template>
</div>
<?php endif; ?>
