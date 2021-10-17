<?php if ( isset( $data['total_items'] ) && $data['total_items'] ) : ?>
<div class="tutor-admin-page-pagination">
	<?php
		// Pagination.
		$paged    = $data['paged'];
		$per_page = $data['per_page'];
		$big      = 999999999;
		echo paginate_links(
			array(
				//'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'base'    => str_replace( $big, '%#%', esc_url( admin_url( $big ) . 'admin.php?paged=%#%' ) ),
				'format'  => '?paged=%#%',
				'current' => $paged,
				'total'   => $data['total_items'] / $per_page,
			)
		);
	?>
</div>   
<?php endif; ?>
