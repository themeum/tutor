<?php
/**
 * Tutor create review modal.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\InputField;
use Tutor\Components\StarRatingInput;

// Get global variables.
global $current_user_id,
$tutor_course_id;

$form_id = 'create-review-form';

?>
<div
	x-data="tutorReviewModal()"
	<?php echo ! empty( $data['clear_review_popup_data'] ) ? 'x-on:tutor-modal-closed.document="if ($event.detail.id === \'create-review-modal\') clearReviewPopupData(' . esc_js( $tutor_course_id ) . ')"' : ''; ?>
>
	<form
		class="tutor-flex tutor-flex-column tutor-gap-6"
		id="<?php echo esc_attr( $form_id ); ?>"
		x-data='tutorForm({
			id: "<?php echo esc_attr( $form_id ); ?>",
			mode: "onChange",
			defaultValues: {
				comment_post_ID: <?php echo esc_html( $tutor_course_id ); ?>,
				clear_review_popup_data: <?php echo ! empty( $data['clear_review_popup_data'] ) ? 'true' : 'false'; ?>
			},
		})'
		x-bind="getFormBindings()"
		@submit.prevent="handleSubmit(
			(data) => handleReviewSubmit(data),
		)($event)"
	>
		<div class="tutor-p-7">
			<?php
			StarRatingInput::make()
				->field_name( 'rating' )
				->current_rating( $review->rating ?? 0 )
				->view( 'emoji' )
				->attr( 'x-bind', "register('rating', { required: '" . esc_js( __( 'Rating is required', 'tutor' ) ) . "' })" )
				->render();
			?>
		</div>

		<div class="tutor-pt-7 tutor-px-7 tutor-border-t">
			<?php
			InputField::make()
				->type( 'textarea' )
				->label( __( 'Write Your Review', 'tutor' ) )
				->placeholder( __( 'Tell us about your experience. Was it a good match for you?', 'tutor' ) )
				->name( 'comment_content' )
				->required()
				->clearable()
				->attr( 'x-bind', "register('comment_content', { required: '" . esc_js( __( 'Review content is required', 'tutor' ) ) . "' })" )
				->render();
			?>
		</div>

		<div class="tutor-flex tutor-justify-between tutor-gap-6 tutor-pb-6 tutor-px-7">
			<?php
			Button::make()
				->label( __( 'Cancel', 'tutor' ) )
				->variant( Variant::SECONDARY )
				->size( Size::SMALL )
				->attr( 'x-on:click', "TutorCore.modal.closeModal('create-review-modal')" )
				->attr( 'type', 'button' )
				->attr( 'class', 'tutor-w-full' )
				->render();

			Button::make()
				->label( __( 'Submit Review', 'tutor' ) )
				->variant( Variant::PRIMARY )
				->size( Size::SMALL )
				->attr( 'type', 'submit' )
				->attr( 'class', 'tutor-w-full' )
				->attr( ':class', "{ 'tutor-btn-loading': saveRatingMutation?.isPending }" )
				->attr( ':disabled', 'saveRatingMutation?.isPending' )
				->render();
			?>
		</div>
	</form>
</div>
