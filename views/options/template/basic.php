<?php
/**
 * Settings option view
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

//phpcs:ignore -- contain safe data
echo $this->view_template( 'common/reset-button-template.php', $section ); ?>

<?php
foreach ( $section['blocks'] as $blocks ) :
	if ( empty( $blocks['label'] ) ) :
		?>
		<div class="tutor-option-single-item tutor-mb-32">
			<?php echo $this->blocks( $blocks ); //phpcs:ignore ?>
		</div>
	<?php else : ?>
		<?php
			echo $this->blocks( $blocks ); //phpcs:ignore
		?>
	<?php endif; ?>
<?php endforeach; ?>
