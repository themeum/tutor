<?php
/**
 * Frontend Dashboard Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

defined( 'ABSPATH' ) || exit;

if ( tutor_utils()->get_option( 'enable_profile_completion' ) ) {
	$profile_completion = tutor_utils()->user_profile_completion();
	$is_instructor      = tutor_utils()->is_instructor( null, true );
	$total_count        = count( $profile_completion );
	$incomplete_count   = count(
		array_filter(
			$profile_completion,
			function ( $data ) {
				return ! $data['is_set'];
			}
		)
	);
	$complete_count     = $total_count - $incomplete_count;

	if ( $is_instructor ) {
		if ( isset( $total_count ) && isset( $incomplete_count ) && $incomplete_count <= $total_count ) {
			?>
			<div class="tutor-profile-completion tutor-card tutor-px-32 tutor-py-24 tutor-mb-40">
				<div class="tutor-row tutor-gx-0">
					<div class="tutor-col-lg-7 <?php echo tutor_utils()->is_instructor() ? 'tutor-profile-completion-content-admin' : ''; ?>">
						<div class="tutor-fs-5 tutor-fw-medium tutor-color-black">
							<?php esc_html_e( 'Complete Your Profile', 'tutor' ); ?>
						</div>

						<div class="tutor-row tutor-align-center tutor-mt-12">
							<div class="tutor-col">
								<div class="tutor-row tutor-gx-1">
									<?php for ( $i = 1; $i <= $total_count; $i++ ) : ?>
										<div class="tutor-col">
											<div class="tutor-progress-bar" style="--tutor-progress-value: <?php echo $i > $complete_count ? 0 : 100; ?>%; height: 8px;"><div class="tutor-progress-value" area-hidden="true"></div></div>
										</div>
									<?php endfor; ?>
								</div>
							</div>

							<div class="tutor-col-auto">
								<span class="tutor-round-box tutor-my-n20">
									<i class="tutor-icon-trophy" area-hidden="true"></i>
								</span>
							</div>
						</div>

						<div class="tutor-fs-6 tutor-mt-20">
							<?php
								$profile_complete_text = __( 'Please complete profile', 'tutor' );
							if ( $complete_count > ( $total_count / 2 ) && $complete_count < $total_count ) {
								$profile_complete_text = __( 'You are almost done', 'tutor' );
							} elseif ( $complete_count === $total_count ) {
								$profile_complete_text = __( 'Thanks for completing your profile', 'tutor' );
							}
								$profile_complete_status = $profile_complete_text;
							?>

							<span class="tutor-color-muted"><?php echo esc_html( $profile_complete_status ); ?>:</span>
							<span><?php echo esc_html( $complete_count . '/' . $total_count ); ?></span>
						</div>
					</div>

					<div class="tutor-col-lg-1 tutor-text-center tutor-my-24 tutor-my-lg-n24">
						<div class="tutor-vr tutor-d-none tutor-d-lg-inline-flex"></div>
						<div class="tutor-hr tutor-d-flex tutor-d-lg-none"></div>
					</div>

					<div class="tutor-col-lg-4 tutor-d-flex tutor-flex-column tutor-justify-center">
						<?php
						$i           = 0;
						$monetize_by = tutils()->get_option( 'monetize_by' );
						foreach ( $profile_completion as $key => $data ) {
							if ( '_tutor_withdraw_method_data' === $key ) {
								if ( 'free' === $monetize_by ) {
									continue;
								}
							}
							$is_set = $data['is_set']; // Whether the step is done or not.
							?>
								<div class="tutor-d-flex tutor-align-center<?php echo $i < ( count( $profile_completion ) - 1 ) ? ' tutor-mb-8' : ''; ?>">
									<?php if ( $is_set ) : ?>
										<span class="tutor-icon-circle-mark-line tutor-color-success tutor-mr-8"></span>
									<?php else : ?>
										<span class="tutor-icon-circle-times-line tutor-color-warning tutor-mr-8"></span>
									<?php endif; ?>

									<span class="<?php echo $is_set ? 'tutor-color-secondary' : 'tutor-color-muted'; ?>">
										<a class="tutor-btn tutor-btn-ghost tutor-has-underline" href="<?php echo esc_url( $data['url'] ); ?>">
											<?php echo esc_html( $data['text'] ); ?>
										</a>
									</span>
								</div>
								<?php
								$i++;
						}
						?>
					</div>
				</div>
			</div>
			<?php
		}
	} elseif ( ! $profile_completion['_tutor_profile_photo']['is_set'] ) {
			$alert_message = sprintf(
				'<div class="tutor-alert tutor-primary tutor-mb-20">
					<div class="tutor-alert-text">
						<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-info tutor-mr-12"></span>
						<span>
							%s
						</span>
					</div>
					<div class="alert-btn-group">
						<a href="%s" class="tutor-btn tutor-btn-sm">' . __( 'Click Here', 'tutor' ) . '</a>
					</div>
				</div>',
				$profile_completion['_tutor_profile_photo']['text'],
				tutor_utils()->tutor_dashboard_url( 'settings' )
			);

			echo $alert_message; //phpcs:ignore

	}
}
?>
<?php do_action( 'tutor_before_dashboard_content' ); ?>

<?php tutor_load_template( 'dashboard.instructor.home' ); ?>
