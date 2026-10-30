<?php
/**
 * Settings tabs
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Template variables provided by Options_V2::template().
 *
 * @var \Tutor\Options_V2 $this
 * @var array            $section
 */

$before = array();
$after  = array();
$tabbed = array();

foreach ( $section['blocks'] as $block ) {
	if ( isset( $block['placement'] ) ) {
		'before' === $block['placement'] ? $before[] = $block : 0;
		'after' === $block['placement'] ? $after[]   = $block : 0;
	} else {
		$tabbed[] = $block;
	}
}

if ( count( $before ) ) {
	$section['blocks'] = $before;
	require __DIR__ . '/basic.php';
}
?>

<div class="tutor-settings-certificate-builder">
	<ul class="tutor-nav" tutor-priority-nav>
		<?php foreach ( $tabbed as $index => $tab_item ) : ?>
			<li class="tutor-nav-item">
				<a href="#" class="tutor-nav-link<?php echo esc_attr( 0 === $index ? ' is-active' : '' ); ?>" data-tutor-nav-target="tutor-settings-tab-<?php echo esc_attr( $tab_item['slug'] ); ?>">
					<?php echo esc_html( $tab_item['label'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>

		<li class="tutor-nav-item tutor-nav-more tutor-d-none">
			<a class="tutor-nav-link tutor-nav-more-item" href="#"><span class="tutor-mr-4"><?php esc_html_e( 'More', 'tutor' ); ?></span> <span class="tutor-nav-more-icon tutor-icon-times"></span></a>
			<ul class="tutor-nav-more-list tutor-dropdown"></ul>
		</li>
	</ul>

	<div class="tutor-tab tutor-mt-32">
		<?php foreach ( $tabbed as $index => $tab_item ) : ?>
			<div id="tutor-settings-tab-<?php echo esc_attr( $tab_item['slug'] ); ?>" class="tutor-tab-item<?php echo 0 === $index ? ' is-active' : ''; ?>">
				<?php
				if ( isset( $tab_item['segments'] ) ) {
					foreach ( $tab_item['segments'] as $segment ) {
						echo $this->blocks( $segment ); //phpcs:ignore -- contain safe data
					}
				} else {
					echo $this->blocks( $tab_item ); //phpcs:ignore -- contain safe data
				}
				?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
