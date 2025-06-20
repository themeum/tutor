<?php
/**
 * List Filter views
 *
 * A common filter element for all the backend pages
 *
 * @package Tutor\Views
 * @subpackage Tutor\ViewElements
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.5.0
 */

use TUTOR\Input;

if ( isset( $data ) ) : ?>
<div class="tutor-admin-container tutor-admin-container-lg">
	<div class="tutor-wp-dashboard-course-filter tutor-justify-<?php echo esc_attr( ! empty( $data['bulk_action'] ) ? 'between' : 'end' ); ?>">
		<?php if ( isset( $data['bulk_action'] ) && true === $data['bulk_action'] ) : ?>
		<form id="tutor-admin-bulk-action-form" action method="post">
			<input type="hidden" name="action" value="<?php echo esc_attr( $data['ajax_action'] ); ?>" />
			<div class="tutor-d-flex">
				<div class="tutor-mr-12">
					<select name="bulk-action" title="Please select an action" class="tutor-form-control tutor-form-select">
						<?php foreach ( $data['bulk_actions'] as $k => $v ) : ?>
							<option value="<?php echo esc_attr( $v['value'] ); ?>">
								<?php echo esc_html( $v['option'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<button class="tutor-btn tutor-btn-outline-primary" id="tutor-admin-bulk-action-btn" data-tutor-modal-target="tutor-bulk-confirm-popup">
					<?php esc_html_e( 'Apply', 'tutor' ); ?>
				</button>
			</div>
		</form>
		<?php endif; ?>
		<?php
		$search_query  = Input::get( 'search', '', Input::TYPE_STRING );
		$current_order = Input::get( 'order', 'DESC', Input::TYPE_STRING );
		$order_link    = add_query_arg( 'order', 'ASC' === $current_order ? 'DESC' : 'ASC' );

		$current_page = Input::get( 'page', '', Input::TYPE_STRING );
		$sub_page     = Input::get( 'sub_page', '', Input::TYPE_STRING );
		$current_tab  = Input::get( 'tab', '', Input::TYPE_STRING );
		if ( '' === $sub_page && '' !== $current_tab ) {
			$sub_page = $current_tab;
		}

		$url = '';
		if ( '' === $sub_page && '' === $current_tab ) {
			$url = "?page=$current_page";
		} elseif ( '' === $current_tab ) {
			$url = "?page=$current_page&sub_page=$sub_page";
		} else {
			$url = "?page=$current_page&tab=$current_tab";
		}

		$filters_count = count(
			array_filter(
				$data['filters'],
				function( $filter ) {
					$value = Input::get( $filter['field_name'], '', Input::TYPE_STRING );
					return null !== $value && '' !== $value;
				}
			)
		);
		?>

		<div class="tutor-wp-dashboard-filter-right tutor-d-flex tutor-flex-wrap tutor-gap-1 <?php echo esc_attr( $filters_count > 2 ? 'tutor-flex-column' : 'tutor-flex-row-reverse' ); ?>">
			<div class="tutor-d-flex tutor-flex-wrap tutor-align-center tutor-justify-end tutor-gap-1 <?php echo esc_attr( $filters_count > 0 ? 'tutor-ml-16' : '' ); ?>">
				<?php if ( isset( $data['filters'] ) ) : ?>
				<div class="tutor-wp-dashboard-filters tutor-dropdown-parent">
					<button type="button" class="tutor-wp-dashboard-filters-button <?php echo esc_attr( $filters_count > 0 ? 'active' : '' ); ?>" action-tutor-dropdown="toggle">
						<i class="tutor-icon-slider-horizontal"></i>
						<span class="tutor-fs-6 tutor-color-secondary"><?php esc_html_e( 'Filters', 'tutor' ); ?></span>
						<?php if ( $filters_count > 0 ) : ?>
						<span class="tutor-wp-dashboard-filters-line"></span>
						<span class="tutor-wp-dashboard-filters-count"><?php echo esc_html( $filters_count ); ?></span>
						<?php endif; ?>
					</button>

					<form class="tutor-dropdown tutor-admin-dashboard-filter-form" data-tutor-dropdown-persistent>
						<div class="tutor-d-flex tutor-justify-between tutor-mb-16">
							<span class="tutor-fs-6 tutor-fw-medium"><?php esc_html_e( 'Filters', 'tutor' ); ?></span>
							<button type="button" class="tutor-iconic-btn" data-tutor-dropdown-close>
								<i class="tutor-icon-times"></i>
							</button>
						</div>

						<div class="tutor-d-flex tutor-flex-column tutor-gap-12px">
							<?php foreach ( $data['filters'] as $filter ) : ?>
								<?php if ( 'date' === $filter['field_type'] ) : ?>
									<div class="tutor-wp-dashboard-filters-item">
										<?php if ( isset( $filter['label'] ) && ! empty( $filter['show_label'] ) ) : ?>
											<label class="tutor-form-label">
												<?php echo esc_html( $filter['label'] ); ?>
											</label>
										<?php endif; ?>
										<div class="tutor-v2-date-picker" data-prevent_redirect="1" data-is_clearable="1" data-input_name="<?php echo esc_attr( $filter['field_name'] ); ?>">
											<div class="tutor-form-wrap">
												<span class="tutor-form-icon tutor-form-icon-reverse">
													<span class="tutor-icon-calender-line" aria-hidden="true"></span>
												</span>
												<input class="tutor-form-control" placeholder="<?php esc_attr_e( 'Loading...', 'tutor' ); ?>">
											</div>
										</div>
									</div>
								<?php elseif ( 'select' === $filter['field_type'] ) : ?>
									<div class="tutor-wp-dashboard-filters-item">
										<?php if ( isset( $filter['label'] ) && ! empty( $filter['show_label'] ) ) : ?>
											<label class="tutor-form-label">
												<?php echo esc_html( $filter['label'] ); ?>
											</label>
										<?php endif; ?>
										<select name="<?php echo esc_attr( $filter['field_name'] ); ?>" class="tutor-form-control tutor-form-select" <?php echo ! empty( $filter['searchable'] ) ? 'data-searchable' : ''; ?>>
											<?php if ( count( $filter['options'] ) ) : ?>
												<?php foreach ( $filter['options'] as $option ) : ?>
													<option value="<?php echo esc_attr( $option['key'] ); ?>" <?php selected( $filter['value'], $option['key'], 'selected' ); ?>>
														<?php echo esc_html( $option['title'] ); ?>
														<?php if ( isset( $option['value'] ) ) : ?>
															(<?php echo esc_html( $option['value'] ); ?>)
														<?php endif; ?>
													</option>
												<?php endforeach; ?>
											<?php else : ?>
												<option value=""><?php esc_html_e( 'No record found', 'tutor' ); ?></option>
											<?php endif; ?>
										</select>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>

						<div class="tutor-d-flex tutor-justify-end tutor-mt-16">
							<button type="submit" class="tutor-btn tutor-btn-outline-primary">
								<?php esc_html_e( 'Apply Filters', 'tutor' ); ?>
							</button>
						</div>
					</form>
				</div>
				<?php endif; ?>

				<a class="tutor-wp-dashboard-filter-order" href="<?php echo esc_url( $order_link ); ?>">
					<?php if ( 'ASC' === $current_order ) : ?> 
						<i class="tutor-icon-sorting-asc"></i>
					<?php else : ?>
						<i class="tutor-icon-sorting-desc"></i>
					<?php endif; ?>
				</a>

				<form action="" method="get" id="tutor-admin-search-filter-form">
					<div class="tutor-form-wrap">
						<span class="tutor-form-icon"><span class="tutor-icon-search" area-hidden="true"></span></span>
						<input type="search" class="tutor-form-control" id="tutor-backend-filter-search" name="search" placeholder="<?php esc_html_e( 'Search...', 'tutor' ); ?>" value="<?php echo esc_html( wp_unslash( $search_query ) ); ?>" />
					</div>
				</form>
			</div>

			<?php if ( $filters_count > 0 || strlen( $search_query ) > 0 ) : ?>
			<div class="tutor-d-flex tutor-flex-wrap tutor-align-center tutor-justify-end tutor-gap-1">
				<a class="tutor-color-subdued tutor-px-8 tutor-py-4" href="<?php echo esc_url( $url ); ?>">
					<?php esc_html_e( 'Clear All', 'tutor' ); ?>
				</a>

				<div class="tutor-wp-dashboard-filter-tag-wrapper">
					<?php
					if ( ! empty( $data['filters'] ) ) {
						foreach ( $data['filters'] as $key => $filter ) {
							$query_value = Input::get( $filter['field_name'], '', Input::TYPE_STRING );
							if ( empty( $query_value ) ) {
								continue;
							}
							?>

							<?php if ( 'date' === $filter['field_type'] ) : ?>
							<div class="tutor-wp-dashboard-filter-tag">
								<div class="tutor-d-flex tutor-gap-4px tutor-align-center">
									<span><?php echo esc_html( $filter['label'] ); ?>:</span>
									<div class="tutor-v2-date-picker" data-input_name="<?php echo esc_attr( $filter['field_name'] ); ?>">
										<input class="tutor-form-control" placeholder="<?php esc_attr_e( 'Loading...', 'tutor' ); ?>">
									</div>
								</div>
								<a href="<?php echo esc_url( remove_query_arg( $filter['field_name'] ) ); ?>">
									<i class="tutor-icon-times"></i>
								</a>
							</div>
							<?php elseif ( 'select' === $filter['field_type'] && ! empty( $filter['options'] ) ) : ?>
							<div class="tutor-wp-dashboard-filter-tag-dropdown">
								<select name="<?php echo esc_attr( $filter['field_name'] ); ?>" class="tutor-form-control tutor-form-select tutor-filter-select" <?php echo ! empty( $filter['searchable'] ) ? 'data-searchable' : ''; ?>>
									<?php if ( count( $filter['options'] ) ) : ?>
										<?php foreach ( $filter['options'] as $option ) : ?>
											<option value="<?php echo esc_attr( $option['key'] ); ?>" <?php selected( $filter['value'], $option['key'], 'selected' ); ?>>
												<?php echo esc_html( $option['title'] ); ?>
												<?php if ( isset( $option['value'] ) ) : ?>
													(<?php echo esc_html( $option['value'] ); ?>)
												<?php endif; ?>
											</option>
										<?php endforeach; ?>
									<?php else : ?>
										<option value=""><?php esc_html_e( 'No record found', 'tutor' ); ?></option>
									<?php endif; ?>
								</select>
								<a href="<?php echo esc_url( remove_query_arg( $filter['field_name'] ) ); ?>">
									<i class="tutor-icon-times"></i>
								</a>
							</div>
							<?php else : ?>
							<div class="tutor-wp-dashboard-filter-tag">
								<span><?php echo esc_html( $filter['label'] ); ?>: <?php echo esc_html( $query_value ); ?></span>
								<a href="<?php echo esc_url( remove_query_arg( $filter['field_name'] ) ); ?>">
									<i class="tutor-icon-times"></i>
								</a>
							</div>
							<?php endif; ?>
							<?php
						}
					}
					?>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php endif; ?>

<?php
tutor_load_template_from_custom_path( tutor()->path . 'views/elements/bulk-confirm-popup.php' );
?>
