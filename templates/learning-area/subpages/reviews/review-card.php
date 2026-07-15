<?php
/**
 * Tutor learning area review card.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\Avatar;
use Tutor\Components\Badge;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\InputField;
use Tutor\Components\StarRating;
use Tutor\Components\StarRatingInput;
use Tutor\Components\SvgIcon;

// Get global variables.
global $current_user_id;

$form_id                 = "review-form-{$review->comment_ID}";
$delete_modal_id         = 'review-delete-modal';
$review->comment_content = wp_kses_post( htmlspecialchars( stripslashes( $review->comment_content ?? '' ) ) );

?>
<div x-data="tutorReviewCard('<?php echo esc_attr( $review->comment_ID ); ?>')" class="tutor-border-t tutor-p-6">
	<div x-show="!isEditMode">
		<div class="tutor-flex tutor-items-center tutor-justify-between">
			<div class="tutor-flex tutor-items-center tutor-gap-4">
				<?php Avatar::make()->user( (int) $review->user_id )->size( Size::SIZE_40 )->render(); ?>
				<div class="tutor-flex tutor-flex-column">
					<div class="tutor-flex tutor-items-center tutor-gap-5">
						<div class="tutor-small"><?php echo esc_html( $review->display_name ?? '' ); ?></div>
						<?php
						if ( $current_user_id === (int) $review->user_id ) {
							Badge::make()->label( __( 'Your Review', 'tutor' ) )->variant( Badge::INFO )->render();
						}
						?>
					</div>
					<div class="tutor-small tutor-text-subdued">
					<?php
					/* translators: %s human-readable time difference. */
					echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $review->comment_date_gmt ) ) ) );
					?>
					</div>
				</div>
			</div>

			<div class="tutor-flex tutor-items-center tutor-gap-5">
				<?php
				StarRating::make()->rating( (float) ( $review->rating ?? 0 ) )->render();

				// Show pending badge for on hold reviews.
				if ( 'hold' === $review->comment_status ) {
					Badge::make()->label( __( 'Pending', 'tutor' ) )->variant( Badge::WARNING )->render();
				}
				?>
			</div>
		</div>

		<div class="tutor-p1 tutor-text-secondary tutor-mt-6">
			<?php echo esc_textarea( html_entity_decode( $review->comment_content ?? '' ) ); ?>
		</div>

		<?php if ( $current_user_id === (int) $review->user_id ) : ?>
			<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-mt-6">
				<?php
				Button::make()
					->variant( Variant::OUTLINE )
					->size( Size::SMALL )
					->label( __( 'Edit Review', 'tutor' ) )
					->icon( Icon::EDIT_2 )
					->attr( 'x-ref', 'edit' )
					->render();

				Button::make()
					->label( __( 'Delete Review', 'tutor' ) )
					->variant( Variant::OUTLINE )
					->size( Size::SMALL )
					->icon( Icon::DELETE_2 )
					->icon_only()
					->attr( 'onclick', 'TutorCore.modal.showModal(' . wp_json_encode( $delete_modal_id ) . ', { id: ' . esc_js( $review->comment_ID ) . ' })' )
					->icon_only()
					->render();
				?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $current_user_id === (int) $review->user_id ) : ?>
	<div x-show="isEditMode" x-cloak class="tutor-flex tutor-flex-column tutor-gap-6">
		<form
			class="tutor-flex tutor-flex-column tutor-gap-6"
			id="<?php echo esc_attr( $form_id ); ?>"
			x-data='tutorForm({
				id: "<?php echo esc_attr( $form_id ); ?>",
				mode: "onChange",
				defaultValues: <?php echo wp_json_encode( $review ); ?>,
			})'
			x-bind="getFormBindings()"
			@submit.prevent="handleSubmit(
				(data) => handleReviewSubmit(data),
			)($event)"
		>
			<?php
				StarRatingInput::make()
					->field_name( 'rating' )
					->current_rating( $review->rating ?? 0 )
					->render();
			?>

			<?php
				InputField::make()
					->type( 'textarea' )
					->name( 'comment_content' )
					->required()
					->clearable()
					->attr( 'x-bind', "register('comment_content', { required: '" . esc_js( __( 'Review content is required', 'tutor' ) ) . "' })" )
					->attr( '@keydown.meta.enter.prevent', 'handleSubmit((data) => handleReviewSubmit(data))' )
					->attr( '@keydown.ctrl.enter.prevent', 'handleSubmit((data) => handleReviewSubmit(data))' )
					->render();
			?>
		</form>
		<div class="tutor-flex tutor-justify-between tutor-gap-3 tutor-sm-justify-end">
			<div class="tutor-flex tutor-gap-3 tutor-tiny tutor-text-subdued tutor-items-center tutor-flex-wrap tutor-sm-hidden">
				<span>
					<?php SvgIcon::make()->name( Icon::COMMAND )->size( 12 )->render(); ?>
				</span>
				<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
				<span>
					<?php SvgIcon::make()->name( Icon::ENTER )->size( 12 )->render(); ?>
				</span>
				<?php esc_html_e( 'Enter to Save', 'tutor' ); ?>
			</div>
			<div class="tutor-flex tutor-gap-3">
				<?php
					Button::make()
						->label( __( 'Cancel', 'tutor' ) )
						->variant( Variant::GHOST )
						->size( Size::X_SMALL )
						->attr( 'x-ref', 'cancel' )
						->attr( 'type', 'button' )
						->render();
				?>
				<?php
					Button::make()
						->label( __( 'Save and Continue', 'tutor' ) )
						->variant( Variant::PRIMARY_SOFT )
						->size( Size::X_SMALL )
						->attr( 'type', 'submit' )
						->attr( 'form', $form_id )
						->attr( ':class', '{ \'tutor-btn-loading\': saveRatingMutation.isPending }' )
						->attr( ':disabled', 'saveRatingMutation.isPending' )
						->render();
				?>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
