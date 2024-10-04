<?php
/**
 * Template for password protected course-bundle.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

?>
<?php get_header(); ?>

<div class="tutor-container">
	<div class="tutor-d-flex tutor-password-protected-course">
		<div>
			<p>
				<span class="tutor-badge-label label-warning"><?php esc_html_e( 'Course is locked', 'tutor' ); ?></span>
			</p>
			<p class="tutor-fw-bold tutor-fs-5"><?php the_title(); ?></p>
		</div>
		<!-- # left part -->
		<div class="tutor-ml-32">
			<form action="<?php echo esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ); ?>" method="post">
			<div>
				<p><?php esc_html_e( 'Enter your password', 'tutor' ); ?></p>
				<input  name="post_password"
						class="tutor-form-control tutor-form-control-sm" 
						name="post_password" type="password" spellcheck="false" size="30" />
			</div>
			<div>
				<label for="show-post-password">
					<input type="checkbox">
					<?php esc_html_e( 'Show password', 'tutor' ); ?>
				</label>
			</div>
			<div>
				<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-sm"><?php esc_html_e( 'Submit', 'tutor' ); ?></button>
			</div>
			</form>
		</div>
	</div>
</div>

<?php get_footer(); ?>
