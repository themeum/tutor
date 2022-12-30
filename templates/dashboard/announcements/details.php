<?php
/**
 * Announcements Details Page
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Announcements
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

?>
<!--details announcements modal-->
<div class="tutor-modal-wrap tutor-announcements-modal-wrap  tutor-accouncement-details-modal">
	<div class="tutor-modal-content tutor-announcement-modal-content tutor-announcement-details-modal-content">
		<a href="#" class="tutor-announcement-close-btn">
			<i class="tutor-icon-times"></i>
		</a>
		<div class="tutor-modal-container tutor-announcement-details-container">
			<div class="tutor-announcement-big-icon">
				<i class="tutor-icon-bullhorn"></i>
			</div>
			<div class="tutor-announcement-detail-content">
			</div>
		</div>
		<div class="tutor-detail-course-content-wrap">
			<div class="tutor-detail-course-content">

				<div class="tutor-detail-course-info-wrap">
					<div class="tutor-announcement-detail-course-info">
						<label for=""><?php esc_html_e( 'Course', 'tutor' ); ?></label>
						<p></p>
					</div>
					<div class="tutor-announcement-detail-date-info">
						<label for=""><?php esc_html_e( 'Publish Date' ); ?></label>
						<p></p>
					</div>
				</div>
				<div class="tutor-announce-detail-popup-button-wrap">
					<div class="announcement-detail-cancel-button">
						<button type="button" class="quiz-modal-tab-navigation-btn  quiz-modal-btn-cancel tutor-announcement-close-btn tutor-announcement-cancel-btn"><?php esc_html_e( 'Cancel', 'tutor' ); ?></button>
					</div>
					<div class="announcement-detail-edit-delete-button">
						<button class="tutor-btn tutor-announcement-delete tutor-border-none" id="tutor-announcement-delete-from-detail"><?php esc_html_e( 'Delete', 'tutor' ); ?></button>
						<button class="tutor-btn tutor-border-none tutor-announcement-edit" id="tutor-announcement-edit-from-detail"><?php esc_html_e( 'Edit', 'tutor' ); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!--details announcements modal end-->
