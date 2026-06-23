<?php
/**
 * Frontend Empty Dashboard Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Alert;
use Tutor\Helpers\UrlHelper;

?>

<div class="tutor-dashboard-welcome-card">
	<div class="tutor-dashboard-welcome-content" data-tutor-instructor-status="pending">
		<h3 class="tutor-h3">
			<?php esc_html_e( 'Thank you for applying to become an Instructor!', 'tutor' ); ?>
		</h3>
		<p class="tutor-p2 tutor-text-secondary tutor-mt-4">
			<?php esc_html_e( 'Our team is reviewing your application. Once approved, you\'ll be able to create and publish courses.', 'tutor' ); ?>
		</p>
	</div>
	<div class="tutor-dashboard-welcome-banner">
		<?php tutor_utils()->render_themed_svg( 'images/illustrations/instructor-pending.svg' ); ?>
	</div>
</div>