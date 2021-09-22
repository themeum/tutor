<div class="tutor-option-main-title">
	<h2><?php echo __( $section['label'], 'tutor' ); ?></h2>
	<a href="#">
		<i class="las la-undo-alt"></i> <?php _e( 'Reset to Default', 'tutor' ); ?> </a>
</div>
<!-- end /.tutor-option-main-title -->
<?php
foreach ( $section['blocks'] as $blocks ) :
	if ( empty( $blocks['label'] ) ) :
		?>
		<div class="tutor-option-single-item"><?php echo $this->blocks( $blocks ); ?> </div>
	<?php else : ?>
		<?php echo $this->blocks( $blocks ); ?>
	<?php endif; ?>
<?php endforeach; ?>
