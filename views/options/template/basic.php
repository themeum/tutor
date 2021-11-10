<div class="tutor-option-main-title">
	<h2><?php echo esc_attr( $section['label'] ); ?></h2>
	<a class="reset_to_default tutor-is-outline" data-reset="<?php echo esc_attr( $section['slug'] ); ?>"><i class="btn-icon ttr-refresh-1-filled"></i> <?php _e( 'Reset to Default', 'tutor' ); ?> </a>
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
