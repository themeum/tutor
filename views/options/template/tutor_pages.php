<?php
$tutor_pages = tutor_utils()->tutor_pages();
?>
<div class="tutor-option-main-title">
	<div class="tutor-fs-4 tutor-fw-medium tutor-color-black"><?php _e('Tutor Pages','tutor'); ?></div>
</div>

<?php tutor_alert(__('Note: This tool will install all the missing Tutor pages. Pages already defined and set up will not be replaced.', 'tutor'), 'primary'); ?>

<div class="tutor-option-single-item tutor-mb-32 item-variation-table table-col-3 all-pages">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted"><?php _e('All Pages','tutor'); ?></div>
	</div>
	<div class="item-wrapper">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<?php _e('ID','tutor'); ?>
			</div>
			<div class="tutor-option-field-label">
				<?php _e('Page Name','tutor'); ?>
			</div>
			<div class="tutor-option-field-label">
				<?php _e('Status','tutor'); ?>
			</div>
		</div>
		<?php
		foreach ( $tutor_pages as $page ) {
			$page_id = $page['page_id'];
			?>
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<div class="tutor-fs-7 tutor-fw-medium"><?php echo $page_id; ?></div>
			</div>
			<div class="tutor-option-field-label">
				<div class="tutor-fs-7 tutor-fw-medium">
					<?php if ( $page['page_exists'] ) : ?>
						<a class="tutor-btn tutor-btn-ghost" href="<?php echo admin_url( "post.php?post={$page_id}&action=edit" ); ?>" target='_blank'>
							<?php echo $page['page_name']; ?>
						</a>
					<?php else : ?>
						<?php echo $page['page_name']; ?>
					<?php endif; ?>
				</div>
				<?php
				// 	echo '';

				// 	echo $page['page_name'];

				// if ( $page['page_exists'] ) {
				// 	$edit_url = admin_url( "post.php?post={$page_id}&action=edit" );
				// 	echo "<a class='tutor-btn tutor-btn-ghost' href='{$edit_url}' target='_blank' class='tutor-iconic-btn tutor-ml-8'><span class='tutor-icon-detail-link-filled'></span></a>";
				// 	// echo "<a class='tutor-btn tutor-btn-ghost' href='{$edit_url}' target='_blank' class='tutor-iconic-btn tutor-ml-8'><span class='tutor-icon-detail-link-filled'></span></a>";
				// }

				// // tutor-d-flex tutor-align-items-center
				// 	echo '';
				?>
			</div>
			<div class="tutor-option-field-label">
				<?php if($page['page_exists'] && $page['page_visible']) : ?>
					<?php $page = get_post( $page_id ); ?>
					<div class="tutor-d-flex tutor-align-items-center">
						<span class='icon-check tutor-icon-mark-cricle tutor-color-success'></span>
						<span class='tutor-mx-4'>/</span>
						<span><?php echo $page->post_name; ?></span>
						<span class="tutor-ml-8"><a href="<?php echo get_permalink( $page ); ?>" class="tutor-iconic-btn" target="_blank"><i class="tutor-icon-detail-link-filled"></i></a></span>
					</div>
				<?php else : ?>
					<span class='tutor-icon-cross-circle-outline-filled tutor-color-warning'></span>
				<?php endif; ?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<div class="btn-wrap regenerate-pages">
	<form method="post">
		<?php
		tutor_action_field( 'regenerate_tutor_pages' );
		tutor_nonce_field();
		?>
		<button class="tutor-btn tutor-btn-primary" type="submit"><?php esc_html_e( 'Re-Generate Tutor Pages', 'tutor' ); ?></button>
	</form>
</div><br>