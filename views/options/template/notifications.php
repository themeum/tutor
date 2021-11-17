<?php echo $this->view_template( 'common/reset-button-template.php', $section ); ?>

<?php
foreach ( $section['blocks'] as $blocks ) :
	if ( empty( $blocks['label'] ) ) :
		?>
		<div class="tutor-option-single-item">
			<?php echo $this->blocks( $blocks ); ?> </div>
	<?php else : ?>
		<?php echo $this->blocks( $blocks ); ?>
	<?php endif; ?>
<?php endforeach; ?>
