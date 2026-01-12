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

use Tutor\Components\Constants\InputType;
use Tutor\Components\InputField;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\DropdownFilter;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\PreviewTrigger;
use Tutor\Components\SearchFilter;
use Tutor\Components\Sorting;
use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Models\CourseModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$limit        = tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );

$order_filter  = Input::get( 'order', 'DESC' );
$search_filter = Input::get( 'search', '' );

// Announcement's parent.
$course_id   = Input::get( 'course-id', '' );
$date_filter = Input::get( 'date', '' );

$filter_year  = gmdate( 'Y', strtotime( $date_filter ) );
$filter_month = gmdate( 'm', strtotime( $date_filter ) );
$filter_day   = gmdate( 'd', strtotime( $date_filter ) );

$args = array(
	'post_type'      => 'tutor_announcements',
	'post_status'    => 'publish',
	's'              => sanitize_text_field( $search_filter ),
	'post_parent'    => sanitize_text_field( $course_id ),
	'posts_per_page' => sanitize_text_field( $limit ),
	'paged'          => sanitize_text_field( $current_page ),
	'orderBy'        => 'ID',
	'order'          => sanitize_text_field( $order_filter ),

);
if ( ! empty( $date_filter ) ) {
	$args['date_query'] = array(
		array(
			'year'  => $filter_year,
			'month' => $filter_month,
			'day'   => $filter_day,
		),
	);
}
if ( ! current_user_can( 'administrator' ) ) {
	$args['author'] = get_current_user_id();
}
$the_query           = new WP_Query( $args );
$announcements       = $the_query->have_posts() ? $the_query->posts : array();
$total_announcements = $the_query->found_posts;

$current_url = tutor_utils()->get_tutor_dashboard_page_permalink( 'announcements' );
$courses     = ( current_user_can( 'administrator' ) ) ? CourseModel::get_courses() : CourseModel::get_courses_by_instructor();
?>

