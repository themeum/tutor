<?php
/**
 * Global Pagination Template for Backend Pages
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Elements
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

// Pagination.
$paged                    = $data['paged'];
$per_page                 = $data['per_page'];
$big                      = 999999999;
$total_page               = isset( $data['total_page'] ) ? $data['total_page'] : ceil( $data['total_items'] / $per_page );
$pagination_enabled_class = wp_doing_ajax() ? ' is-ajax-pagination-enabled ' : '';

// Prepare data set attribute string.
$dataset      = isset( $data['data_set'] ) ? $data['data_set'] : array();
$dataset_attr = '';
foreach ( $dataset as $key => $value ) {
	$dataset_attr .= ' data-' . $key . '="' . esc_attr( $value ) . '" ';
}

// @todo: conditions are incorrect.

if ( isset( $data['layout'] ) && 'load_more' == $data['layout']['type'] ) {
	$current_url = tutor()->current_url;

	echo '<nav ' . ( isset( $data['ajax'] ) ? ' data-tutor_pagination_ajax="' . esc_attr( json_encode( $data['ajax'] ) ) . '" ' : '' ) . ' data-tutor_pagination_layout="' . esc_attr( json_encode( $data['layout'] ) ) . '" class="' . $pagination_enabled_class . '" ' . $dataset_attr . '>';//phpcs:ignore

	if ( $paged < $total_page ) {
		echo '<a class="tutor-btn tutor-btn-outline-primary page-numbers tutor-mr-16" href="' . esc_url( add_query_arg( array( 'current_page' => $paged + 1 ), $current_url ) ) . '">' .
				esc_html( $data['layout']['load_more_text'] )
			. '</a>';
	}

	echo '</nav>';

	return;
}

if ( ( isset( $data['total_page'] ) && $data['total_page'] ) || ( isset( $data['total_items'] ) && $data['total_items'] ) ) : ?>
	<nav class="tutor-pagination tutor-mt-40 <?php echo esc_attr( $pagination_enabled_class ); ?>" 
			<?php
			echo isset( $data['ajax'] ) ? ' data-tutor_pagination_ajax="' . esc_attr( json_encode( $data['ajax'] ) ) . '" ' : '';
			echo $dataset_attr; //phpcs:ignore
			?>
	>
		<div class="tutor-pagination-hints">
			<div class="tutor-fs-7 tutor-color-black-60">
				<?php esc_html_e( 'Page', 'tutor' ); ?> 
				<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
					<?php echo esc_html( $data['paged'] ); ?>
				</span>
				<?php esc_html_e( 'of', 'tutor' ); ?> 
				<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
					<?php echo esc_html( $total_page ); ?>
				</span>
			</div>
		</div>
		<ul class="tutor-pagination-numbers">
			<?php
				//phpcs:ignore
				echo paginate_links(
					array(
						'format'    => '?current_page=%#%',
						'current'   => $paged,
						'total'     => $total_page,
						'prev_text' => '<span class="tutor-icon-angle-left"></span>',
						'next_text' => '<span class="tutor-icon-angle-right"></span>',
					)
				);
			?>
		</ul>
	</nav>
<?php endif; ?>
