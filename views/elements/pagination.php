<?php
/**
 * Pagination component
 *
 * @package Tutor\Views
 * @subpackage Tutor\ViewElements
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( isset( $data['total_items'] ) && $data['total_items'] ) : ?>
	<nav class="tutor-pagination">
		<div class="tutor-pagination-hints">
			<div class="tutor-fs-7 tutor-color-secondary">
				<?php esc_html_e( 'Page', 'tutor' ); ?>
				<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
					<?php echo esc_html( $data['paged'] ); ?>
				</span>
				<?php esc_html_e( 'of', 'tutor' ); ?>
				<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
					<?php echo esc_html( ceil( 0 < $data['per_page'] ) ? ceil( $data['total_items'] / $data['per_page'] ) : '' ); ?>
				</span>
			</div>
		</div>
		<ul class="tutor-pagination-numbers">
			<?php
			// Pagination.
			$big  = 999999999;
			$base = str_replace( $big, '%#%', esc_url( admin_url( $big ) . 'admin.php?paged=%#%' ) );

			echo wp_kses_post(
				paginate_links(
					array(
						'base'      => ! empty( $data['base'] ) ? $data['base'] : $base,
						'format'    => '?paged=%#%',
						'current'   => (int) $data['paged'],
						'total'     => $data['per_page'] ? ceil( $data['total_items'] / (int) $data['per_page'] ) : 1,
						'prev_text' => '<span class="tutor-icon-angle-left"></span>',
						'next_text' => '<span class="tutor-icon-angle-right"></span>',
					)
				)
			);
			?>
		</ul>
	</nav>
<?php endif; ?>