<div class="tutor-dashboard-announcements" x-data="tutorAnnouncements()">
	<div class="tutor-surface-l1 tutor-border tutor-rounded-2xl">
		<div class="tutor-flex tutor-flex-wrap tutor-gap-4 tutor-items-center tutor-justify-between tutor-p-6 tutor-sm-p-5 tutor-border-b">
			<?php
			DropdownFilter::make()
				->courses( $courses )
				->count( $total_announcements )
				->query_arg( 'course-id' )
				->variant( Variant::PRIMARY_SOFT )
				->size( Size::X_SMALL )
				->placeholder( __( 'Search Course', 'tutor' ) )
				->search( true )
				->render();
			?>
			<button 
				type="button" 
				class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-gap-2"
				@click="openCreateModal()"
			>
				<?php tutor_utils()->render_svg_icon( Icon::ADD ); ?>
				<?php esc_html_e( 'New Announcement', 'tutor' ); ?>
			</button>
		</div>
		<div class="tutor-flex tutor-flex-wrap tutor-gap-4 tutor-items-center tutor-justify-between tutor-py-5 tutor-px-6 tutor-sm-p-5 tutor-border-b">
			<?php
			SearchFilter::make()
				->form_id( 'tutor-my-courses-search-form' )
				->placeholder( __( 'Search announcements...', 'tutor' ) )
				->action( $current_url )
				->size( Size::SMALL )
				->render();
			?>
			<div class="tutor-flex tutor-items-center tutor-gap-3">
				<button type="button" class="tutor-btn tutor-btn-outline tutor-btn-x-small tutor-btn-icon">
					<?php tutor_utils()->render_svg_icon( Icon::CALENDAR_2, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
				</button>
				<?php Sorting::make()->order( $order_filter )->render(); ?>
			</div>
		</div>

		<?php if ( empty( $announcements ) ) : ?>
			<?php EmptyState::make()->render(); ?>
		<?php else : ?>
			<div class="tutor-announcement-list">
				<?php
				foreach ( $announcements as $announcement ) :
					?>
					<div class="tutor-announcement-item">
						<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mb-5">
							<div class="tutor-flex tutor-items-center tutor-gap-3">
								<?php tutor_utils()->render_svg_icon( Icon::ANNOUNCEMENT ); ?>
								<div class="tutor-tiny tutor-text-secondary">
									<?php echo esc_html( tutor_i18n_get_formated_date( $announcement->post_date ) ); ?>
								</div>
							</div>
							<div class="tutor-announcement-actions">
								<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-sm-hidden">
									<button 
										type="button" 
										class="tutor-btn tutor-btn-secondary tutor-btn-x-small tutor-btn-icon"
										@click="openEditModal({ 
											id: <?php echo (int) $announcement->ID; ?>, 
											title: '<?php echo esc_js( $announcement->post_title ); ?>', 
											summary: '<?php echo esc_js( $announcement->post_content ); ?>', 
											course_id: <?php echo (int) $announcement->post_parent; ?> 
										})"
									>
										<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
									</button>
									<button 
										type="button" 
										class="tutor-btn tutor-btn-secondary tutor-btn-x-small tutor-btn-icon"
										@click="TutorCore.modal.showModal('tutor-announcement-delete-modal', { announcementId: <?php echo esc_html( $announcement->ID ); ?> });"
									>
										<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
									</button>
								</div>
								<!-- Mobile Popover -->
								<div x-data="tutorPopover({ placement: 'bottom-end' })" class="tutor-hidden tutor-sm-block">
									<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-btn-icon">
										<?php tutor_utils()->render_svg_icon( Icon::ELLIPSES, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
									</button>
									<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
										<div class="tutor-popover-menu" style="min-width: 104px;">
											<button 
												class="tutor-popover-menu-item"
												@click="hide(); openEditModal({ 
													id: <?php echo (int) $announcement->ID; ?>, 
													title: '<?php echo esc_js( $announcement->post_title ); ?>', 
													summary: '<?php echo esc_js( $announcement->post_content ); ?>', 
													course_id: <?php echo (int) $announcement->post_parent; ?> 
												})"
											>
												<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
												<?php esc_html_e( 'Edit', 'tutor' ); ?>
											</button>
											<button 
												class="tutor-popover-menu-item"
												@click="hide(); TutorCore.modal.showModal('tutor-announcement-delete-modal', { announcementId: <?php echo esc_html( $announcement->ID ); ?> });"
											>
												<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
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
						<div class="tutor-p2 tutor-mb-6">
							<?php echo wp_kses_post( $announcement->post_content ); ?>
						</div>
						<div class="tutor-tiny tutor-text-secondary">
							<span class="tutor-mr-4">
								<span class="tutor-mr-1"><?php esc_html_e( 'Course', 'tutor' ); ?></span>
								<?php PreviewTrigger::make()->id( $announcement->post_parent )->render(); ?>
							</span>
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
			->id( 'tutor-announcement-delete-modal' )
			->title( __( 'Delete This Announcement?', 'tutor' ) )
			->message( __( 'Are you sure you want to delete this announcement permanently? Please confirm your choice.', 'tutor' ) )
			->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
			->confirm_handler( 'handleDeleteAnnouncement(payload?.announcementId)' )
			->mutation_state( 'deleteMutation' )
			->render();
		?>
	<?php endif; ?>

	<div x-data="tutorModal({ id: 'tutor-announcement-form-modal' })" x-cloak>
		<template x-teleport="body">
			<div x-bind="getModalBindings()">
				<div x-bind="getBackdropBindings()"></div>
				<div x-bind="getModalContentBindings()" style="max-width: 480px;">
					<button x-data="tutorIcon({ name: 'cross', width: 16, height: 16})", x-bind="getCloseButtonBindings()"></button>

					<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-px-7 tutor-pt-8 tutor-pb-4">
						<?php tutor_utils()->render_svg_icon( Icon::ANNOUNCEMENT, 24, 24, array( 'class' => 'tutor-icon-brand' ) ); ?>
						<h5 class="tutor-modal-title" x-text="formTitle"></h5>
					</div>

					<?php
					$course_options = array_map(
						function( $course ) {
							return array(
								'label' => $course->post_title,
								'value' => $course->ID,
							);
						},
						$courses
					);
					?>
					<form 
						id="tutor-announcement-form"
						x-data="tutorForm({ id: 'announcement-form' })"
						x-bind="getFormBindings()"
						@submit.prevent="handleSubmit((data) => handleFormSubmit(data))($event)"
					>
						<div class="tutor-flex tutor-flex-column tutor-gap-5 tutor-p-7">
							<?php
							// Select Course.
							InputField::make()
								->type( InputType::SELECT )
								->name( 'tutor_announcement_course' )
								->label( __( 'Select Course', 'tutor' ) )
								->placeholder( __( 'Select a course', 'tutor' ) )
								->options( $course_options )
								->searchable()
								->attr( 'x-bind', "register('tutor_announcement_course', { required: 'Please select a course' })" )
								->render();

							// Announcement Title.
							InputField::make()
								->type( InputType::TEXT )
								->name( 'tutor_announcement_title' )
								->label( __( 'Announcement Title', 'tutor' ) )
								->placeholder( __( 'Announcement title', 'tutor' ) )
								->clearable()
								->attr( 'x-bind', "register('tutor_announcement_title', { required: 'Title is required' })" )
								->render();

							// Summary.
							InputField::make()
								->type( InputType::TEXTAREA )
								->name( 'tutor_announcement_summary' )
								->label( __( 'Summary', 'tutor' ) )
								->placeholder( __( 'Summary...', 'tutor' ) )
								->attr( 'rows', '5' )
								->attr( 'x-bind', "register('tutor_announcement_summary', { required: 'Summary is required' })" )
								->render();
							?>
						</div>

						<div class="tutor-modal-footer">
							<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-small" @click="TutorCore.modal.closeModal('tutor-announcement-form-modal')">
								<?php esc_html_e( 'Cancel', 'tutor' ); ?>
							</button>
							<button 
								type="submit" 
								class="tutor-btn tutor-btn-primary tutor-btn-small"
								:class="createUpdateMutation?.isPending ? 'tutor-btn-loading' : ''"
								:disabled="createUpdateMutation?.isPending"
							>
								<span x-text="formActionText"></span>
							</button>
						</div>
					</form>
				</div>
			</div>
		</template>
	</div>
</div>
