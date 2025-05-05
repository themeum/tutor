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
$blocks_slug = $blocks['slug'] ?? '';
foreach ( $section['blocks'] as $blocks ) :
	do_action( 'tutor_before_basic_option_single_item', $blocks_slug, $blocks );
	if ( empty( $blocks['label'] ) ) :
		?>
		<div class="tutor-option-single-item tutor-mb-32">
			<?php echo $this->blocks( $blocks ); //phpcs:ignore ?>
			<?php do_action( 'tutor_inside_basic_option_single_item', $blocks_slug, $blocks ); ?>
		</div>
	<?php else : ?>
		<?php
			echo $this->blocks( $blocks ); //phpcs:ignore
		?>
	<?php endif; ?>
	<?php do_action( 'tutor_after_basic_option_single_item', $blocks_slug, $blocks ); ?>
	
<?php endforeach; ?>
