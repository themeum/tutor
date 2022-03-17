<?php
/**
 * Instructors List Template.
 *
 * @package Instructors List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $_GET['sub_page'] ) ) {
	$page = sanitize_text_field( $_GET['sub_page'] );
	include_once tutor()->path . "views/pages/{$page}.php";
	return;
}

use TUTOR\Instructors_List;
$instructors = new Instructors_List();

/**
 * Short able params
 */
$user_id   = isset( $_GET['user_id'] ) ? $_GET['user_id'] : '';
$course_id = isset( $_GET['course-id'] ) ? $_GET['course-id'] : '';
$order     = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
$date      = isset( $_GET['date'] ) ? $_GET['date'] : '';
$search    = isset( $_GET['search'] ) ? $_GET['search'] : '';

/**
 * Determine active tab
 */
$active_tab = isset( $_GET['data'] ) && $_GET['data'] !== '' ? esc_html__( $_GET['data'] ) : 'all';

/**
 * Pagination data
 */
$paged    = ( isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) && $_GET['paged'] >= 1 ) ? $_GET['paged'] : 1;
$per_page = tutor_utils()->get_option( 'pagination_per_page' );
$offset   = ( $per_page * $paged ) - $per_page;

// Available status for instructor.
$instructor_status = array( 'approved', 'pending', 'blocked' );
if ( 'pending' === $active_tab ) {
	$instructor_status = array( 'pending' );
} elseif ( 'blocked' === $active_tab ) {
	$instructor_status = array( 'blocked' );
} elseif ( 'approved' == $active_tab ) {
	$instructor_status = array( 'approved' );
}
$instructors_list = tutor_utils()->get_instructors( $offset, $per_page, $search, $course_id, $date, $order, $instructor_status );
$total            = tutor_utils()->get_total_instructors( $search, $instructor_status, $course_id, $date );

/**
 * Navbar data to make nav menu
 */
$url               = get_pagenum_link();
$add_insructor_url = $url . '&sub_page=add_new_instructor';
$navbar_data       = array(
	'page_title'   => $instructors->page_title,
	'tabs'         => $instructors->tabs_key_value( $search, $course_id, $date ),
	'active'       => $active_tab,
	'add_button'   => true,
	'button_title' => __( 'Add New', 'tutor' ),
	'button_url'   => $add_insructor_url,
	'modal_target' => 'tutor-instructor-add-new',
);

$filters = array(
	'bulk_action'   => $instructors->bulk_action,
	'bulk_actions'  => $instructors->prpare_bulk_actions(),
	'ajax_action'   => 'tutor_instructor_bulk_action',
	'filters'       => true,
	'course_filter' => true,
);

?>

<?php
	/**
	 * Load Templates with data.
	 */
	$navbar_template  = tutor()->path . 'views/elements/navbar.php';
	$filters_template = tutor()->path . 'views/elements/filters.php';
	tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
	tutor_load_template_from_custom_path( $filters_template, $filters );
	$available_status = array(
		'pending'  => array( __( 'Pending', 'tutor' ), 'select-warning' ),
		'approved' => array( __( 'Approved', 'tutor' ), 'select-success' ),
		'blocked'  => array( __( 'Blocked', 'tutor' ), 'select-danger' ),
	);
	?>

