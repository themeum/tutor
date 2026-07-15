<?php
/**
 * Course Review Card single item component.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Components\Avatar;
use Tutor\Components\Button;
use Tutor\Components\Badge;
use Tutor\Components\InputField;
use Tutor\Components\Popover;
use Tutor\Components\StarRating;
use Tutor\Components\StarRatingInput;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Color;
use Tutor\Components\Constants\Positions;
use Tutor\Components\Constants\Variant;
use Tutor\Helpers\DateTimeHelper;

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
	'comment_status'  => '',
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
			<div class="tutor-flex tutor-align-center">
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
			</div>

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
				<div class="tutor-flex tutor-items-center tutor-gap-5">
					<?php
					StarRating::make()->rating( (float) ( $review['rating'] ?? 0 ) )->render();

					// Show pending badge for on hold reviews.
					if ( 'hold' === $review['comment_status'] ) {
						Badge::make()->label( __( 'Pending', 'tutor' ) )->variant( Badge::WARNING )->render();
					}
					?>
				</div>

				<!-- Actions -->
				<?php if ( $review['is_editable'] ) : ?>
					<div class="tutor-flex tutor-review-actions tutor-sm-hidden">
						<?php
							Button::make()
								->label( __( 'Edit Review', 'tutor' ) )
								->variant( Variant::SECONDARY )
								->size( Size::X_SMALL )
								->icon( SvgIcon::make()->name( Icon::EDIT_2 )->get() )
								->icon_only()
								->attr( 'x-ref', 'edit' )
								->render();
						?>
						<?php
							Button::make()
								->label( __( 'Delete Review', 'tutor' ) )
								->variant( Variant::SECONDARY )
								->size( Size::X_SMALL )
								->icon( SvgIcon::make()->name( Icon::DELETE_2 )->get() )
								->icon_only()
								->attr( 'onclick', 'TutorCore.modal.showModal(' . wp_json_encode( $delete_modal_id ) . ', { id: ' . esc_js( $review['comment_ID'] ) . ' })' )
								->render();
						?>
					</div>
					<div class="tutor-review-actions tutor-hidden tutor-sm-flex">
						<?php
							Popover::make()
								->placement( Positions::BOTTOM_END )
								->menu_min_width( '104px' )
								->menu_item(
									array(
										'tag'     => 'button',
										'icon'    => SvgIcon::make()->name( Icon::EDIT_2 )->size( 20 )->get(),
										'content' => __( 'Edit', 'tutor' ),
										'attr'    => array(
											'@click' => '$refs.edit.click(); hide()',
										),
									)
								)
								->menu_item(
									array(
										'tag'     => 'button',
										'icon'    => SvgIcon::make()->name( Icon::DELETE_2 )->size( 20 )->get(),
										'content' => __( 'Delete', 'tutor' ),
										'attr'    => array(
											'@click' => "hide(); TutorCore.modal.showModal('{$delete_modal_id}', { id: " . (int) $review['comment_ID'] . ' })',
										),
									)
								)
								->trigger(
									Button::make()
										->label( __( 'Review Actions', 'tutor' ) )
										->variant( Variant::GHOST )
										->size( Size::X_SMALL )
										->icon( SvgIcon::make()->name( Icon::ELLIPSES )->size( 16 )->color( Color::SECONDARY )->get() )
										->icon_only()
										->attr( 'x-ref', 'trigger' )
										->attr( '@click', 'toggle()' )
										->attr( 'type', 'button' )
										->get()
								)
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
			<div class="tutor-flex tutor-justify-between tutor-sm-justify-end tutor-gap-3">
				<div class="tutor-flex tutor-gap-3 tutor-tiny tutor-text-subdued tutor-items-center tutor-sm-hidden">
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
