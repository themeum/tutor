<?php
/**
 * Search filter
 *
 * @package Tutor\Views
 * @subpackage Tutor\ViewElements
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<?php if ( isset( $data ) ) : ?>

	<div class="tutor-admin-page-filters" style="display: flex; justify-content: space-between">
		<?php if ( $data['bulk_action'] ) : ?>
			<div class="tutor-admin-bulk-action-wrapper">
				<form action="" method="post">
					<div class="tutor-bulk-action-group">
						<select name="bulk-action" id="tutor-backend-bulk-action">
							<?php foreach ( $data['bulk_actions'] as $k => $v ) : ?>
								<option value="<?php echo esc_attr( $v['value'] ); ?>">
									<?php echo esc_html( $v['option'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="tutor-btn">
							<?php esc_html_e( 'Apply', 'tutor' ); ?>
						</button>
					</div>
				</form>
			</div>
		<?php endif; ?>
		<?php if ( isset( $data['search_filter'] ) && true === $data['search_filter'] ) : ?>
			<div class="tutor-admin-page-search-filter-wrapper">
				<form>
					<div class="tutor-form-group select-with-input" style="display: flex; align-items: center; column-gap: 15px;">
						<select name="tutor-admin-search-by" id="tutor-admin-search-by">
							<option value="course">
								<?php esc_html_e( 'Course', 'tutor' ); ?>
							</option>
							<option value="student">
								<?php esc_html_e( 'Student', 'tutor' ); ?>
							</option>
						</select>
						<input type="text" name="tutor-admin-search-by" id="tutor-admin-search-by">
						<button type="button" class="tutor-btn">
							<?php esc_html_e( 'Apply', 'tutor' ); ?>
						</button>
					</div>
				</form>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
