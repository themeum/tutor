<?php
/**
 * View for course builder.
 *
 * @package Tutor\Views
 * @subpackage Pages
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e( 'Tutor Course Builder', 'tutor' ); ?></title>
	<?php wp_print_styles(); ?>
</head>
<body data-tutor-context="<?php echo is_admin() ? 'backend' : 'frontend'; ?>">
	<div id="tutor-course-builder"></div>
	<?php wp_print_footer_scripts(); ?>

	<div id="wp-link-backdrop" style="display: none"></div>
	<div id="wp-link-wrap" class="wp-core-ui" style="display: none" role="dialog" aria-labelledby="link-modal-title">
		<form id="wp-link" tabindex="-1">
			<?php wp_nonce_field( 'internal-linking', '_ajax_linking_nonce', false ); ?>
			<h1 id="link-modal-title">Insert/edit link</h1>
			<button type="button" id="wp-link-close">
				<span class="screen-reader-text">Close</span>
			</button>
			<div id="link-selector">
				<div id="link-options">
					<p class="howto" id="wplink-enter-url">Enter the destination URL</p>
					<div>
						<label><span>URL</span>
						<input id="wp-link-url" type="text" aria-describedby="wplink-enter-url" /></label>
					</div>
					<div class="wp-link-text-field">
						<label><span>Link Text</span>
						<input id="wp-link-text" type="text" /></label>
					</div>
					<div class="link-target">
						<label><span></span>
						<input type="checkbox" id="wp-link-target" /> Open link in a new tab</label>
					</div>
				</div>
				<p class="howto" id="wplink-link-existing-content">Or link to existing content</p>
				<div id="search-panel">
					<div class="link-search-wrapper">
						<label>
							<span class="search-label">Search</span>
							<input type="search" id="wp-link-search" class="link-search-field" autocomplete="off" aria-describedby="wplink-link-existing-content" />
							<span class="spinner"></span>
						</label>
					</div>
					<div id="search-results" class="query-results" tabindex="0">
						<ul></ul>
						<div class="river-waiting">
							<span class="spinner"></span>
						</div>
					</div>
					<div id="most-recent-results" class="query-results" tabindex="0">
						<div class="query-notice" id="query-notice-message">
							<em class="query-notice-default">No search term specified. Showing recent items.</em>
							<em class="query-notice-hint screen-reader-text">
								Search or use up and down arrow keys to select an item.						</em>
						</div>
						<ul></ul>
						<div class="river-waiting">
							<span class="spinner"></span>
						</div>
					</div>
				</div>
			</div>
			<div class="submitbox">
				<div id="wp-link-cancel">
					<button type="button" class="button">Cancel</button>
				</div>
				<div id="wp-link-update">
					<input type="submit" value="Add Link" class="button button-primary" id="wp-link-submit" name="wp-link-submit">
				</div>
			</div>
		</form>
	</div>
</body>
</html>

