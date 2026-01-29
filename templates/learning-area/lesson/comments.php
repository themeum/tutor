<?php
/**
 * Lesson comments template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div x-show="activeTab === 'comments'" x-cloak class="tutor-tab-panel" role="tabpanel">
	<?php esc_html_e( 'Comments', 'tutor' ); ?>
</div>
