<?php

function tutor_announcement_modal( $id, $title, $courses, $announcement = null ) {
	$course_id         = $announcement ? $announcement->post_parent : null;
	$announcment_id    = $announcement ? $announcement->ID : null;
	$announcment_title = $announcement ? $announcement->post_title : '';
	$summary           = $announcement ? $announcement->post_content : '';

	// Assign fallback course id
	( ! $course_id && count( $courses ) ) ? $course_id = $courses[0]->ID : 0;
	?>
	<form class="tutor-modal modal-sticky-header-footer tutor-announcements-form" id="<?php echo $id; ?>">
		<span class="tutor-modal-overlay"></span>
		<div class="tutor-modal-root">
			<div class="tutor-modal-inner">
				<div class="tutor-modal-header">
					<div class="tutor-fs-6 tutor-fw-bold tutor-color-black-70 tutor-modal-title">
						<?php echo $title; ?>
					</div>
					<button data-tutor-modal-close class="tutor-modal-close">
						<span class="tutor-icon-line-cross-line"></span>
					</button>
				</div>

				<div class="tutor-modal-body-alt modal-container">
					<?php tutor_nonce_field(); ?>
					<input type="hidden" name="announcement_id" value="<?php echo $announcment_id; ?>">
					<input type="hidden" name="action" value="tutor_announcement_create"/>
					<input type="hidden" name="action_type" value="<?php echo $announcement ? 'update' : 'create'; ?>"/>
					<div class="tutor-form-group tutor-mb-20 tutor-form-group-select-md">
						<label class="tutor-form-label">
							<?php _e( 'Select Course', 'tutor' ); ?>
						</label>
						<select class="tutor-form-select tutor-form-control" name="tutor_announcement_course" required>
							<?php if ( $courses ) : ?>
								<?php foreach ( $courses as $course ) : ?>
									<option value="<?php echo esc_attr( $course->ID ); ?>" <?php selected( $course_id, $course->ID ); ?>>
										<?php echo esc_html( $course->post_title ); ?>
									</option>
								<?php endforeach; ?>
							<?php else : ?>
								<option><?php esc_html_e( 'No course found', 'tutor' ); ?></option>
							<?php endif; ?>
						</select>
					</div>
					<div class="tutor-form-group">
						<label class="tutor-form-label">
							<?php _e( 'Announcement Title', 'tutor' ); ?>
						</label>
						<input class="tutor-form-control" type="text" name="tutor_announcement_title" value="<?php echo $announcment_title; ?>" placeholder="<?php _e( 'Announcement title', 'tutor' ); ?>" maxlength="255" required>
					</div>
					<div class="tutor-form-group">
						<label class="tutor-form-label" for="tutor_announcement_course">
							<?php _e( 'Summary', 'tutor' ); ?>
						</label>
						<textarea style="resize: unset;" class="tutor-form-control" rows="6" type="text" name="tutor_announcement_summary" placeholder="<?php _e( 'Summary...', 'tutor' ); ?>" required><?php
							echo $summary;
						?></textarea>
					</div>

					<?php do_action( 'tutor_announcement_editor/after' ); ?>
				</div>

				<div class="tutor-modal-footer tutor-d-flex tutor-justify-content-between">
					<button type="submit" data-action="next" class="tutor-btn tutor-is-primary">
						<?php _e( 'Publish', 'tutor' ); ?>
					</button>
					<button data-tutor-modal-close type="button" data-action="back" class="tutor-btn tutor-is-default">
						<?php _e( 'Cancel', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</form>
	<?php
}

function tutor_announcement_modal_details( $id, $update_modal_id, $delete_modal_id, $announcement, $course_title, $publish_date, $publish_time ) {
	// TODO: Update this modal with new modal variation from Saif
	?>
	<div id="<?php echo $id; ?>" class="tutor-modal modal-view-double-segment">
		<span class="tutor-modal-overlay"></span>

		<div class="tutor-modal-root">
			<div class="tutor-modal-inner">
				<div class="tutor-modal-header tutor-fs-6 tutor-fw-bold tutor-color-black-70">
					<button data-tutor-modal-close class="tutor-modal-close tutor-color-muted">
						<span class="tutor-icon-line-cross-line tutor-icon-40"></span>
					</button>
				</div>
				<div class="tutor-modal-body tutor-align-items-start">
					<div class="view-announcement-icon bg-primary-40 tutor-color-brand-wordpress">
						<span class="tutor-icon-speaker-filled tutor-color-design-brand"></span>
					</div>
					<div class="tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mt-32 tutor-pr-lg-4">
						<?php echo $announcement->post_title; ?>
					</div>
					<div class="text-regular-body tutor-color-muted tutor-mt-20">
						<?php echo $announcement->post_content; ?>
					</div>
				</div>
				<div class="tutor-modal-footer">
					<div class="footer-top">
						<div class="">
							<div class="tutor-fs-7 tutor-fw-normal tutor-color-black-60">
								<?php _e( 'Course', 'tutor' ); ?>
							</div>
							<div class="text-bold-body tutor-color-black tutor-mt-4">
								<?php echo $course_title; ?>
							</div>
						</div>
						<div class="">
							<div class="text-regular-caption tutor-color-black-60">
								<?php _e( 'Publised Date', 'tutor' ); ?>
							</div>
							<div class="text-bold-body tutor-color-black tutor-mt-4">
								<?php echo $publish_date . ', ' . $publish_time; ?>
							</div>
						</div>
					</div>
					<div class="footer-bottom tutor-mt-sm-60 tutor-mt-20">
						<div class="footer-btns">
							<button data-tutor-modal-close class="tutor-btn tutor-btn-disable tutor-no-hover tutor-btn-md tutor-d-none tutor-d-sm-block tutor-modal-btn-cancel">
								<?php _e( 'Cancel', 'tutor' ); ?>
							</button>
						</div>
						<div class="footer-btns announcement-action-button tutor-d-flex tutor-justify-content-end tutor-flex-sm-wrap tutor-flex-md-nowrap">
							<button data-tutor-modal-target="<?php echo $delete_modal_id; ?>" class="tutor-btn tutor-btn-disable-outline tutor-btn-outline-fd tutor-btn-md tutor-modal-btn-delete">
								<?php _e( 'Delete', 'tutor' ); ?>
							</button>
							<button data-tutor-modal-target="<?php echo $update_modal_id; ?>" class="tutor-btn tutor-btn-md tutor-modal-btn-edit <?php echo is_admin() ? 'tutor-btn-wordpress' : 'tutor-btn-primary'; ?>">
								<?php _e( 'Edit', 'tutor' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function tutor_announcement_modal_delete( $id, $announcment_id, $row_id ) {
	?>
	<div id="<?php echo $id; ?>" class="tutor-modal">
		<span class="tutor-modal-overlay"></span>
		<div class="tutor-modal-root">
			<div class="tutor-modal-inner tutor-modal-close-inner">
				<button data-tutor-modal-close class="tutor-modal-close">
					<span class="tutor-icon-line-cross-line"></span>
				</button>
				<div class="tutor-modal-body tutor-text-center">
					<div class="tutor-modal-icon">
						<img src="<?php echo tutor()->url; ?>assets/images/icon-trash.svg" />
					</div>
					<div class="tutor-modal-text-wrap">
						<h3 class="tutor-modal-title">
							<?php _e( 'Delete This Announcement?', 'tutor' ); ?>
						</h3>
						<p>
							<?php _e( 'Are you sure you want to delete this Announcement permanently from the site? Please confirm your choice.', 'tutor' ); ?>
						</p>
					</div>
					<div class="tutor-modal-btns tutor-btn-group">
						<button data-tutor-modal-close class="tutor-btn tutor-is-outline tutor-is-default">
							<?php _e( 'Cancel', 'tutor' ); ?>
						</button>
						<button class="tutor-btn <?php echo is_admin() ? 'tutor-btn-wordpress' : ''; ?> tutor-list-ajax-action"  data-request_data='{"announcement_id":<?php echo $announcment_id; ?>, "action":"tutor_announcement_delete"}' data-delete_element_id="<?php echo $row_id; ?>">
							<?php _e( 'Yes, Delete This', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}

extract( $data ); // $announcements, $the_query, $paged
$courses = ( current_user_can( 'administrator' ) ) ? tutor_utils()->get_courses() : tutor_utils()->get_courses_by_instructor();
?>

<!-- Now Load The View -->
	<div class="tutor-ui-table-wrapper">
		<table class="tutor-ui-table tutor-ui-table-responsive frontend-dashboard-announcement-table">
			<thead>
				<tr>
					<?php if ( is_admin() ) : ?>
						<th width="2%">
							<div class="tutor-d-flex">
								<input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input">
							</div>
						</th>
						<th width="17%">
							<div class="tutor-fs-7 tutor-fw-normal tutor-color-black-60">
								<?php esc_html_e( 'Date', 'tutor-pro'); ?>
							</div>
						</th>
					<?php else : ?>
						<th width="17%" class="tutor-shrink">
							<span class="tutor-fs-7 tutor-fw-normal tutor-color-black-60">
								<?php esc_html_e( 'Date', 'tutor'); ?>
							</span>
						</th>
					<?php endif; ?>
					<th class="tutor-table-rows-sorting">
						<div class="tutor-color-black-60 tutor-fs-7 tutor-fw-normal">
							<?php esc_html_e( 'Announcements', 'tutor' ); ?>
						</div>
					</th>
					<th class="tutor-shrink"></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( is_array( $announcements ) && count( $announcements ) ) : ?>
					<?php foreach ( $announcements as $announcement ) : ?>
						<?php
							$course      = get_post( $announcement->post_parent );
							$date_format = tutor_get_formated_date( get_option( 'date_format' ), $announcement->post_date );
							$time_format = tutor_get_formated_date( get_option( 'time_format' ), $announcement->post_date );

							$update_modal_id  = 'tutor_announcement_' . $announcement->ID;
							$details_modal_id = $update_modal_id . '_details';
							$delete_modal_id  = $update_modal_id . '_delete';
							$row_id           = 'tutor-announcement-tr-' . $announcement->ID;
						?>
						<tr id="<?php echo $row_id; ?>">
							<?php if ( is_admin() ) : ?>
								<td data-th="<?php esc_html_e( 'Select', 'tutor' ); ?>" class="v-align-top">
									<div class="tutor-form-check">
										<input
											id="tutor-admin-list-<?php esc_attr_e( $announcement->ID ); ?>"
											type="checkbox"
											class="tutor-form-check-input tutor-bulk-checkbox"
											name="tutor-bulk-checkbox-all"
											value="<?php esc_attr_e( $announcement->ID ); ?>"
										/>
									</div>
								</td>
								<td data-th="<?php esc_html_e( 'Date', 'tutor' ); ?>" width="17%" class="v-align-top">
									<div class="td-datetime">
										<div class="tutor-fs-8 tutor-fw-medium tutor-color-black"><?php echo esc_html( $date_format ); ?></div>
										<div class="tutor-fs-7 tutor-fw-normal tutor-color-black"><?php echo esc_html( $time_format ); ?></div>
									</div>
								</td>
							<?php else : ?>
								<td data-th="<?php esc_html_e( 'Date', 'tutor' ); ?>" width="17%" class="tutor-text-nowrap">
									<div class="td-datetime">
										<div class="tutor-date tutor-fs-8 tutor-fw-medium tutor-color-black"><?php echo esc_html( $date_format ); ?></div>
										<div class="tutor-fs-7 tutor-fw-normal tutor-color-black"><?php echo esc_html( $time_format ); ?></div>
									</div>
								</td>
							<?php endif; ?>

							<td data-th="<?php esc_html_e( 'Announcement', 'tutor' ); ?>">
								<div>
									<div class="td-course tutor-color-black tutor-fs-6 tutor-fw-medium">
										<?php echo esc_html( $announcement->post_title ); ?>
									</div>
									<div class="tutor-fs-8 tutor-fw-medium tutor-color-black-60" style="margin-top: 3px;">
										<?php esc_html_e( 'Course', 'tutor' ); ?>: <?php echo esc_html( $course ? $course->post_title : '' ); ?>
									</div>
								</div>
							</td>
							<td data-th="<?php esc_html_e( 'Action', 'tutor' ); ?>">
								<div class="tutor-d-flex tutor-align-items-center tutor-justify-content-end">
									<div class="inline-flex-center td-action-btns tutor-mr-4">
										<button class="tutor-btn <?php echo is_admin() ? 'tutor-btn-wordpress-outline' : 'tutor-btn-disable-outline tutor-btn-outline-fd'?> tutor-btn-sm tutor-announcement-details"  data-tutor-modal-target="<?php echo $details_modal_id; ?>">
											<?php esc_html_e( 'Details', 'tutor' ); ?>
										</button>
									</div>

									<div class="tutor-popup-opener">
										<button type="button" class="popup-btn" data-tutor-popup-target="<?php echo $update_modal_id; ?>_action">
											<span class="toggle-icon"></span>
										</button>
										<ul class="popup-menu" id="<?php echo $update_modal_id; ?>_action">
											<li>
												<a href="#" data-tutor-modal-target="<?php echo $update_modal_id; ?>">
													<i class="tutor-icon-edit-filled tutor-color-design-white"></i>
													<span class="text-regular-body tutor-color-white"><?php _e( 'Edit', 'tutor' ); ?></span>
												</a>
											</li>
											<li>
												<a href="#" data-tutor-modal-target="<?php echo $delete_modal_id; ?>">
													<i class="tutor-icon-delete-fill-filled tutor-color-design-white"></i>
													<span class="text-regular-body tutor-color-white"><?php _e( 'Delete', 'tutor' ); ?></span>
												</a>
											</li>
										</ul>
									</div>
								</div>

								<?php
									$course_title = isset($course->post_title)?$course->post_title:'';
									tutor_announcement_modal( $update_modal_id, __( 'Edit Announcment', 'tutor' ), $courses, $announcement );
									tutor_announcement_modal_details( $details_modal_id, $update_modal_id, $delete_modal_id, $announcement, $course_title, $date_format, $time_format );
									tutor_announcement_modal_delete( $delete_modal_id, $announcement->ID, $row_id );
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="100%" class="column-empty-state">
							<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<div class="tutor-pagination-wrapper tutor-mt-40 <?php echo esc_attr( is_admin() ? 'tutor-mt-20' : '' ); ?>">
	<?php
		// Need an unlikely integer.
		// $big = 999999999;
		// $pages = paginate_links( array(
		// 'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
		// 'format'    => '?current_page=%#%',
		// 'current'   => $paged,
		// 'total'     => $the_query->max_num_pages,
		// 'type'      => 'array',
		// 'previous_text' => '<span class="tutor-icon-angle-left-filled"></span>',
		// 'next_text' => '<span class="tutor-icon-angle-right-filled"></span>'
		// ));

		/**
		 * Prepare pagination data & load template
		 */
		$limit           = tutor_utils()->get_option( 'pagination_per_page' );
		if($the_query->found_posts > $limit) {
			$pagination_data = array(
				'total_items' => $the_query->found_posts,
				'per_page'    => $limit,
				'paged'       => $paged,
			);

			$pagination_template = tutor()->path . 'views/elements/pagination.php';
			if ( is_admin() ) {
				tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
			} else {
				$pagination_template_frontend = tutor()->path . 'templates/dashboard/elements/pagination.php';
				tutor_load_template_from_custom_path( $pagination_template_frontend, $pagination_data );
			}
		}

		?>
	</div>



<?php
	tutor_announcement_modal( 'tutor_announcement_new', __( 'Create Announcement' ), $courses );
?>
