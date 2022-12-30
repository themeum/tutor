<?php
/**
 * Tutor available addons
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$current_page = tutor_utils()->avalue_dot( 'tab', tutor_sanitize_data( $_GET ) ); //phpcs:ignore
$page_name    = $current_page ? $current_page : 'addons';
?>

<div class="wrap plugin-install-tab-featured tutor-addons">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Tutor Add-ons' ); ?></h1>

	<hr class="wp-header-end">

	<div class="wp-filter">
		<ul class="filter-links">
			<li class="tutor-available-addons <?php echo esc_attr( 'addons' === $page_name ? 'current' : '' ); ?> ">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=tutor-addons' ) ); ?>" aria-current="page"><?php esc_html_e( 'Plugins', 'tutor' ); ?>
				</a>
			</li>
			<li class="tutor-available-themes <?php echo esc_attr( 'themes' === $page_name ? 'current' : '' ); ?>">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=tutor-addons&tab=themes' ) ); ?>">
					<?php esc_html_e( 'Themes', 'tutor' ); ?>
				</a>
			</li>
		</ul>
	</div>

	<br class="clear">

	<form id="plugin-filter" method="post">
		<div class="wp-list-table widefat plugin-install">
			<?php
			$last_checked_time = tutor_utils()->avalue_dot( 'last_checked_time', $addons_themes_data );
			if ( $last_checked_time ) {
				$last_checked_time = tutor_utils()->avalue_dot( 'last_checked_time', $addons_themes_data );
				$data              = json_decode( tutor_utils()->avalue_dot( 'data', $addons_themes_data ) );

				if ( 'themes' === $current_page ) {
					$addons = tutor_utils()->avalue_dot( 'theme', $data );
				} else {
					$addons = tutor_utils()->avalue_dot( 'addon', $data );
				}
				?>

				<p class="tutor-addons-last-checked-time">
					<?php
						$last_checked_human_time_diff       = human_time_diff( $last_checked_time );
						$last_checked_human_time_diff_hours = human_time_diff( tutor_time(), $last_checked_time + 6 * HOUR_IN_SECONDS );

						$text  = _x( 'Last checked', 'addon-last-checked', 'tutor' );
						$text .= $last_checked_human_time_diff;
						$text .= _x( 'ago, It will check again after', 'addon-last-checked', 'tutor' );
						$text .= $last_checked_human_time_diff_hours;
						$text .= _x( 'from now', 'addon-last-checked', 'tutor' );
						echo esc_html( $text );
					?>
				</p>

				<div id="the-list">
					<?php
					if ( is_array( $addons ) && count( $addons ) ) {
						foreach ( $addons as $addon ) {
							?>
							<div class="plugin-card plugin-card-akismet">
								<div class="plugin-card-top">
									<div class="name column-name">
										<h3>
											<?php
											echo '<a href="' . esc_url( $addon->product_url ) . '" target="_blank">' . esc_attr( $addon->product_name ) . '</a>';
											if ( $addon->thumbnail ) {
												echo '<img src="' . esc_url( $addon->thumbnail ) . '" class="plugin-icon" alt="">';
											}
											?>
										</h3>
									</div>
									<div class="action-links">
										<ul class="plugin-action-buttons">
											<li><a href="<?php echo esc_url( $addon->product_url ); ?>" class="button button-primary activate-now"
												target="_blank">  <?php esc_html_e( 'Buy Now', 'tutor' ); ?></a></li>

											<li>
												<span class="addon-regular-price">
													<del>
														<?php echo esc_html( $addon->regular_price ); ?>
													</del>
												</span>
												<span class="addon-current-price">
													<?php echo esc_html( $addon->price ); ?>
												</span>
											</li>

										</ul>
									</div>
									<div class="desc column-description">
										<?php echo $addon->short_description ? '<p>' . esc_attr( $addon->short_description ) . '</p>' : ''; ?>

										<p class="authors"><cite>By <a href="https://www.themeum.com" target="_blank">Themeum</a></cite></p>
									</div>
								</div>
								<div class="plugin-card-bottom">
									<?php if ( $addon->version ) : ?>
										<div class="plugin-version tutor-d-inline-block">
											<?php
												echo esc_html__( 'Version:', 'tutor' ) . esc_html( $addon->version );
											?>
										</div>
									<?php endif; ?>
								</div>
							</div>
							<?php
						}
					} else {
						echo esc_html( "No {$page_name} currently available" );
					}
					?>
				</div>
				<?php
			} else {
				echo esc_html( "No {$page_name} currently available" );
			}
			?>

		</div>
	</form>

	<span class="spinner"></span>
</div>
