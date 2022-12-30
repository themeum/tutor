<?php
/**
 * Profile completion
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Notifications
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

$profile_completion = tutils()->user_profile_completion();
if ( $profile_completion->progress < 100 ) { ?>
	<div class="tutor-col-12">
		<div class="tutor-profile-completion-warning">
			<div class="profile-completion-warning-icon">
				<span class="tutor-icon-warning"></span>
			</div>
			<div class="profile-completion-warning-content">
				<h4><?php esc_html_e( 'Complete Your Profile', 'tutor' ); ?></h4>
				<div class="profile-completion-warning-details">
					<p><?php esc_html_e( 'Complete your profile so people can know more about you! Go to Profile', 'tutor' ); ?> <a href="<?php echo esc_url( tutils()->tutor_dashboard_url( 'settings' ) ); ?>"><?php esc_html_e( 'Settings', 'tutor' ); ?></a></p>
					<ul>
						<?php
						foreach ( $profile_completion->empty_fields as $empty_field ) {
							echo wp_kses(
								'<li>' . __( 'Set Your', 'tutor' ) . '<span> ' . $empty_field . '</span></li>',
								array(
									'li'   => array(),
									'span' => array(),
								)
							);
						}
						?>
					</ul>
				</div>
				<div class="profile-completion-warning-status">
					<p>
						<span>
							<?php echo esc_html( $profile_completion->progress . __( '% Complete', 'tutor' ) ); ?>,
						</span> 
						<?php esc_html_e( 'You are almost done!', 'tutor' ); ?>
					</p>
					<div class="tutor-progress-bar-wrap">
						<div class="tutor-progress-bar">
							<div class="tutor-progress-filled" style="--tutor-progress-left: <?php echo esc_attr( $profile_completion->progress ); ?>%;"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
?>
