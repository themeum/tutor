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
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Sorting;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use Tutor\Components\Constants\Variant;
use TUTOR\User;

?>
<?php if ( ! empty( $replies ) ) : ?>
<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-t" :class="{ 'tutor-loading-spinner': loadingReplies }">
	<div class="tutor-small tutor-text-secondary">
		<?php esc_html_e( 'Replies', 'tutor' ); ?>
		<span class="tutor-text-primary tutor-font-medium">(<?php echo esc_html( count( $replies ) ); ?>)</span>
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
			<div class="tutor-flex tutor-gap-5 tutor-w-full" x-show="editingId !== <?php echo esc_attr( $reply->comment_ID ); ?>">
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
					<div class="tutor-p2 tutor-text-secondary" id="tutor-qna-text-<?php echo esc_attr( $reply->comment_ID ); ?>">
						<?php echo wp_kses_post( $reply->comment_content ); ?>
					</div>
				</div>
				<?php
				$can_edit   = $user_id === (int) $reply->user_id;
				$can_delete = User::is_instructor_view() || $user_id === (int) $reply->user_id;
				$has_menu   = $can_edit || $can_delete;
				?>
				<?php if ( $has_menu ) : ?>
					<div x-data="tutorPopover({ placement: 'bottom-end' })" class="tutor-ml-auto">
						<?php
						Button::make()
							->label( __( 'More options', 'tutor' ) )
							->variant( Variant::GHOST )
							->size( Size::X_SMALL )
							->icon( Icon::ELLIPSES, 'left', Size::SIZE_16, Color::SECONDARY )
							->icon_only()
							->attr( 'x-ref', 'trigger' )
							->attr( '@click', 'toggle()' )
							->render();
						?>
						<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
							<div class="tutor-popover-menu" style="min-width: 110px;">
								<?php if ( $can_edit ) : ?>
									<button class="tutor-popover-menu-item tutor-gap-5" @click="setEditing(<?php echo esc_attr( $reply->comment_ID ); ?>, 'qna'); hide()">
										<?php SvgIcon::make()->name( Icon::EDIT_2 )->size( 20 )->render(); ?>
										<?php esc_html_e( 'Edit', 'tutor' ); ?>
									</button>
								<?php endif; ?>
								<?php if ( $can_delete ) : ?>
									<button
										class="tutor-popover-menu-item tutor-gap-5"
										@click="TutorCore.modal.showModal('tutor-qna-delete-modal', { question_id: <?php echo esc_attr( $reply->comment_ID ); ?>, context: 'reply' }); hide()"
									>
										<?php SvgIcon::make()->name( Icon::DELETE_2 )->size( 20 )->render(); ?>
										<?php esc_html_e( 'Delete', 'tutor' ); ?>
									</button>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $can_edit ) : ?>
				<div x-show="editingId === <?php echo esc_attr( $reply->comment_ID ); ?>" x-cloak class="tutor-mt-5 tutor-w-full">
					<?php
					tutor_load_template(
						'dashboard.discussions.qna-form',
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
