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
use Tutor\Components\Avatar;
use Tutor\Components\Button;
use Tutor\Components\Badge;
use Tutor\Components\InputField;
use Tutor\Components\StarRating;
use Tutor\Components\StarRatingInput;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Helpers\DateTimeHelper;

defined( 'ABSPATH' ) || exit;

$default_review = array(
	'comment_ID'      => '',
	'comment_post_ID' => '',
	'course_title'    => '',
	'comment_date'    => '',
	'rating'          => 0,
	'is_bundle'       => false,
	'comment_content' => '',
	'is_editable'     => false,
	'user_avatar'     => '',
	'display_name'    => '',
);

$review = wp_parse_args( $review, $default_review );

$review['comment_content'] = wp_kses_post( htmlspecialchars( stripslashes( $review['comment_content'] ?? '' ) ) );

$form_id         = "review-form-{$review['comment_ID']}";
$delete_modal_id = 'review-delete-modal';
?>

<div class="tutor-review-card" x-data="tutorReviewCard('<?php echo esc_attr( $review['comment_ID'] ); ?>')">

	<!-- Review Display (Hidden in Edit Mode) -->
	<div x-show="!isEditMode">
		<!-- Header Section -->
		<div class="tutor-review-header">
			<!-- Type Badge with Icon -->
			<?php if ( ! empty( $review['is_bundle'] ) ) : ?>
				<?php
					Badge::make()
						->variant( Badge::HIGHLIGHT )
						->icon( Icon::BUNDLE )
						->label( __( 'Bundle', 'tutor' ) )
						->render();
				?>
			<?php else : ?>
				<?php
					Badge::make()
						->variant( Badge::INFO )
						->icon( Icon::COURSES )
						->label( __( 'Course', 'tutor' ) )
						->render();
				?>
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
				<?php if ( $review['is_editable'] ) : ?>
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
								->attr( 'onclick', 'TutorCore.modal.showModal(' . wp_json_encode( $delete_modal_id ) . ', { id: ' . esc_js( $review['comment_ID'] ) . ' })' )
								->render();
						?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Review Text -->
			<div class="tutor-review-text">
				<?php echo esc_textarea( html_entity_decode( $review['comment_content'] ?? '' ) ); ?>
			</div>

			<?php	if ( ! $review['is_editable'] ) : ?>
				<!-- Student Information -->
				<div class="tutor-review-student">
					<?php
						Avatar::make()
							->src( $review['user_avatar'] )
							->size( Size::SIZE_40 )
							->initials( $review['display_name'] )
							->render();
					?>
					<div class="tutor-flex tutor-flex-column tutor-tiny">
						<div class="tutor-font-medium">
							<?php echo esc_html( $review['display_name'] ); ?>
						</div>
						<div class="tutor-text-subdued">
							<!-- This is hard coded as only enrolled students can submit reviews -->
							<?php esc_html_e( 'Student', 'tutor' ); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Edit Form (Shown in Edit Mode) -->
	<?php if ( $review['is_editable'] ) : ?>
		<div class="tutor-review-form" x-show="isEditMode" x-cloak>
			<form
				class="tutor-review-form-fields"
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
						->current_rating( $review['rating'] ?? 0 )
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
	<?php endif; ?>
</div>
