<?php
/**
 * Course Review Card single item component.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Components\Button;
use Tutor\Components\InputField;
use Tutor\Components\StarRating;
use Tutor\Components\StarRatingInput;
use Tutor\Helpers\DateTimeHelper;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

$default_review = array(
	'comment_ID'      => '',
	'comment_post_ID' => '',
	'course_title'    => '',
	'comment_date'    => '',
	'rating'          => 0,
	'is_bundle'       => false,
	'comment_content' => '',
);

$review = wp_parse_args( $review, $default_review );

$form_id         = "review-form-{$review['id']}";
$delete_modal_id = 'review-delete-modal';
?>

<div class="tutor-review-card" x-data="tutorReviewCard('<?php echo esc_attr( $review['id'] ); ?>')">

	<!-- Review Display (Hidden in Edit Mode) -->
	<div x-show="!isEditMode">
		<!-- Header Section -->
		<div class="tutor-review-header">
			<!-- Type Badge with Icon -->
			<?php if ( ! empty( $review['is_bundle'] ) ) : ?>
				<div class="tutor-badge tutor-badge-exception tutor-badge-circle">
					<?php tutor_utils()->render_svg_icon( Icon::BUNDLE, 16, 16 ); ?>
					<span class="tutor-text-sm tutor-font-medium">
						<?php esc_html_e( 'Bundle', 'tutor' ); ?>
					</span>
				</div>
			<?php else : ?>
				<div class="tutor-badge tutor-badge-primary-soft tutor-badge-circle">
					<?php tutor_utils()->render_svg_icon( Icon::COURSES, 16, 16 ); ?>
					<span class="tutor-text-sm tutor-font-medium">
						<?php esc_html_e( 'Course', 'tutor' ); ?>
					</span>
				</div>
			<?php endif; ?>

			<!-- Course Title -->
			<div class="tutor-review-title">
				<?php echo esc_html( $review['course_title'] ?? '' ); ?>
			</div>

			<!-- Review Date -->
			<div class="tutor-review-date">
				<?php
					printf(
						// translators: %s - Review date.
						esc_html__( 'Reviewed on: %s', 'tutor' ),
						esc_html( DateTimeHelper::get_gmt_to_user_timezone_date( $review['comment_date'], get_option( 'date_format' ) ) ?? '' )
					);
					?>
			</div>
		</div>

		<!-- Divider -->
		<hr class="tutor-section-separator" />

		<!-- Review Content -->
		<div class="tutor-review-content">
			<!-- Actions and Rating -->
			<div class="tutor-review-rating-wrapper">
				<!-- Rating -->
					<?php
						StarRating::make()
							->rating( $review['rating'] ?? 0 )
							->render();
					?>

				<!-- Actions -->
				<div class="tutor-review-actions">
					<?php
						Button::make()
							->label( __( 'Edit Review', 'tutor' ) )
							->variant( Variant::SECONDARY )
							->size( Size::X_SMALL )
							->icon( tutor_utils()->get_svg_icon( Icon::EDIT_2 ) )
							->icon_only()
							->attr( 'x-ref', 'edit' )
							->render();
					?>
					<?php
						Button::make()
							->label( __( 'Delete Review', 'tutor' ) )
							->variant( Variant::SECONDARY )
							->size( Size::X_SMALL )
							->icon( tutor_utils()->get_svg_icon( Icon::DELETE_2 ) )
							->icon_only()
							->attr( 'onclick', 'TutorCore.modal.showModal(' . wp_json_encode( $delete_modal_id ) . ', { id: ' . esc_js( $review['id'] ) . ' })' )
							->render();
					?>
				</div>
			</div>

			<!-- Review Text -->
			<div class="tutor-review-text">
				<?php echo esc_textarea( htmlspecialchars( stripslashes( $review['review_content'] ?? '' ) ) ); ?>
			</div>
		</div>
	</div>

	<!-- Edit Form (Shown in Edit Mode) -->
	<div class="tutor-review-form" x-show="isEditMode" x-cloak>
		<form
			class="tutor-review-form-fields"
			id="<?php echo esc_attr( $form_id ); ?>"
			x-data='tutorForm({
				id: "<?php echo esc_attr( $form_id ); ?>",
				mode: "onBlur",
				defaultValues: <?php echo wp_json_encode( $review ); ?>,
			})'
			x-bind="getFormBindings()"
			@submit.prevent="handleSubmit(
				(data) => handleReviewSubmit(data),
			)($event)"
		>
			<?php
				StarRatingInput::make()
					->fieldName( 'rating' )
					->currentRating( $review['rating'] ?? 0 )
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
		<div class="tutor-flex tutor-justify-between tutor-gap-3">
			<div class="tutor-flex tutor-gap-3 tutor-tiny tutor-text-subdued tutor-items-center">
				<span>
					<?php tutor_utils()->render_svg_icon( Icon::COMMAND, 12, 12 ); ?>
				</span>
				<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
				<span>
					<?php tutor_utils()->render_svg_icon( Icon::ENTER, 12, 12 ); ?>
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
</div>