<div class="wrap">
	<div class="tutor-ui-table-responsive tutor-mt-32">
		<table class="tutor-ui-table tutor-ui-table-responsive table-instructors tutor-table-with-checkbox">
			<thead>
			<tr>
				<th width="3%">
					<div class="tutor-d-flex">
						<input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input" />
					</div>
				</th>
				<th class="tutor-table-rows-sorting">
					<div class=" tutor-color-black-60">
						<span class="text-regular-small tutor-ml-5"> <?php esc_html_e( 'Name', 'tutor' ); ?></span>
						<span class="tutor-icon-ordering-a-to-z-filled a-to-z-sort-icon tutor-icon-22"></span>
					</div>
				</th>
				<th class="tutor-table-rows-sorting">
					<div class=" tutor-color-black-60">
						<span class="text-regular-small"><?php esc_html_e( 'Email', 'tutor' ); ?></span>
						<span class="tutor-icon-order-down-filled up-down-icon"></span>
					</div>
				</th>
				<th class="tutor-table-rows-sorting">
					<div class=" tutor-color-black-60">
						<span class="text-regular-small"><?php esc_html_e( 'Total Course', 'tutor' ); ?></span>
						<span class="tutor-icon-order-down-filled up-down-icon"></span>
					</div>
				</th>
				<th class="tutor-table-rows-sorting">
					<div class=" tutor-color-black-60">
						<span class="text-regular-small">
							<?php esc_html_e( 'Commission Rate', 'tutor' ); ?>
						</span>
						<span class="tutor-icon-order-down-filled up-down-icon"></span>
					</div>
				</th>
				<th class="tutor-table-rows-sorting">
				<div class=" tutor-color-black-60">
					<span class="text-regular-small"><?php esc_html_e( 'Status', 'tutor' ); ?></span>
					<span class="tutor-icon-order-down-filled up-down-icon"></span>
				</div>
				</th>
				<th class="tutor-shrink"></th>
			</tr>
			</thead>
			<tbody>
				<?php if ( is_array( $instructors_list ) && count( $instructors_list ) ) : ?>
					<?php
					foreach ( $instructors_list as $list ) :
						$alert = ( 'pending' === $list->status ? 'warning' : ( 'approved' === $list->status ? 'success' : ( 'blocked' === $list->status ? 'danger' : 'default' ) ) );
						?>
						<tr>
							<td data-th="<?php esc_html_e( 'Checkbox', 'tutor' ); ?>">
								<div class="td-checkbox tutor-d-flex ">
									<input id="tutor-admin-list-<?php esc_attr_e( $list->ID ); ?>" type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $list->ID ); ?>" />
								</div>
							</td>
							<td data-th="<?php esc_html_e( 'Avatar', 'tutor' ); ?>" class="column-fullwidth">
								<div class="td-avatar">
								<?php $avatar_url = get_avatar_url( $list->ID ); ?>
									<img src="<?php echo esc_url( $avatar_url ); ?>" alt="student avatar"/>
									<span class="tutor-color-black tutor-fs-6 tutor-fw-medium">
										<?php echo esc_html( $list->display_name ); ?>
									</span>
									<a href="<?php echo esc_url( tutor_utils()->profile_url( $list->ID, true ) ); ?>" class="btn-text btn-detail-link tutor-color-design-dark" target="_blank">
										<span class="tutor-icon-detail-link-filled tutor-mt-4"></span>
									</a>
								</div>
							</td>
							<td data-th="<?php esc_html_e( 'Email', 'tutor' ); ?>">
								<span class="tutor-color-black tutor-fs-7 tutor-fw-normal">
							<?php echo esc_html( $list->user_email ); ?>
								</span>
							</td>
							</td>
							<td data-th="<?php esc_html_e( 'Total Course', 'tutor' ); ?>">
								<span class="tutor-color-black tutor-fs-7 tutor-fw-normal">
							<?php echo esc_html( $instructors->column_total_course( $list, 'total_course' ) ); ?>
								</span>
							</td>
							<td data-th="<?php esc_html_e( 'Commission Rate', 'tutor' ); ?>">
								<span class="tutor-color-black tutor-fs-7 tutor-fw-normal">
								<?php echo esc_html( tutor_utils()->get_option( 'earning_instructor_commission' ) . '%' ); ?>
								</span>
							</td>
							<td data-th="<?php esc_html_e( 'Status', 'tutor' ); ?>">
								<div class="tutor-form-select-with-icon <?php echo esc_html( $available_status[ $list->status ][1] ); ?>">
									<select class="tutor-table-row-status-update" data-bulk-ids="<?php echo esc_attr( $list->ID ); ?>" data-status_key="bulk-action" data-action="tutor_instructor_bulk_action">
										<?php foreach ( $available_status as $key => $status ) : ?>
											<option data-status_class="<?php echo $available_status[ $key ][1]; ?>" value="<?php echo esc_attr( $key ); ?>" data-status="<?php echo esc_attr( $key ); ?>" <?php selected( $list->status, $key ); ?>>
												<?php echo esc_html( $available_status[ $key ][0] ); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<i class="icon1 tutor-icon-eye-fill-filled"></i>
									<i class="icon2 tutor-icon-angle-down-filled"></i>
								</div>
							</td>
							<td data-th="<?php esc_html_e( 'Status', 'tutor' ); ?>">
								<a href="<?php echo esc_url( add_query_arg( 'user_id', $list->ID, self_admin_url( 'user-edit.php' ) ) ); ?>" class="tutor-btn tutor-btn-wordpress tutor-btn-disable-outline tutor-btn-sm">
									<?php esc_html_e( 'Edit', 'tutor' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="100%" class="column-empty-state">
								<?php tutor_utils()->tutor_empty_state( __( 'No instructor found', 'tutor' ) ); ?>
							</td>
						</tr>
				<?php endif; ?>
			</tbody>
		</table>
		</div>
	<div class="tutor-admin-page-pagination-wrapper tutor-mt-48">
		<?php
			/**
			 * Prepare pagination data & load template
			 */
		if ( $total > $per_page ) {
			$pagination_data     = array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'paged'       => $paged,
			);
			$pagination_template = tutor()->path . 'views/elements/pagination.php';
			tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
		}
		?>
	</div>
