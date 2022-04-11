<?php

function tutor_announcement_modal( $id, $title, $courses, $announcement = null ) {
	$course_id         = $announcement ? $announcement->post_parent : null;
	$announcment_id    = $announcement ? $announcement->ID : null;
	$announcment_title = $announcement ? $announcement->post_title : '';
	$summary           = $announcement ? $announcement->post_content : '';
	// Assign fallback course id
	( ! $course_id && count( $courses ) ) ? $course_id = $courses[0]->ID : 0;
	?>
	<form class="tutor-modal tutor-modal-scrollable tutor-announcements-form" id="<?php echo $id; ?>">
		<div class="tutor-modal-overlay"></div>
		<div class="tutor-modal-window">
			<div class="tutor-modal-content">
				<div class="tutor-modal-header">
					<div class="tutor-modal-title">
						<?php echo esc_html( $title ); ?>
					</div>
					<button class="tutor-modal-close tutor-iconic-btn" data-tutor-modal-close role="button">
						<span class="tutor-icon-times" area-hidden="true"></span>
					</button>
				</div>

				<div class="tutor-modal-body">
					<?php tutor_nonce_field(); ?>
					<input type="hidden" name="announcement_id" value="<?php echo esc_attr( $announcment_id ); ?>">
					<input type="hidden" name="action" value="tutor_announcement_create"/>
					<input type="hidden" name="action_type" value="<?php echo esc_attr( $announcement ? 'update' : 'create' ); ?>"/>
					<div class="tutor-mb-32">
						<label class="tutor-form-label">
							<?php esc_html_e( 'Select Course', 'tutor' ); ?>
						</label>
						<select class="tutor-form-select" name="tutor_announcement_course" required>
							<?php if ( $courses ) : ?>
								<?php foreach ( $courses as $course ) : ?>
									<option value="<?php echo esc_attr( $course->ID ); ?>" <?php selected( $course_id, $course->ID ); ?>>
										<?php echo esc_html( $course->post_title ); ?>
									</option>
								<?php endforeach; ?>
							<?php else : ?>
								<option value=""><?php esc_html_e( 'No course found', 'tutor' ); ?></option>
							<?php endif; ?>
						</select>
					</div>

					<div class="tutor-mb-32">
						<label class="tutor-form-label">
							<?php esc_html_e( 'Announcement Title', 'tutor' ); ?>
						</label>
						<input class="tutor-form-control" type="text" name="tutor_announcement_title" value="<?php echo $announcment_title; ?>" placeholder="<?php esc_html_e( 'Announcement title', 'tutor' ); ?>" maxlength="255" required>
					</div>

					<div class="tutor-mb-32">
						<label class="tutor-form-label" for="tutor_announcement_course">
							<?php esc_html_e( 'Summary', 'tutor' ); ?>
						</label>
						<textarea style="resize: unset;" class="tutor-form-control" rows="6" type="text" name="tutor_announcement_summary" placeholder="<?php esc_html_e( 'Summary...', 'tutor' ); ?>" required><?php echo esc_textarea( $summary ); ?></textarea>
					</div>

					<?php do_action( 'tutor_announcement_editor/after' ); ?>
				</div>

				<div class="tutor-modal-footer">
					<button data-tutor-modal-close type="button" data-action="back" class="tutor-btn tutor-btn-outline-primary">
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>
					<button type="submit" data-action="next" class="tutor-btn tutor-btn-primary">
						<?php esc_html_e( 'Publish', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</form>
	<?php
}

function tutor_announcement_modal_details( $id, $update_modal_id, $delete_modal_id, $announcement, $course_title, $publish_date, $publish_time ) {
	?>
	<div id="<?php echo $id; ?>" class="tutor-modal">
		<div class="tutor-modal-overlay"></div>
		<div class="tutor-modal-window">
			<div class="tutor-modal-content tutor-modal-content-white">
				<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
					<span class="tutor-icon-times" area-hidden="true"></span>
				</button>

				<div class="tutor-modal-body">
					<div class="tutor-py-20 tutor-px-24">
						<span class="tutor-round-box tutor-round-box-lg tutor-mb-32">
							<i class="tutor-icon-bullhorn" area-hidden="true"></i>
						</span>
						<div class="tutor-fs-4 tutor-fw-medium tutor-color-black tutor-mb-24"><?php echo $announcement->post_title; ?></div>
						<div class="tutor-fs-6 tutor-color-muted"><?php echo $announcement->post_content; ?></div>
					</div>

					<div class="tutor-mx-n32 tutor-my-32"><div class="tutor-hr" area-hidden="true"></div></div>

					<div class="tutor-py-20 tutor-px-24">
						<div class="tutor-row tutor-mb-60">
							<div class="tutor-col-lg-7 tutor-mb-16 tutor-mb-lg-0">
								<div class="tutor-fs-7 tutor-color-black-60">
									<?php _e( 'Course', 'tutor' ); ?>
								</div>
								<div class="tutor-fs-6 tutor-fw-bold tutor-color-black tutor-mt-4">
									<?php echo $course_title; ?>
								</div>
							</div>

							<div class="tutor-col-lg-5">
								<div class="tutor-fs-7 tutor-color-black-60">
									<?php _e( 'Published Date', 'tutor' ); ?>
								</div>
								<div class="tutor-fs-6 tutor-fw-bold tutor-color-black tutor-mt-4">
									<?php echo $publish_date . ', ' . $publish_time; ?>
								</div>
							</div>
						</div>

						<div class="tutor-row">
							<div class="tutor-col-6 tutor-col-lg-7">
								<button class="tutor-btn tutor-btn-outline-primary tutor-btn-md" data-tutor-modal-close>
									<?php _e( 'Cancel', 'tutor' ); ?>
								</button>
							</div>

							<div class="tutor-col-6 tutor-col-lg-5">
								<div class="tutor-d-flex tutor-justify-end">
									<button data-tutor-modal-target="<?php echo $delete_modal_id; ?>" class="tutor-btn tutor-btn-secondary tutor-btn-md tutor-modal-btn-delete">
										<?php _e( 'Delete', 'tutor' ); ?>
									</button>
									<button data-tutor-modal-target="<?php echo $update_modal_id; ?>" class="tutor-btn tutor-btn-primary tutor-btn-md tutor-modal-btn-edit tutor-ml-16">
										<?php _e( 'Edit', 'tutor' ); ?>
									</button>
								</div>
							</div>
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
		<div class="tutor-modal-overlay"></div>
		<div class="tutor-modal-window">
			<div class="tutor-modal-content tutor-modal-content-white">
				<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
					<span class="tutor-icon-times" area-hidden="true"></span>
				</button>

				<div class="tutor-modal-body tutor-text-center">
					<div class="tutor-mt-48">
						<img class="tutor-d-inline-block" src="<?php echo tutor()->url; ?>assets/images/icon-trash.svg" />
					</div>

					<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12"><?php _e( 'Delete This Announcement?', 'tutor' ); ?></div>
					<div class="tutor-fs-6 tutor-color-muted"><?php _e( 'Are you sure you want to delete this Announcement permanently from the site? Please confirm your choice.', 'tutor' ); ?></div>
					
					<div class="tutor-d-flex tutor-justify-center tutor-my-48">
						<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
							<?php esc_html_e('Cancel', 'tutor'); ?>
						</button>
						<button class="tutor-btn tutor-btn-primary tutor-list-ajax-action tutor-ml-20" data-request_data='{"announcement_id":<?php echo $announcment_id; ?>, "action":"tutor_announcement_delete"}' data-delete_element_id="<?php echo $row_id; ?>">
							<?php esc_html_e('Yes, Delete This', 'tutor'); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}

extract( $data );
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
							<div class="tutor-fs-7 tutor-color-black-60">
								<?php esc_html_e( 'Date', 'tutor-pro'); ?>
							</div>
						</th>
					<?php else : ?>
						<th width="17%" class="tutor-shrink">
							<span class="tutor-fs-7 tutor-color-black-60">
								<?php esc_html_e( 'Date', 'tutor'); ?>
							</span>
						</th>
					<?php endif; ?>
					<th class="tutor-table-rows-sorting">
						<div class="tutor-color-black-60 tutor-fs-7">
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
									<div class="td-datetime tutor-fs-7 tutor-color-black">
										<div class="tutor-fw-medium"><?php echo esc_html( $date_format ); ?></div>
										<div><?php echo esc_html( $time_format ); ?></div>
									</div>
								</td>
							<?php else : ?>
								<td data-th="<?php esc_html_e( 'Date', 'tutor' ); ?>" width="17%" class="tutor-nowrap-ellipsis">
									<div class="td-datetime">
										<div class="tutor-date tutor-fs-7 tutor-fw-medium tutor-color-black"><?php echo esc_html( $date_format ); ?></div>
										<div class="tutor-fs-7 tutor-color-black"><?php echo esc_html( $time_format ); ?></div>
									</div>
								</td>
							<?php endif; ?>

							<td data-th="<?php esc_html_e( 'Announcement', 'tutor' ); ?>">
								<div>
									<div class="td-course tutor-color-black tutor-fs-6 tutor-fw-medium">
										<?php echo esc_html( $announcement->post_title ); ?>
									</div>
									<div class="tutor-fs-7 tutor-fw-medium tutor-color-black-60" style="margin-top: 3px;">
										<?php esc_html_e( 'Course', 'tutor' ); ?>: <?php echo esc_html( $course ? $course->post_title : '' ); ?>
									</div>
								</div>
							</td>
							<td data-th="<?php esc_html_e( 'Action', 'tutor' ); ?>">
								<div class="tutor-d-flex tutor-align-items-center tutor-justify-end">
									<div class="tutor-d-inline-flex tutor-align-items-center td-action-btns tutor-mr-4">
										<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-announcement-details"  data-tutor-modal-target="<?php echo $details_modal_id; ?>">
											<?php esc_html_e( 'Details', 'tutor' ); ?>
										</button>
									</div>

									<div class="tutor-dropdown-parent">
										<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
											<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
										</button>
										<ul class="tutor-dropdown tutor-dropdown-dark">
											<li>
												<a href="#" class="tutor-dropdown-item" data-tutor-modal-target="<?php echo $update_modal_id; ?>">
													<i class="tutor-icon-edit tutor-mr-8" area-hidden="true"></i>
													<span><?php _e( 'Edit', 'tutor' ); ?></span>
												</a>
											</li>
											<li>
												<a href="#" class="tutor-dropdown-item" data-tutor-modal-target="<?php echo $delete_modal_id; ?>">
													<i class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></i>
													<span><?php _e( 'Delete', 'tutor' ); ?></span>
												</a>
											</li>
										</ul>
									</div>
								</div>

								<?php
									$course_title = isset($course->post_title)?$course->post_title:'';
									tutor_announcement_modal( $update_modal_id, __( 'Edit Announcement', 'tutor' ), $courses, $announcement );
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
		// 'previous_text' => '<span class="tutor-icon-angle-left"></span>',
		// 'next_text' => '<span class="tutor-icon-angle-right"></span>'
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
