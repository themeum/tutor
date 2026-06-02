<?php
/**
 * QnA replies list template.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;
use Tutor\Components\Sorting;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;

?>
<?php if ( ! empty( $replies ) ) : ?>
<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-t" :class="{ 'tutor-loading-spinner': loadingReplies }">
	<div class="tutor-small tutor-text-secondary">
		<?php esc_html_e( 'Replies', 'tutor' ); ?>
		<span class="tutor-text-primary tutor-font-medium">(<?php echo (int) count( $replies ); ?>)</span>
	</div>
	<?php
	Sorting::make()
		->order( $replies_order )
		// ->on_change( 'reloadReplies' )
		// ->bind_active_order( 'repliesOrder' )
		->render();
	?>
</div>

<div class="tutor-discussion-single-reply-list tutor-border-t">
	<?php foreach ( $replies as $reply ) : ?>
		<div class="tutor-discussion-reply-list-item">
			<div class="tutor-flex tutor-gap-5 tutor-w-full" x-show="editingId !== <?php echo (int) $reply->comment_ID; ?>">
				<?php Avatar::make()->user( $reply->user_id )->size( Size::SIZE_40 )->render(); ?>
				<div class="tutor-flex-1">
					<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
						<span class="tutor-discussion-card-author">
							<?php echo esc_html( $reply->comment_author ); ?>
						</span>
						<span class="tutor-text-secondary">
							<?php
								/* translators: %s human-readable time difference. */
								echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $reply->comment_date_gmt ) ) ) );
							?>
						</span>
					</div>
					<div class="tutor-p2 tutor-text-secondary" id="tutor-qna-text-<?php echo (int) $reply->comment_ID; ?>">
						<?php echo wp_kses_post( $reply->comment_content ); ?>
					</div>
				</div>
				<?php if ( $user_id === (int) $reply->user_id ) : ?>
				<div x-data="tutorPopover({ placement: 'bottom-end' })" class="tutor-ml-auto">
					<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
						<?php SvgIcon::make()->name( Icon::ELLIPSES )->size( 16 )->color( Color::SECONDARY )->render(); ?>
					</button>
					<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
						<div class="tutor-popover-menu" style="min-width: 110px;">
							<button class="tutor-popover-menu-item tutor-gap-5" @click="setEditing(<?php echo (int) $reply->comment_ID; ?>); hide()">
								<?php SvgIcon::make()->name( Icon::EDIT_2 )->size( 20 )->render(); ?>
								<?php esc_html_e( 'Edit', 'tutor' ); ?>
							</button>
							<button 
								class="tutor-popover-menu-item tutor-gap-5" 
								@click="TutorCore.modal.showModal('tutor-qna-delete-modal', { question_id: <?php echo esc_html( $reply->comment_ID ); ?>, context: 'reply' }); hide()">
								<?php SvgIcon::make()->name( Icon::DELETE_2 )->size( 20 )->render(); ?>
								<?php esc_html_e( 'Delete', 'tutor' ); ?>
							</button>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</div>

			<?php if ( $user_id === (int) $reply->user_id ) : ?>
				<div x-show="editingId === <?php echo (int) $reply->comment_ID; ?>" x-cloak class="tutor-mt-5 tutor-w-full">
					<?php
					tutor_load_template(
						'learning-area.subpages.qna.form',
						array(
							'form_id'             => 'qna-edit-' . (int) $reply->comment_ID,
							'default_value'       => $reply->comment_content,
							'submit_handler'      => '(data) => updateQnAMutation?.mutate({ ...data, question_id: ' . (int) $reply->comment_ID . ' })',
							'cancel_handler'      => 'setEditing(null)',
							'is_pending'          => 'updateQnAMutation?.isPending',
							'placeholder'         => __( 'Write your answer', 'tutor' ),
							'keep_footer_visible' => true,
						)
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
<?php endif; ?>
