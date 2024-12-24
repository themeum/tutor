<?php
/**
 * WP Editor link modal
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

?>
<div id="wp-link-backdrop" style="display: none"></div>
<div id="wp-link-wrap" class="wp-core-ui" style="display: none" role="dialog" aria-labelledby="link-modal-title">
	<form id="wp-link" tabindex="-1">
		<?php wp_nonce_field( 'internal-linking', '_ajax_linking_nonce', false ); ?>
		<h1 id="link-modal-title"><?php echo esc_html__( 'Insert/edit link' ); ?></h1>
		<button type="button" id="wp-link-close">
			<span class="screen-reader-text"><?php echo esc_html__( 'Close' ); ?></span>
		</button>
		<div id="link-selector">
			<div id="link-options">
				<p class="howto" id="wplink-enter-url"><?php echo esc_html__( 'Enter the destination URL' ); ?></p>
				<div>
					<label><span><?php echo esc_html__( 'URL' ); ?></span>
					<input id="wp-link-url" type="text" aria-describedby="wplink-enter-url" /></label>
				</div>
				<div class="wp-link-text-field">
					<label><span><?php echo esc_html__( 'Link Text' ); ?></span>
					<input id="wp-link-text" type="text" /></label>
				</div>
				<div class="link-target">
					<label><span></span>
					<input type="checkbox" id="wp-link-target" /> <?php echo esc_html__( 'Open link in a new tab' ); ?></label>
				</div>
			</div>
			<p class="howto" id="wplink-link-existing-content"><?php echo esc_html__( 'Or link to existing content' ); ?></p>
			<div id="search-panel">
				<div class="link-search-wrapper">
					<label>
						<span class="search-label"><?php echo esc_html__( 'Search' ); ?></span>
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
						<em class="query-notice-default"><?php echo esc_html__( 'No search term specified. Showing recent items.' ); ?></em>
						<em class="query-notice-hint screen-reader-text">
							<?php echo esc_html__( 'Search or use up and down arrow keys to select an item.' ); ?>
						</em>
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
				<button type="button" class="button"><?php echo esc_html__( 'Cancel' ); ?></button>
			</div>
			<div id="wp-link-update">
				<input type="submit" value="<?php echo esc_attr__( 'Add Link' ); ?>" class="button button-primary" id="wp-link-submit" name="wp-link-submit">
			</div>
		</div>
	</form>
</div>
