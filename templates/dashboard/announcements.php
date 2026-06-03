<?php
/**
 * Template for displaying Announcements
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.7.9
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Announcements;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use Tutor\Components\Button;
use Tutor\Components\CourseFilter;
use Tutor\Components\DateFilter;
use Tutor\Components\EmptyState;
use Tutor\Components\InputField;
use Tutor\Components\Pagination;
use Tutor\Components\PreviewTrigger;
use Tutor\Components\SearchFilter;
use Tutor\Components\Sorting;
use Tutor\Components\Constants\Variant;
use Tutor\Components\Constants\Color;
use Tutor\Components\SvgIcon;

$limit        = (int) tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );

$order_filter  = Input::get( 'order', 'DESC' );
$search_filter = Input::get( 'search', '' );

// Announcement's parent.
$course_id  = Input::get( 'course-id', 0, Input::TYPE_INT );
$start_date = Input::get( 'start_date', '' );
$end_date   = Input::get( 'end_date', '' );

$args = array(
	's'              => $search_filter,
	'posts_per_page' => sanitize_text_field( $limit ),
	'paged'          => $current_page,
	'orderBy'        => 'ID',
	'order'          => sanitize_text_field( $order_filter ),
);

if ( $course_id ) {
	$args['post_parent'] = $course_id;
}

if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
	$args['date_query'] = array(
		array(
			'before'    => $end_date,
			'after'     => $start_date,
			'inclusive' => true,
		),
	);
}
if ( ! current_user_can( 'administrator' ) ) {
	$args['author'] = get_current_user_id();
}
$the_query           = Announcements::get_announcements( $args );
$announcements       = $the_query->have_posts() ? $the_query->posts : array();
$total_announcements = $the_query->found_posts;

$current_url = tutor_utils()->get_tutor_dashboard_page_permalink( 'announcements' );
$courses     = ( current_user_can( 'administrator' ) ) ? CourseModel::get_courses() : CourseModel::get_courses_by_instructor();

$form_id         = 'tutor-announcement-form';
$delete_modal_id = 'tutor-announcement-delete-modal';
$create_modal_id = 'tutor-announcement-form-modal';
?>

<div 
	class="tutor-dashboard-announcements"
	x-data="tutorAnnouncements({
		formId: '<?php echo esc_attr( $form_id ); ?>',
		deleteModalId: '<?php echo esc_attr( $delete_modal_id ); ?>',
		createModalId: '<?php echo esc_attr( $create_modal_id ); ?>',
	})"
>
	<div class="tutor-hidden tutor-sm-flex tutor-items-center tutor-justify-between tutor-mb-5">
		<h4 class="tutor-h4"><?php esc_html_e( 'Announcements', 'tutor' ); ?></h4>
		<?php
		Button::make()
			->label( __( 'New Announcement', 'tutor' ) )
			->size( Size::SMALL )
			->icon( Icon::ADD )
			->icon_only()
			->attr( '@click', 'openCreateModal()' )
			->render();
		?>
	</div>
	<div class="tutor-surface-l1 tutor-border tutor-rounded-2xl tutor-overflow-hidden">
		<div class="tutor-flex tutor-flex-wrap tutor-gap-4 tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-sm-p-5 tutor-border-b">
			<?php
				CourseFilter::make()
					->size( Size::SMALL )
					->courses( $courses )
					->count( $total_announcements )
					->render();

				DateFilter::make()
						->type( DateFilter::TYPE_RANGE )
						->placement( DateFilter::PLACEMENT_BOTTOM_END )
						->hide_initial_label()
						->attr( 'class', 'tutor-hidden tutor-sm-flex' )
						->render();

				Button::make()
					->label( __( 'New Announcement', 'tutor' ) )
					->size( Size::SMALL )
					->icon( Icon::ADD )
					->attr( '@click', 'openCreateModal()' )
					->attr( 'class', 'tutor-force-sm-hidden' )
					->render();
			?>
		</div>
		<div class="tutor-flex tutor-flex-wrap tutor-gap-4 tutor-items-center tutor-justify-between tutor-py-5 tutor-px-6 tutor-sm-p-5 tutor-border-b">
			<?php
			SearchFilter::make()
				->form_id( 'tutor-my-courses-search-form' )
				->size( Size::SMALL )
				->placeholder( __( 'Search announcements...', 'tutor' ) )
				->action( $current_url )
				->size( Size::SMALL )
				->attr( 'class', 'tutor-sm-flex-1' )
				->render();
			?>
			<div class="tutor-flex tutor-items-center tutor-gap-3">
				<?php
					DateFilter::make()
						->type( DateFilter::TYPE_RANGE )
						->placement( DateFilter::PLACEMENT_BOTTOM_END )
						->hide_initial_label()
						->attr( 'class', 'tutor-sm-hidden' )
						->render();

					Sorting::make()
						->order( $order_filter )
						->size( Size::SMALL )
						->render();
				?>
			</div>
		</div>

		<?php if ( empty( $announcements ) ) : ?>
			<?php
				EmptyState::make()
					->title( __( 'No Announcements Found', 'tutor' ) )
					->icon( tutor_utils()->get_themed_svg( 'images/illustrations/no-announcements.svg' ) )
					->render();
			?>
		<?php else : ?>
			<div class="tutor-announcement-list">
				<?php
				foreach ( $announcements as $announcement ) :
					?>
					<div class="tutor-announcement-item">
						<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mb-5">
							<div class="tutor-flex tutor-items-center tutor-gap-3">
								<?php SvgIcon::make()->name( Icon::ANNOUNCEMENT )->color( Color::IDLE )->render(); ?>
								<div class="tutor-tiny tutor-text-secondary">
									<?php echo esc_html( tutor_i18n_get_formated_date( $announcement->post_date ) ); ?>
								</div>
							</div>
							<div class="tutor-announcement-actions">
								<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-sm-hidden">
									<?php
										Button::make()
											->label( __( 'Edit', 'tutor' ) )
											->size( Size::X_SMALL )
											->variant( Variant::SECONDARY )
											->icon( Icon::EDIT_2 )
											->icon_only()
											->attr(
												'@click',
												sprintf(
													'openEditModal(%s)',
													wp_json_encode(
														array(
															'id'        => (int) $announcement->ID,
															'title'     => $announcement->post_title,
															'summary'   => $announcement->post_content,
															'course_id' => (int) $announcement->post_parent,
														)
													)
												)
											)
											->render();

										Button::make()
											->label( __( 'Delete', 'tutor' ) )
											->size( Size::X_SMALL )
											->variant( Variant::SECONDARY )
											->icon( Icon::DELETE_2 )
											->icon_only()
											->attr( '@click', "TutorCore.modal.showModal('$delete_modal_id', { announcementId: $announcement->ID });" )
											->render();
									?>
								</div>
								<!-- Mobile Popover -->
								<div x-data="tutorPopover({ placement: 'bottom-end' })" class="tutor-hidden tutor-sm-block">
									<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon" aria-label="<?php esc_attr_e( 'Announcement actions', 'tutor' ); ?>">
										<?php SvgIcon::make()->name( Icon::ELLIPSES )->size( 16 )->color( Color::SECONDARY )->render(); ?>
									</button>
									<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
										<div class="tutor-popover-menu" style="min-width: 104px;">
											<button 
												class="tutor-popover-menu-item"
												@click="hide(); openEditModal(
												<?php
												echo esc_attr(
													wp_json_encode(
														array(
															'id'        => (int) $announcement->ID,
															'title'     => $announcement->post_title,
															'summary'   => $announcement->post_content,
															'course_id' => (int) $announcement->post_parent,
														)
													)
												);
												?>
												)"
											>
												<?php SvgIcon::make()->name( Icon::EDIT_2 )->render(); ?>
												<?php esc_html_e( 'Edit', 'tutor' ); ?>
											</button>
											<button 
												class="tutor-popover-menu-item"
												@click="hide(); TutorCore.modal.showModal('<?php echo esc_attr( $delete_modal_id ); ?>', { announcementId: <?php echo esc_html( $announcement->ID ); ?> });"
											>
												<?php SvgIcon::make()->name( Icon::DELETE_2 )->render(); ?>
												<?php esc_html_e( 'Delete', 'tutor' ); ?>
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="tutor-medium tutor-font-medium tutor-mb-4">
							<?php echo esc_html( $announcement->post_title ); ?>
						</div>
						<div class="tutor-p2 tutor-mb-6 tutor-whitespace-pre-line">
							<?php echo wp_kses_post( $announcement->post_content ); ?>
						</div>
						<div class="tutor-tiny tutor-text-secondary tutor-flex tutor-items-center tutor-gap-2">
							<span class="tutor-flex-shrink-0"><?php esc_html_e( 'Course', 'tutor' ); ?></span>
							<?php PreviewTrigger::make()->id( $announcement->post_parent )->render(); ?>
						</div>
					</div>
					<?php
				endforeach;
				?>
			</div>
		<?php endif; ?>

		<?php if ( $total_announcements > $limit ) : ?>
		<div class="tutor-p-6 tutor-sm-p-5 tutor-border-t">
			<?php Pagination::make()->current( $current_page )->total( $total_announcements )->limit( $limit )->render(); ?>
		</div>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $announcements ) ) : ?>
		<?php
		ConfirmationModal::make()
			->id( $delete_modal_id )
			->title( __( 'Delete This Announcement?', 'tutor' ) )
			->message( __( 'Are you sure you want to delete this announcement permanently? Please confirm your choice.', 'tutor' ) )
			->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
			->confirm_handler( 'handleDeleteAnnouncement(payload?.announcementId)' )
			->mutation_state( 'deleteMutation' )
			->render();
		?>
	<?php endif; ?>

	<div x-data="tutorModal({ id: '<?php echo esc_attr( $create_modal_id ); ?>', isCloseable: false })" x-cloak>
		<template x-teleport="body">
			<div x-bind="getModalBindings()">
				<div x-bind="getBackdropBindings()"></div>
				<div x-bind="getModalContentBindings()" style="max-width: 480px;">
					<button x-data="tutorIcon({ name: 'cross', width: 16, height: 16})", x-bind="getCloseButtonBindings()"></button>

					<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-px-7 tutor-py-6 tutor-border-b">
						<?php SvgIcon::make()->name( Icon::ANNOUNCEMENT )->size( 24 )->color( Color::BRAND )->render(); ?>
						<h5 class="tutor-modal-title" x-text="formTitle"></h5>
					</div>

					<?php
					$course_options = array_map(
						function ( $course ) {
							return array(
								'label' => $course->post_title,
								'value' => $course->ID,
							);
						},
						$courses
					);
					?>
					<form 
						id="<?php echo esc_attr( $form_id ); ?>"
						x-data="tutorForm({ id: '<?php echo esc_attr( $form_id ); ?>' })"
						x-bind="getFormBindings()"
						@submit.prevent="handleSubmit((data) => handleFormSubmit(data))($event)"
					>
						<div class="tutor-flex tutor-flex-column tutor-gap-5 tutor-p-7">
							<?php
							InputField::make()
								->type( InputType::SELECT )
								->name( 'tutor_announcement_course' )
								->label( __( 'Select Course', 'tutor' ) )
								->placeholder( __( 'Select a course', 'tutor' ) )
								->options( $course_options )
								->searchable()
								->attr( 'x-bind', "register('tutor_announcement_course', { required: 'Please select a course' })" )
								->render();

							InputField::make()
								->type( InputType::TEXT )
								->name( 'tutor_announcement_title' )
								->label( __( 'Announcement Title', 'tutor' ) )
								->placeholder( __( 'Announcement title', 'tutor' ) )
								->clearable()
								->attr( 'x-bind', "register('tutor_announcement_title', { required: 'Title is required' })" )
								->render();

							InputField::make()
								->type( InputType::TEXTAREA )
								->name( 'tutor_announcement_summary' )
								->label( __( 'Description', 'tutor' ) )
								->placeholder( __( 'Write a short announcement description.', 'tutor' ) )
								->attr( 'rows', '5' )
								->attr( 'x-bind', "register('tutor_announcement_summary', { required: 'Summary is required' })" )
								->render();
							?>

							<?php do_action( 'tutor_announcement_editor/after' ); ?>
						</div>

						<div class="tutor-modal-footer">
							<?php
								Button::make()
									->label( __( 'Cancel', 'tutor' ) )
									->size( Size::SMALL )
									->variant( Variant::SECONDARY )
									->attr( 'type', 'button' )
									->attr( '@click', sprintf( 'TutorCore.modal.closeModal("%s")', $create_modal_id ) )
									->render();

								Button::make()
									->label( __( 'Publish', 'tutor' ) )
									->size( Size::SMALL )
									->variant( Variant::PRIMARY )
									->attr( ':class', "createUpdateMutation?.isPending ? 'tutor-btn-loading' : ''" )
									->attr( ':disabled', 'createUpdateMutation?.isPending' )
									->attr(
										'@click',
										'handleSubmit((data) => handleFormSubmit(data))($event)'
									)
									->attr( 'x-text', 'formActionText' )
									->render();
								?>
							
						</div>
					</form>
				</div>
			</div>
		</template>
	</div>
</div>
