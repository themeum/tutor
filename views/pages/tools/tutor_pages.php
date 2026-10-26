<?php
/**
 * Tutor pages
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

$tutor_pages = tutor_utils()->tutor_pages();
?>

<div id="tools-tutor-pages" class="tools-tutor-pages">
	<table class="tutor-table tutor-pages-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'Page Name', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'Status', 'tutor' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $tutor_pages as $page ) {
				$page_id = $page['page_id'];
				?>
					<tr>
						<td><?php echo esc_html( $page_id ); ?></td>
						<td>
							<p>
							<?php
							if ( $page['page_exists'] ) {
								$edit_url = admin_url( "post.php?post={$page_id}&action=edit" );
								echo '<a href="' . esc_url( $edit_url ) . '" target="_blank">' . esc_html( $page['page_name'] ) . '</a>';
							} else {
								echo esc_html( $page['page_name'] );
							}
							?>
							</p>
						</td>

						<td>
							<?php if ( ! $page_id ) : ?>
								<p style="color: red;">
									<i class="dashicons dashicons-warning"></i> <?php esc_html_e( ' Page not set', 'tutor' ); ?>
								</p>
							<?php endif; ?>

							<?php if ( ! $page['page_exists'] ) : ?>
								<p style="color: red;">
									<i class="dashicons dashicons-warning"></i>
									<?php esc_html_e( ' Page deleted, please set new one', 'tutor' ); ?>
								</p>
							<?php endif; ?>

							<?php if ( $page['page_exists'] && ! $page['page_visible'] ) : ?>
								<p style="color: red;">
									<i class="dashicons dashicons-warning"></i>
									<?php esc_html_e( 'Page visibility is not public', 'tutor' ); ?>
								</p>
							<?php endif; ?>

							<?php
							if ( $page['page_exists'] && $page['page_visible'] ) {
								$page_post = get_post( $page_id );
								if ( $page_post ) {
									echo '<a href="' . esc_url( get_permalink( $page_post ) ) . '" target="_blank" style="color: green;"> 
											<i class="dashicons dashicons-yes-alt"></i> /' . esc_html( $page_post->post_name ) . ' 
										</a>';
								}
							}
							?>
						</td>
					</tr>
				<?php
			}
			?>
		</tbody>
	</table>

	<form action="" method="post">
		<?php
			tutor_action_field( 'regenerate_tutor_pages' );
			tutor_nonce_field();
		?>

		<p>
			<button class="tutor-btn" type="submit"><?php esc_html_e( 'Re-Generate Tutor Pages', 'tutor' ); ?></button>
		</p>
	</form>

	<?php
		tutor_alert( __( 'Note: This tool will install all the missing Tutor pages. Pages already defined and set up will not be replaced.', 'tutor' ), 'info' );
	?>
</div>
