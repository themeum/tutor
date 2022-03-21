<?php
/**
 * Global Pagination Template for Backend Pages
 *
 * @package Pagination
 * @since v2.0.0
 */

// Pagination.
$paged    = $data['paged'];
$per_page = $data['per_page'];
$big      = 999999999;
$total_page = ceil( $data['total_items'] / $per_page );

if(isset($data['layout']) && $data['layout']['type']=='load_more') {
	$current_url = tutor()->current_url;
	
	echo '<nav '.(isset($data['ajax']) ? ' data-tutor_pagination_ajax="'.esc_attr( json_encode($data['ajax']) ).'" ' : '').'>';
		
		if($paged<$total_page){
			echo '<a class="tutor-btn tutor-btn-tertiary tutor-is-outline page-numbers tutor-mr-4" href="'.add_query_arg( array('current_page' => $paged+1), $current_url ).'">'.
					$data['layout']['load_more_text']
				.'</a>';
		}
		
	echo '</nav>';

	return;
}

if ( isset( $data['total_items'] ) && $data['total_items'] ) : ?>
	<nav class="tutor-ui-pagination tutor-mt-40" <?php echo isset($data['ajax']) ? ' data-tutor_pagination_ajax="'.esc_attr( json_encode($data['ajax']) ).'" ' : ''; ?>>
		<div classs="tutor-pagination-hints">
			<div class="tutor-fs-7 tutor-color-black-60">
				<?php esc_html_e( 'Page', 'tutor' ); ?> 
				<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
					<?php echo esc_html( $data['paged'] ); ?>
				</span>
				<?php esc_html_e( 'of', 'tutor' ); ?> 
				<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
					<?php echo esc_html( ceil( $data['total_items'] / $data['per_page'] ) ); ?>
				</span>
			</div>
		</div>
		<ul class="tutor-pagination-numbers">
			<?php
				echo paginate_links(
					array(
						'format'    => '?current_page=%#%',
						'current'   => $paged,
						'total'     => $total_page,
						'prev_text' => '<span class="tutor-icon-angle-left-filled"></span>',
						'next_text' => '<span class="tutor-icon-angle-right-filled"></span>',
					)
				);
			?>
		</ul>
	</nav>
<?php endif; ?>
