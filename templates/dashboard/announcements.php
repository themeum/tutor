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

use Tutor\Components\Constants\Size;
use Tutor\Components\CourseFilter;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\PreviewTrigger;
use Tutor\Components\SearchFilter;
use Tutor\Components\Sorting;
use TUTOR\Icon;
use TUTOR\Input;

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

$year  = gmdate( 'Y', strtotime( $date_filter ) );
$month = gmdate( 'm', strtotime( $date_filter ) );
$day   = gmdate( 'd', strtotime( $date_filter ) );

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
			'year'  => $year,
			'month' => $month,
			'day'   => $day,
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
?>

<div class="tutor-dashboard-announcements" x-data="tutorAnnouncements()">
	<div class="tutor-surface-l1 tutor-border tutor-rounded-2xl">
		<div class="tutor-flex tutor-flex-wrap tutor-gap-4 tutor-items-center tutor-justify-between tutor-p-6 tutor-sm-p-5 tutor-border-b">
			<?php CourseFilter::make()->count( $total_announcements )->button_class( 'tutor-btn tutor-btn-primary-soft tutor-btn-small' )->render(); ?>
			<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-gap-2">
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
											<button class="tutor-popover-menu-item">
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
	<div x-data="tutorModal({ id: 'tutor-announcement-delete-modal' })" x-cloak>
		<template x-teleport="body">
			<div x-bind="getModalBindings()">
				<div x-bind="getBackdropBindings()"></div>
				<div x-bind="getModalContentBindings()" style="max-width: 426px;">
					<button x-data="tutorIcon({ name: 'cross', width: 16, height: 16})", x-bind="getCloseButtonBindings()"></button>

					<div class="tutor-p-7 tutor-pt-10 tutor-flex tutor-flex-column tutor-items-center">
						<?php tutor_utils()->render_svg_icon( Icon::BIN, 100, 100 ); ?>
						<h5 class="tutor-h5 tutor-font-medium tutor-mt-8">
							<?php esc_html_e( 'Delete This Announcement?', 'tutor' ); ?>
						</h5>
						<p class="tutor-p3 tutor-text-secondary tutor-mt-2 tutor-text-center">
							<?php esc_html_e( 'Are you sure you want to delete this announcement permanently? Please confirm your choice.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-modal-footer">
						<button class="tutor-btn tutor-btn-ghost tutor-btn-small" @click="TutorCore.modal.closeModal('tutor-announcement-delete-modal')">
							<?php esc_html_e( 'Cancel', 'tutor' ); ?>
						</button>
						<button 
							class="tutor-btn tutor-btn-destructive tutor-btn-small"
							:class="deleteMutation?.isPending ? 'tutor-btn-loading' : ''"
							@click="handleDeleteAnnouncement(payload?.announcementId)"
							:disabled="deleteMutation?.isPending"
						>
							<?php esc_html_e( 'Yes, Delete This', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
		</template>
	</div>
	<?php endif; ?>
</div>