</div>

<div id="tutor-instructor-add-new" class="tutor-modal modal-sticky-header-footer tutor-modal-is-close-inside-header">
  <span class="tutor-modal-overlay"></span>
  <div class="tutor-modal-root">
	<div class="tutor-modal-inner">
	<form action="" method="post" id="tutor-new-instructor-form" autocomplete="off">
	  <div class="tutor-modal-header">
		<div class="tutor-modal-title tutor-fs-6 tutor-fw-bold tutor-color-black-70">
			<?php esc_html_e( 'Add New Instructor', 'tutor' ); ?>
		</div>
		<button data-tutor-modal-close class="tutor-modal-close">
			<span class="tutor-icon-line-cross-line"></span>
		</button>
	  </div>
		  <div class="tutor-modal-body-alt tutor-bg-gray-10">

				<?php tutor_nonce_field(); ?>
				<?php do_action( 'tutor_add_new_instructor_form_fields_before' ); ?>
				<div class="tutor-row ">
					<div class="tutor-col">
						<label class="tutor-form-label">
							<?php esc_html_e( 'First Name', 'tutor' ); ?>
						</label>
						<div class="tutor-input-group tutor-mb-16">
							<input type="text" name="first_name" class="tutor-form-control tutor-mb-12" placeholder="<?php echo esc_attr( 'Enter First Name', 'tutor' ); ?>" pattern="[a-zA-Z0-9-\s]+" title="<?php esc_attr_e( 'Only alphanumeric & space are allowed', 'tutor' ); ?>" required/>
						</div>
					</div>
					<div class="tutor-col">
						<label class="tutor-form-label">
							<?php esc_html_e( 'Last Name', 'tutor' ); ?>
						</label>
						<div class="tutor-input-group tutor-mb-16">
							<input type="text" name="last_name" class="tutor-form-control tutor-mb-12" placeholder="<?php echo esc_attr( 'Enter Last Name', 'tutor' ); ?>" pattern="[a-zA-Z0-9-\s]+" title="<?php esc_attr_e( 'Only alphanumeric & space are allowed', 'tutor' ); ?>" required/>
						</div>
					</div>
				</div>
				<div class="tutor-row ">
					<div class="tutor-col">
						<label class="tutor-form-label">
							<?php esc_html_e( 'User Name', 'tutor' ); ?>
						</label>
						<div class="tutor-input-group tutor-mb-16">
							<input type="text" name="user_login" class="tutor-form-control tutor-mb-12" autocomplete="off" placeholder="<?php echo esc_attr( 'Enter Your Name', 'tutor' ); ?>" pattern="^[a-zA-Z0-9_]*$" title="<?php esc_attr_e( 'Only alphanumeric and underscore are allowed', 'tutor' ); ?>" required/>
						</div>
					</div>
					<div class="tutor-col">
						<label class="tutor-form-label">
							<?php esc_html_e( 'Phone Number', 'tutor' ); ?>
						</label>
						<div class="tutor-input-group tutor-mb-16">
							<input type="text" name="phone_number"  class="tutor-form-control tutor-mb-12" placeholder="<?php echo esc_attr( 'Enter Phone Number', 'tutor' ); ?>" minlength="8" maxlength="16" pattern="[0-9]+" title="<?php esc_attr_e( 'Only number is allowed', 'tutor' ); ?>" required/>
						</div>
					</div>
				</div>
				<div class="tutor-row ">
					<div class="tutor-col">
						<label class="tutor-form-label">
							<?php esc_html_e( 'Email Address', 'tutor' ); ?>
						</label>
						<div class="tutor-input-group tutor-mb-16">
							<input type="email" name="email"  class="tutor-form-control tutor-mb-12" autocomplete="off" placeholder="<?php echo esc_attr( 'Enter Your Email', 'tutor' ); ?>" required/>
						</div>
					</div>
				</div>
				<div class="tutor-row ">
					<div class="tutor-col">
						<label class="tutor-form-label">
							<?php esc_html_e( 'Password', 'tutor' ); ?>
						</label>
						<div class="tutor-input-group tutor-form-control-has-icon-right tutor-mb-16">
							<span class="tutor-icon-eye-filled tutor-input-group-icon-right tutor-password-reveal"></span>
							<input type="password" name="password" id="tutor-instructor-pass"  class="tutor-form-control tutor-mb-12" minlength="8" placeholder="*******" autocomplete="new-password" required/>
						</div>
					</div>
					<div class="tutor-col">
						<label class="tutor-form-label">
							<?php esc_html_e( 'Retype Password', 'tutor' ); ?>
						</label>
						<div class="tutor-input-group tutor-form-control-has-icon-right tutor-mb-16">
							<span class="tutor-icon-eye-filled tutor-input-group-icon-right tutor-password-reveal"></span>
							<input type="password" name="password_confirmation"  class="tutor-form-control tutor-mb-12" placeholder="*******" autocomplete="off" pattern="" title="<?php esc_attr_e( 'Your passwords should match each other. Please recheck.', 'tutor' ); ?>" onfocus="this.setAttribute('pattern', document.getElementById('tutor-instructor-pass').value)" required/>
						</div>
					</div>
				</div>
				<?php do_action( 'tutor_add_new_instructor_form_fields_after' ); ?>
				<div class="tutor-row ">
					<div class="tutor-col">
						<label class="tutor-form-label">
							<?php esc_html_e( 'Bio', 'tutor' ); ?>
							<span class="text-medium-caption" style="color: #999ead;">
								<?php esc_html_e( '(Optional)', 'tutor' ); ?>
							</span>
						</label>
						<div class="tutor-input-group tutor-mb-16">
							<textarea  name="tutor_profile_bio" class="tutor-form-control" rows="3" style="width: 100%;" placeholder="<?php esc_html_e( 'Write Your Bio...', 'tutor' ); ?>"></textarea>
						</div>
					</div>
				</div>
				<div class="tutor-row " id="tutor-new-instructor-form-response"></div>
			  </div>
			<div class="tutor-modal-footer">
				<div class="tutor-d-flex tutor-justify-content-between">
					<div class="col">
						<button type="submit" class="tutor-btn tutor-btn-wordpress tutor-btn-lg tutor-btn-loading" name="tutor_register_instructor_btn">
							<?php esc_html_e( 'Add Instructor', 'tutor' ); ?>
						</button>
					</div>
					<div class="col-auto">
						<button data-tutor-modal-close class="tutor-btn tutor-is-default">
							<?php esc_html_e( 'Cancel', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
	</form>
	</div>
  </div>
</div>
<?php
/**
 * Instructor Approve, Reject popup
 * that will be shown based on get params
 *
 * @since v2.0.0
 */
$instructor_id       = isset( $_GET['instructor'] ) ? sanitize_text_field( $_GET['instructor'] ) : '';
$prompt_action       = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
	$instructor_data = get_userdata( $instructor_id );
if ( $instructor_data && ( 'approved' === $prompt_action || 'blocked' === $prompt_action ) ) :
	$instructor_status = tutor_utils()->instructor_status( $instructor_data->ID, false );
	?>
<div id="tutor-ins-approval-1" class="tutor-modal tutor-modal-is-close-beside tutor-modal-ins-approval tutor-is-active">
	<span class="tutor-modal-overlay"></span>
	<div class="tutor-modal-root">
		<button data-tutor-modal-close="true" class="tutor-modal-close">
			<span class="tutor-icon-56 tutor-icon-line-cross-line"></span>
		</button>
		<div class="tutor-modal-inner">
		<?php if ( $instructor_data ) : ?>
				<div class="tutor-modal-body tutor-text-center">
					<div class="tutor-modal-text-wrap">
						<div class="text-regular-h4 tutor-color-black">
							<?php esc_html_e( 'A New Instructor Just Signed Up', 'tutor' ); ?>
						</div>
						<div class="text-regular-small tutor-color-black-60 tutor-mt-12">
							<?php esc_html_e( 'You can either accept or reject the application. The applicant will be notified via email either way.', 'tutor' ); ?>
						</div>
					</div>
					<div class="tutor-modal-ins-meta tutor-mt-44">
						<div class="flex-center">
							<div class="tutor-avatar">
								<?php echo get_avatar( $instructor_data->ID ); ?>
							</div>
						</div>
						<div class="text-medium-h4 tutor-color-text-primay tutor-mt-20">
							<?php
							echo esc_html(
								( '' !== $instructor_data->display_name ?
								$instructor_data->display_name : ( '' !== $instructor_data->user_nicename ?
								$instructor_data->user_nicename : '' ) )
							);
							?>
						</div>
						<div class="text-regular-body tutor-color-black-70 tutor-mt-8">
							<?php esc_html_e( 'Username:', 'tutor' ); ?>
							<span class="tutor-color-black">
								<?php echo esc_html( $instructor_data->user_login ); ?>
							</span>
						</div>
						<div class="text-regular-body tutor-color-black-70 tutor-mt-4">
							<?php esc_html_e( 'Email:', 'tutor' ); ?>
							<span class="tutor-color-black">
								<?php echo esc_html( $instructor_data->user_email ); ?>
							</span>
						</div>
					</div>
					<div class="tutor-modal-buttons tutor-mt-32 tutor-mt-md-48">
						<?php if ( 'approved' === $prompt_action || 'blocked' === $prompt_action ) : ?>
							<?php if ( 'pending' === $instructor_status ) : ?>
								<a class="instructor-action tutor-btn tutor-btn-full " data-action="approve" data-instructor-id="<?php echo esc_attr( $instructor_data->ID ); ?>">
									<?php esc_html_e( 'Approve The Instructor', 'tutor' ); ?>
								</a>
								<a class="instructor-action tutor-btn tutor-is-outline tutor-is-default tutor-btn-full tutor-mt-md-25 tutor-mt-12" data-action="blocked" data-instructor-id="<?php echo esc_attr( $instructor_data->ID ); ?>">
									<?php esc_html_e( 'Reject The Application', 'tutor' ); ?>
								</a>
								<?php elseif ( 'approved' === $instructor_status ) : ?>
									<a class="instructor-action tutor-btn tutor-is-outline tutor-is-default tutor-btn-full tutor-mt-md-25 tutor-mt-12" data-action="blocked" data-instructor-id="<?php echo esc_attr( $instructor_data->ID ); ?>">
									<?php esc_html_e( 'Reject The Application', 'tutor' ); ?>
									</a>
								<?php elseif ( 'blocked' === $instructor_status ) : ?>
									<a class="instructor-action tutor-btn tutor-btn-full " data-action="approve" data-instructor-id="<?php echo esc_attr( $instructor_data->ID ); ?>">
									<?php esc_html_e( 'Approve The Instructor', 'tutor' ); ?>
								</a>
							<?php endif; ?>
						<?php else : ?>
							<div class="tutor-alert tutor-danger">
								<div class="tutor-alert-text">
									<span class="tutor-alert-icon tutor-icon-34 tutor-icon-cross-circle-outline-filled tutor-mr-12"></span>
									<span>
										<?php esc_html_e( 'Attempted invalid action', 'tutor' ); ?>
									</span>
								</div>
							</div>
						<?php endif; ?>
					</div>

				</div>
			<?php endif; ?>
			<?php if ( false === $instructor_data ) : ?>
				<div class="tutor-modal-body tutor-text-center">
					<div class="tutor-modal-text-wrap">
						<div class="tutor-alert tutor-danger">
								<div class="tutor-alert-text">
									<span class="tutor-alert-icon tutor-icon-34 tutor-icon-cross-circle-outline-filled tutor-mr-12"></span>
									<span>
									<?php esc_html_e( 'Invalid instructor', 'tutor' ); ?>
									</span>
								</div>
							</div>
						</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php endif; ?>
