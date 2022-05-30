<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$reviews = tutor_utils()->get_reviews_by_user(null, 0, 10, false, null, array('approved', 'hold'));
$available_status = array(
	'approved' => array(__( 'Published', 'tutor' ), 'select-success'),
	'hold' => array(__( 'Unpublished', 'tutor' ), 'select-warning'),
);

?>


<div class="tutor-admin-wrap">
	<div class="tutor-admin-body">
		<div class="tutor-mt-24">
			<div class="tutor-table-responsive">
                <table class="tutor-table tutor-table-middle table-dashboard-review-list">
					<thead class="tutor-text-sm tutor-text-400">
						<tr>
							<th class="tutor-table-rows-sorting" width="30%">
								<?php esc_html_e( 'Student', 'tutor' ); ?>
								<span class="a-to-z-sort-icon tutor-icon-ordering-a-z"></span>
							</th>
							<th width="13%">
								<?php esc_html_e( 'Date', 'tutor' ); ?>
							</th>
							<th width="13%">
								<?php esc_html_e( 'Course', 'tutor' ); ?>
							</th>
							<th width="6%">
								<?php esc_html_e( 'Feedback', 'tutor' ); ?>
							</th>
							<th></th>
						</tr>
					</thead>
                    <tbody>
                        <?php foreach($reviews as $review): ?>
                            <tr>
                                <td>
									<div class="tutor-d-flex tutor-align-center tutor-gap-1">
										<?php echo tutor_utils()->get_tutor_avatar( $review->user_id ); ?>
										<span>
											<?php esc_html_e( $review->display_name ); ?>
										</span>
										<a href="<?php echo esc_url( tutor_utils()->profile_url( $review->user_id, false ) ); ?>" class="tutor-iconic-btn" target="_blank">
											<span class="tutor-icon-external-link" area-hidden="True"></span>
										</a>
									</div>
                                </td>

                                <td>
                                    <span class="tutor-fs-7">
										<?php echo tutor_utils()->get_local_time_from_unix( $review->comment_date ); ?>
									</span>
                                </td>

                                <td>
                                    <a class="tutor-table-link" href="#">
                                        <?php echo esc_html( $review->course_title ); ?>
                                    </a>
                                </td>

                                <td>
                                    <?php 
                                    	tutor_utils()->star_rating_generator_v2( $review->rating, null, true );
                                    ?>
                                    <div class="tutor-fs-6 tutor-color-muted">
                                        <?php echo $review->comment_content; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="tutor-d-flex tutor-align-center tutor-justify-end tutor-gap-2">
                                        <div class="tutor-form-select-with-icon <?php echo $available_status[$review->comment_status][1]; ?>">
                                            <select title="<?php esc_attr_e( 'Update course status', 'tutor' ); ?>" class="tutor-table-row-status-update" data-id="<?php echo esc_attr( $review->comment_ID ); ?>" data-status="<?php echo esc_attr( $review->comment_status ); ?>" data-status_key="status" data-action="tutor_change_review_status">
                                                <?php foreach ( $available_status as $key => $value ) : ?>
                                                    <option data-status_class="<?php echo esc_attr( $value[1] ); ?>" value="<?php echo $key; ?>" <?php selected( $key, $review->comment_status, 'selected' ); ?>>
                                                        <?php echo esc_html( $value[0] ); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <i class="icon1 tutor-icon-eye-bold"></i>
                                            <i class="icon2 tutor-icon-angle-down"></i>
                                        </div>
                                        <div class="tutor-dropdown-parent">
                                            <button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
                                                <span class="tutor-icon-kebab-menu" area-hidden="true"></span>
                                            </button>
                                            <div id="table-dashboard-review-list-<?php echo esc_attr( $review->comment_ID ); ?>" class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
                                                <a class="tutor-dropdown-item" href="<?php echo esc_url( get_permalink( $review->comment_post_ID ) ); ?>" target="_blank">
                                                    <i class="tutor-icon-edit tutor-mr-8" area-hidden="true"></i>
                                                    <span><?php esc_html_e( 'Preview', 'tutor' ); ?></span>
                                                </a>
                                                <a href="javascript:void(0)" class="tutor-dropdown-item tutor-admin-review-delete" data-tutor-modal-target="tutor-common-confirmation-modal" data-id="<?php echo esc_attr( $review->comment_ID ); ?>">
                                                    <i class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></i>
                                                    <span><?php esc_html_e( 'Delete', 'tutor' ); ?></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php tutor_load_template_from_custom_path( tutor()->path . 'views/elements/common-confirm-popup.php' );