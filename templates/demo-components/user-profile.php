<?php
/**
 * Tutor dashboard profile.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>
<div class="tutor-user-profile">
	<?php
	tutor_load_template(
		'demo-components.dashboard.components.profile-pages-header',
		array( 'page_title' => __( 'Profile', 'tutor' ) )
	);
	?>
	<div class="tutor-profile-container">
		<h4 class="tutor-profile-page-title"><?php esc_html_e( 'Profile', 'tutor' ); ?></h4>
		<div class="tutor-profile-card">
			<div class="tutor-profile-card-header">
				<div class="tutor-avatar tutor-avatar-104 tutor-border tutor-border-2 tutor-border-brand-secondary">
					<img src="https://i.pravatar.cc/150?u=a04258a2462d826" alt="User Avatar" class="tutor-avatar-image">
				</div>
				<a href="#" class="tutor-edit-profile-btn">
					<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
					<span><?php esc_html_e( 'Edit Profile', 'tutor' ); ?></span>
				</a>
			</div>
			<div class="tutor-profile-card-body">
				<div class="tutor-profile-card-body-left">
					<div>
						<h3 class="tutor-user-profile-title">Alicia Jasmine</h3>
						<div class="tutor-user-profile-designation">Comedian</div>
						<div class="tutor-user-profile-social">
							<a href="#"><?php tutor_utils()->render_svg_icon( Icon::FACEBOOK ); ?></a>
							<a href="#"><?php tutor_utils()->render_svg_icon( Icon::X ); ?></a>
							<a href="#"><?php tutor_utils()->render_svg_icon( Icon::LINKEDIN ); ?></a>
							<a href="#"><?php tutor_utils()->render_svg_icon( Icon::GITHUB ); ?></a>
							<a href="#"><?php tutor_utils()->render_svg_icon( Icon::GLOBE ); ?></a>
						</div>
					</div>
				</div>
				<div class="tutor-profile-card-body-right">
					<div class="tutor-user-profile-bio">Alicia Jasmin is a creative designer passionate about art and technology, transforming ideas into reality.</div>
					<ul class="tutor-user-profile-details">
						<li>Username: <span>alicia</span></li>
						<li>Email: <span>alicia@gmail.com</span></li>
						<li>Phone: <span>+018 753754 7886</span></li>
					</ul>
				</div>
				<div class="tutor-profile-member-since">
					<?php tutor_utils()->render_svg_icon( Icon::MEMBER ); ?>
					Member since August 7, 2020
				</div>
			</div>
		</div>
		<div class="tutor-user-profile-statistics">
			<h5 class="tutor-statistic-title">
				<?php esc_html_e( 'Statistics', 'tutor' ); ?>
			</h5>

			<div class="tutor-statistic-cards">
				<div class="tutor-statistic-card">
					<div class="tutor-statistic-card-icon">
						<?php
						tutor_utils()->render_svg_icon(
							Icon::FIRE_DISABLED,
							24,
							24,
							array( 'class' => 'tutor-icon-secondary' )
						);
						?>
					</div>
					<div class="tutor-statistic-card-content">
						<h3 class="tutor-statistic-card-value">0</h3>
						<div class="tutor-statistic-card-label">Day streak</div>
					</div>
				</div>
				<div class="tutor-statistic-card">
					<div class="tutor-statistic-card-icon">
						<?php
						tutor_utils()->render_svg_icon(
							Icon::MARKER_TICK,
							24,
							24,
							array( 'class' => 'tutor-actions-success-primary' )
						);
						?>
					</div>
					<div class="tutor-statistic-card-content">
						<h3 class="tutor-statistic-card-value">10</h3>
						<div class="tutor-statistic-card-label">Course Completed</div>
					</div>
				</div>
				<div class="tutor-statistic-card">
					<div class="tutor-statistic-card-icon">
						<?php
						tutor_utils()->render_svg_icon(
							Icon::LEAGUE,
							24,
							24,
							array( 'class' => 'tutor-icon-secondary' )
						);
						?>
					</div>
					<div class="tutor-statistic-card-content">
						<h3 class="tutor-statistic-card-value">None</h3>
						<div class="tutor-statistic-card-label">Current league</div>
					</div>
				</div>
				<div class="tutor-statistic-card">
					<div class="tutor-statistic-card-icon">
						<?php tutor_utils()->render_svg_icon( Icon::FIRE, 24, 24 ); ?>
					</div>
					<div class="tutor-statistic-card-content">
						<h3 class="tutor-statistic-card-value">3000 hr+</h3>
						<div class="tutor-statistic-card-label">Total time spent</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
