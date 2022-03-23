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
				<p><?php _e('ID','tutor'); ?></p>
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
				<p class="tutor-fs-7 tutor-fw-medium"><?php echo $page_id; ?></p>
			</div>
			<div class="tutor-option-field-label">
				<?php
					echo '<div class="tutor-fs-7 tutor-fw-medium tutor-d-flex tutor-align-items-center">';

					echo $page['page_name'];

				if ( $page['page_exists'] ) {
					$edit_url = admin_url( "post.php?post={$page_id}&action=edit" );
					echo "<a href='{$edit_url}' target='_blank' class='icon-link tutor-color-stroke-light-30 tutor-d-flex tutor-ml-4'><span class=' tutor-icon-detail-link-filled tutor-icon-24'></span></a>";
				}
					echo '</div>';
				?>
			</div>
			<div class="tutor-option-field-label">
				<?php
				if ( $page['page_exists'] && $page['page_visible'] ) {
					$page = get_post( $page_id );
					echo "<a href='" . get_permalink( $page ) . "' target='_blank' class='tutor-fs-7 tutor-fw-medium tutor-color-black tutor-d-flex tutor-align-items-center'><span class='icon-check tutor-icon-mark-cricle tutor-icon-20 tutor-color-design-success'></span>/{$page->post_name}</a>";
				}
				?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<div class="btn-wrap regenerate-pages">
	<form action="" method="post">
		<?php
		tutor_action_field( 'regenerate_tutor_pages' );
		tutor_nonce_field();
		?>
		<p>
			<button class="tutor-btn tutor-is-sm" type="submit"><?php esc_html_e( 'Re-Generate Tutor Pages', 'tutor' ); ?></button>
		</p>
	</form>
</div><br>