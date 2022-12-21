<?php
/**
 * Status settings
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<div class="tutor-option-main-title">
	<div class="tutor-fs-4 tutor-fw-medium tutor-color-black"><?php esc_html_e( 'Status', 'tutor' ); ?></div>
</div>

<?php
foreach ( $section['blocks'] as $blocks ) :
	if ( empty( $blocks['label'] ) ) :
		?>
		<div class="tutor-option-single-item tutor-mb-32">
			<?php echo $this->blocks( $blocks ); //phpcs:ignore --contain safe data ?>
		</div>
	<?php else : ?>
		<?php echo $this->blocks( $blocks ); //phpcs:ignore --contain safe data ?>
	<?php endif; ?>
<?php endforeach; ?>
