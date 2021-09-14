<?php
$tutor_pages = tutils()->tutor_pages();
?>
<div class="tutor-option-main-title">
	<h2>Tutor Pages</h2>
</div>

<div class="tutor-option-single-item item-variation-table table-col-3 all-pages">
	<h4>All Pages</h4>
	<div class="item-wrapper">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<p>ID</p>
			</div>
			<div class="tutor-option-field-label">
				<p>Page Name</p>
			</div>
			<div class="tutor-option-field-label">
				<p>Status</p>
			</div>
		</div>
		<?php
		foreach ( $tutor_pages as $page ) {
			$page_id = $page['page_id'];
			?>
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<p class="text-medium-caption"><?php echo $page_id; ?></p>
			</div>
			<div class="tutor-option-field-label">
				<?php
					echo '<p class="text-medium-caption">';

					echo $page['page_name'];

				if ( $page['page_exists'] ) {
					$edit_url = admin_url( "post.php?post={$page_id}&action=edit" );
					echo "<a href='{$edit_url}' target='_blank' class='icon-link'><span class=' tutor-v2-icon-test icon-detail-link-filled'></span></a>";
				}
					echo '</p>';
				?>
			</div>
			<div class="tutor-option-field-label">
				<?php
				if ( $page['page_exists'] && $page['page_visible'] ) {
						$page = get_post( $page_id );
						echo "<a href='" . get_permalink( $page ) . "' target='_blank' class='text-medium-caption'> <span class='icon-check tutor-v2-icon-test icon-mark-filled'></span>/{$page->post_name} </a>";
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
<div class="tutor-backend-alert alert-primary">
	<span class="alert-icon tutor-v2-icon-test icon-warning-filled"></span>
	<span class="alert-content">Note: This tool will install all the missing Tutor pages. Pages already defined and set up will not be replaced.</span>
</div>
